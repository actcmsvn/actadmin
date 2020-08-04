<?php

namespace ACT\Actadmin;

use Arrilot\Widgets\Facade as Widget;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use ACT\Actadmin\Actions\DeleteAction;
use ACT\Actadmin\Actions\EditAction;
use ACT\Actadmin\Actions\ViewAction;
use ACT\Actadmin\Events\AlertsCollection;
use ACT\Actadmin\FormFields\After\HandlerInterface as AfterHandlerInterface;
use ACT\Actadmin\FormFields\HandlerInterface;
use ACT\Actadmin\Models\Category;
use ACT\Actadmin\Models\DataRow;
use ACT\Actadmin\Models\DataType;
use ACT\Actadmin\Models\Menu;
use ACT\Actadmin\Models\MenuItem;
use ACT\Actadmin\Models\Page;
use ACT\Actadmin\Models\Permission;
use ACT\Actadmin\Models\Post;
use ACT\Actadmin\Models\Role;
use ACT\Actadmin\Models\Setting;
use ACT\Actadmin\Models\Translation;
use ACT\Actadmin\Models\User;
use ACT\Actadmin\Traits\Translatable;

class Actadmin
{
    protected $version;
    protected $filesystem;

    protected $alerts = [];
    protected $alertsCollected = false;

    protected $formFields = [];
    protected $afterFormFields = [];

    protected $permissionsLoaded = false;
    protected $permissions = [];

    protected $users = [];

    protected $viewLoadingEvents = [];

    protected $actions = [
        DeleteAction::class,
        EditAction::class,
        ViewAction::class,
    ];

    protected $models = [
        'Category'    => Category::class,
        'DataRow'     => DataRow::class,
        'DataType'    => DataType::class,
        'Menu'        => Menu::class,
        'MenuItem'    => MenuItem::class,
        'Page'        => Page::class,
        'Permission'  => Permission::class,
        'Post'        => Post::class,
        'Role'        => Role::class,
        'Setting'     => Setting::class,
        'User'        => User::class,
        'Translation' => Translation::class,
    ];

    public $setting_cache = null;

    public function __construct()
    {
        $this->filesystem = app(Filesystem::class);

        $this->findVersion();
    }

    public function model($name)
    {
        return app($this->models[studly_case($name)]);
    }

    public function modelClass($name)
    {
        return $this->models[$name];
    }

    public function useModel($name, $object)
    {
        if (is_string($object)) {
            $object = app($object);
        }

        $class = get_class($object);

        if (isset($this->models[studly_case($name)]) && !$object instanceof $this->models[studly_case($name)]) {
            throw new \Exception("[{$class}] must be instance of [{$this->models[studly_case($name)]}].");
        }

        $this->models[studly_case($name)] = $class;

        return $this;
    }

    public function view($name, array $parameters = [])
    {
        foreach (array_get($this->viewLoadingEvents, $name, []) as $event) {
            $event($name, $parameters);
        }

        return view($name, $parameters);
    }

    public function onLoadingView($name, \Closure $closure)
    {
        if (!isset($this->viewLoadingEvents[$name])) {
            $this->viewLoadingEvents[$name] = [];
        }

        $this->viewLoadingEvents[$name][] = $closure;
    }

    public function formField($row, $dataType, $dataTypeContent)
    {
        $formField = $this->formFields[$row->type];

        return $formField->handle($row, $dataType, $dataTypeContent);
    }

    public function afterFormFields($row, $dataType, $dataTypeContent)
    {
        return collect($this->afterFormFields)->filter(function ($after) use ($row, $dataType, $dataTypeContent) {
            return $after->visible($row, $dataType, $dataTypeContent, $row->details);
        });
    }

    public function addFormField($handler)
    {
        if (!$handler instanceof HandlerInterface) {
            $handler = app($handler);
        }

        $this->formFields[$handler->getCodename()] = $handler;

        return $this;
    }

    public function addAfterFormField($handler)
    {
        if (!$handler instanceof AfterHandlerInterface) {
            $handler = app($handler);
        }

        $this->afterFormFields[$handler->getCodename()] = $handler;

        return $this;
    }

    public function formFields()
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver", 'mysql');

        return collect($this->formFields)->filter(function ($after) use ($driver) {
            return $after->supports($driver);
        });
    }

    public function addAction($action)
    {
        array_push($this->actions, $action);
    }

    public function replaceAction($actionToReplace, $action)
    {
        $key = array_search($actionToReplace, $this->actions);
        $this->actions[$key] = $action;
    }

    public function actions()
    {
        return $this->actions;
    }

    /**
     * Get a collection of the dashboard widgets.
     *
     * @return \Arrilot\Widgets\WidgetGroup
     */
    public function dimmers()
    {
        $widgetClasses = config('actadmin.dashboard.widgets');
        $dimmers = Widget::group('Actadmin::dimmers');

        foreach ($widgetClasses as $widgetClass) {
            $widget = app($widgetClass);

            if ($widget->shouldBeDisplayed()) {
                $dimmers->addWidget($widgetClass);
            }
        }

        return $dimmers;
    }

    public function setting($key, $default = null)
    {
        if ($this->setting_cache === null) {
            foreach (self::model('Setting')->all() as $setting) {
                $keys = explode('.', $setting->key);
                @$this->setting_cache[$keys[0]][$keys[1]] = $setting->value;
            }
        }

        $parts = explode('.', $key);

        if (count($parts) == 2) {
            return @$this->setting_cache[$parts[0]][$parts[1]] ?: $default;
        } else {
            return @$this->setting_cache[$parts[0]] ?: $default;
        }
    }

    public function image($file, $default = '')
    {
        if (!empty($file)) {
            return str_replace('\\', '/', Storage::disk(config('actadmin.storage.disk'))->url($file));
        }

        return $default;
    }

    public function routes()
    {
        require __DIR__.'/../routes/actadmin.php';
    }

    /** @deprecated */
    public function can($permission)
    {
        $this->loadPermissions();

        // Check if permission exist
        $exist = $this->permissions->where('key', $permission)->first();

        // Permission not found
        if (!$exist) {
            throw new \Exception('Permission does not exist', 400);
        }

        $user = $this->getUser();
        if ($user == null || !$user->hasPermission($permission)) {
            return false;
        }

        return true;
    }

    /** @deprecated */
    public function canOrFail($permission)
    {
        if (!$this->can($permission)) {
            throw new AccessDeniedHttpException();
        }

        return true;
    }

    /** @deprecated */
    public function canOrAbort($permission, $statusCode = 403)
    {
        if (!$this->can($permission)) {
            return abort($statusCode);
        }

        return true;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function addAlert(Alert $alert)
    {
        $this->alerts[] = $alert;
    }

    public function alerts()
    {
        if (!$this->alertsCollected) {
            event(new AlertsCollection($this->alerts));

            $this->alertsCollected = true;
        }

        return $this->alerts;
    }

    protected function findVersion()
    {
        if (!is_null($this->version)) {
            return;
        }

        if ($this->filesystem->exists(base_path('composer.lock'))) {
            // Get the composer.lock file
            $file = json_decode(
                $this->filesystem->get(base_path('composer.lock'))
            );

            // Loop through all the packages and get the version of Actadmin
            foreach ($file->packages as $package) {
                if ($package->name == 'act/actadmin') {
                    $this->version = $package->version;
                    break;
                }
            }
        }
    }

    /**
     * @param string|Model|Collection $model
     *
     * @return bool
     */
    public function translatable($model)
    {
        if (!config('actadmin.multilingual.enabled')) {
            return false;
        }

        if (is_string($model)) {
            $model = app($model);
        }

        if ($model instanceof Collection) {
            $model = $model->first();
        }

        if (!is_subclass_of($model, Model::class)) {
            return false;
        }

        $traits = class_uses_recursive(get_class($model));

        return in_array(Translatable::class, $traits);
    }

    /** @deprecated */
    protected function loadPermissions()
    {
        if (!$this->permissionsLoaded) {
            $this->permissionsLoaded = true;

            $this->permissions = self::model('Permission')->all();
        }
    }

    protected function getUser($id = null)
    {
        if (is_null($id)) {
            $id = auth()->check() ? auth()->user()->id : null;
        }

        if (is_null($id)) {
            return;
        }

        if (!isset($this->users[$id])) {
            $this->users[$id] = self::model('User')->find($id);
        }

        return $this->users[$id];
    }

    public function getLocales()
    {
        return array_diff(scandir(realpath(__DIR__.'/../publishable/lang')), ['..', '.']);
    }
}
