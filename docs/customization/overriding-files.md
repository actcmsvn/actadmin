# Overriding files

### Overriding BREAD Views

You can override any of the BREAD views for a **single** BREAD by creating a new folder in `resources/views/vendor/actadmin/slug-name` where _slug-name_ is the _slug_ that you have assigned for that table. There are 4 files that you can override:

* browse.blade.php
* edit-add.blade.php
* read.blade.php
* order.blade.php

Alternatively you can override the views for **all** BREADs by creating any of the above files under `resources/views/vendor/actadmin/bread`

### Using custom Controllers

You can override the controller for a single BREAD by creating a controller which extends Actadmin controller, for example:

```php
<?php

namespace App\Http\Controllers;

class ActadminCategoriesController extends \ACT\Actadmin\Http\Controllers\ActadminBaseController
{
    //...
}
```

After that go to the BREAD-settings and fill in the Controller Name with your fully-qualified class-name:

![](../.gitbook/assets/bread_controller.png)

You can now override all methods from the [ActadminBaseController](https://github.com/host9999/actadmin/blob/1.1/src/Http/Controllers/ActadminBaseController.php)

### Overriding Actadmin Controllers

If you want to override any of Actadmins core controllers you first have to change your config file `config/actadmin.php`:

```php
/*
|--------------------------------------------------------------------------
| Controllers config
|--------------------------------------------------------------------------
|
| Here you can specify Actadmin controller settings
|
*/
​
'controllers' => [
    'namespace' => 'App\\Http\\Controllers\\Actadmin',
],
```

Then run `php artisan actadmin:controllers`, Actadmin will now use the child controllers which will be created at `App/Http/Controllers/Actadmin`

### Overriding actadmin-Models

You are also able to override Actadmin models if you need to.  
To do so, you need to add the following to your AppServiceProviders register method:

```php
Actadmin::useModel($name, $object);
```

Where **name** is the class-name of the model and **object** the fully-qualified name of your custom model. For example:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Events\Dispatcher;
use ACT\Actadmin\Facades\Actadmin;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        Actadmin::useModel('DataRow', \App\DataRow::class);
    }
    // ...
}
```

The next step is to create your model and make it extend the original model. In case of `DataRow`:

```php
<?php

namespace App;

class DataRow extends \ACT\Actadmin\Models\DataRow
{
    // ...
}
```
