<?php

namespace ACT\Actadmin\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ACT\Actadmin\Database\Schema\Column;
use ACT\Actadmin\Database\Schema\SchemaManager;
use ACT\Actadmin\Database\Schema\Table;
use ACT\Actadmin\Database\Types\Type;
use ACT\Actadmin\Events\BreadAdded;
use ACT\Actadmin\Events\BreadDeleted;
use ACT\Actadmin\Events\BreadUpdated;
use ACT\Actadmin\Facades\Actadmin;
use ACT\Actadmin\Models\DataRow;
use ACT\Actadmin\Models\DataType;
use ACT\Actadmin\Models\Permission;

class ActadminBreadController extends Controller
{
    public function index()
    {
        Actadmin::canOrFail('browse_bread');

        $dataTypes = Actadmin::model('DataType')->select('id', 'name', 'slug')->get()->keyBy('name')->toArray();

        $tables = array_map(function ($table) use ($dataTypes) {
            $table = [
                'name'       => $table,
                'slug'       => isset($dataTypes[$table]['slug']) ? $dataTypes[$table]['slug'] : null,
                'dataTypeId' => isset($dataTypes[$table]['id']) ? $dataTypes[$table]['id'] : null,
            ];

            return (object) $table;
        }, SchemaManager::listTableNames());

        return Actadmin::view('Actadmin::tools.bread.index')->with(compact('dataTypes', 'tables'));
    }

    /**
     * Create BREAD.
     *
     * @param Request $request
     * @param string  $table   Table name.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request, $table)
    {
        Actadmin::canOrFail('browse_bread');

        $dataType = Actadmin::model('DataType')->whereName($table)->first();

        $data = $this->prepopulateBreadInfo($table);
        $data['fieldOptions'] = SchemaManager::describeTable((isset($dataType) && strlen($dataType->model_name) != 0)
            ? app($dataType->model_name)->getTable()
            : $table
        );

        return Actadmin::view('Actadmin::tools.bread.edit-add', $data);
    }

    private function prepopulateBreadInfo($table)
    {
        $displayName = Str::singular(implode(' ', explode('_', Str::title($table))));
        $modelNamespace = config('actadmin.models.namespace', app()->getNamespace());
        if (empty($modelNamespace)) {
            $modelNamespace = app()->getNamespace();
        }

        return [
            'isModelTranslatable'  => true,
            'table'                => $table,
            'slug'                 => Str::slug($table),
            'display_name'         => $displayName,
            'display_name_plural'  => Str::plural($displayName),
            'model_name'           => $modelNamespace.Str::studly(Str::singular($table)),
            'generate_permissions' => true,
            'server_side'          => false,
        ];
    }

    /**
     * Store BREAD.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $dataType = Actadmin::model('DataType');
            $res = $dataType->updateDataType($request->all(), true);
            $data = $res
                ? $this->alertSuccess(__('Actadmin::bread.success_created_bread'))
                : $this->alertError(__('Actadmin::bread.error_creating_bread'));
            if ($res) {
                event(new BreadAdded($dataType, $data));
            }

            return redirect()->route('actadmin.bread.index')->with($data);
        } catch (Exception $e) {
            return redirect()->route('actadmin.bread.index')->with($this->alertException($e, 'Saving Failed'));
        }
    }

    /**
     * Edit BREAD.
     *
     * @param string $table
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($table)
    {
        Actadmin::canOrFail('browse_bread');

        $dataType = Actadmin::model('DataType')->whereName($table)->first();

        $fieldOptions = SchemaManager::describeTable((strlen($dataType->model_name) != 0)
            ? app($dataType->model_name)->getTable()
            : $dataType->name
        );

        $isModelTranslatable = is_bread_translatable($dataType);
        $tables = SchemaManager::listTableNames();
        $dataTypeRelationships = Actadmin::model('DataRow')->where('data_type_id', '=', $dataType->id)->where('type', '=', 'relationship')->get();

        return Actadmin::view('Actadmin::tools.bread.edit-add', compact('dataType', 'fieldOptions', 'isModelTranslatable', 'tables', 'dataTypeRelationships'));
    }

    /**
     * Update BREAD.
     *
     * @param \Illuminate\Http\Request $request
     * @param number                   $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function update(Request $request, $id)
    {
        Actadmin::canOrFail('browse_bread');

        /* @var \ACT\Actadmin\Models\DataType $dataType */
        try {
            $dataType = Actadmin::model('DataType')->find($id);

            // Prepare Translations and Transform data
            $translations = is_bread_translatable($dataType)
                ? $dataType->prepareTranslations($request)
                : [];

            $res = $dataType->updateDataType($request->all(), true);
            $data = $res
                ? $this->alertSuccess(__('Actadmin::bread.success_update_bread', ['datatype' => $dataType->name]))
                : $this->alertError(__('Actadmin::bread.error_updating_bread'));
            if ($res) {
                event(new BreadUpdated($dataType, $data));
            }

            // Save translations if applied
            $dataType->saveTranslations($translations);

            return redirect()->route('actadmin.bread.index')->with($data);
        } catch (Exception $e) {
            return back()->with($this->alertException($e, __('Actadmin::generic.update_failed')));
        }
    }

    /**
     * Delete BREAD.
     *
     * @param Number $id BREAD data_type id.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        Actadmin::canOrFail('browse_bread');

        /* @var \ACT\Actadmin\Models\DataType $dataType */
        $dataType = Actadmin::model('DataType')->find($id);

        // Delete Translations, if present
        if (is_bread_translatable($dataType)) {
            $dataType->deleteAttributeTranslations($dataType->getTranslatableAttributes());
        }

        $res = Actadmin::model('DataType')->destroy($id);
        $data = $res
            ? $this->alertSuccess(__('Actadmin::bread.success_remove_bread', ['datatype' => $dataType->name]))
            : $this->alertError(__('Actadmin::bread.error_updating_bread'));
        if ($res) {
            event(new BreadDeleted($dataType, $data));
        }

        if (!is_null($dataType)) {
            Actadmin::model('Permission')->removeFrom($dataType->name);
        }

        return redirect()->route('actadmin.bread.index')->with($data);
    }

    // ************************************************************
    //  _____      _       _   _                 _     _
    // |  __ \    | |     | | (_)               | |   (_)
    // | |__) |___| | __ _| |_ _  ___  _ __  ___| |__  _ _ __  ___
    // |  _  // _ \ |/ _` | __| |/ _ \| '_ \/ __| '_ \| | '_ \/ __|
    // | | \ \  __/ | (_| | |_| | (_) | | | \__ \ | | | | |_) \__ \
    // |_|  \_\___|_|\__,_|\__|_|\___/|_| |_|___/_| |_|_| .__/|___/
    //                                                  | |
    //                                                  |_|
    // ************************************************************

    /**
     * Add Relationship.
     *
     * @param Request $request
     */
    public function addRelationship(Request $request)
    {
        $relationshipField = $this->getRelationshipField($request);

        if (!class_exists($request->relationship_model)) {
            return back()->with([
                'message'    => 'Model Class '.$request->relationship_model.' does not exist. Please create Model before creating relationship.',
                'alert-type' => 'error',
            ]);
        }

        try {
            DB::beginTransaction();

            $relationship_column = $request->relationship_column_belongs_to;
            if ($request->relationship_type == 'hasOne' || $request->relationship_type == 'hasMany') {
                $relationship_column = $request->relationship_column;
            }

            // Build the relationship details
            $relationshipDetails = [
                'model'       => $request->relationship_model,
                'table'       => $request->relationship_table,
                'type'        => $request->relationship_type,
                'column'      => $relationship_column,
                'key'         => $request->relationship_key,
                'label'       => $request->relationship_label,
                'pivot_table' => $request->relationship_pivot,
                'pivot'       => ($request->relationship_type == 'belongsToMany') ? '1' : '0',
                'taggable'    => $request->relationship_taggable,
            ];

            $newRow = new DataRow();

            $newRow->data_type_id = $request->data_type_id;
            $newRow->field = $relationshipField;
            $newRow->type = 'relationship';
            $newRow->display_name = $request->relationship_table;
            $newRow->required = 0;

            foreach (['browse', 'read', 'edit', 'add', 'delete'] as $check) {
                $newRow->{$check} = 1;
            }

            $newRow->details = $relationshipDetails;
            $newRow->order = intval(Actadmin::model('DataType')->find($request->data_type_id)->lastRow()->order) + 1;

            if (!$newRow->save()) {
                return back()->with([
                    'message'    => 'Error saving new relationship row for '.$request->relationship_table,
                    'alert-type' => 'error',
                ]);
            }

            DB::commit();

            return back()->with([
                'message'    => 'Successfully created new relationship for '.$request->relationship_table,
                'alert-type' => 'success',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with([
                'message'    => 'Error creating new relationship: '.$e->getMessage(),
                'alert-type' => 'error',
            ]);
        }
    }

    /**
     * Get Relationship Field.
     *
     * @param Request $request
     *
     * @return string
     */
    private function getRelationshipField($request)
    {
        // We need to make sure that we aren't creating an already existing field

        $dataType = Actadmin::model('DataType')->find($request->data_type_id);

        $field = str_singular($dataType->name).'_'.$request->relationship_type.'_'.str_singular($request->relationship_table).'_relationship';

        $relationshipFieldOriginal = $relationshipField = strtolower($field);

        $existingRow = Actadmin::model('DataRow')->where('field', '=', $relationshipField)->first();
        $index = 1;

        while (isset($existingRow->id)) {
            $relationshipField = $relationshipFieldOriginal.'_'.$index;
            $existingRow = Actadmin::model('DataRow')->where('field', '=', $relationshipField)->first();
            $index += 1;
        }

        return $relationshipField;
    }

    /**
     * Delete Relationship.
     *
     * @param Number $id Record id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteRelationship($id)
    {
        Actadmin::model('DataRow')->destroy($id);

        return back()->with([
                'message'    => 'Successfully deleted relationship.',
                'alert-type' => 'success',
            ]);
    }
}
