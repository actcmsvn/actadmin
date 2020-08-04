<?php

use Illuminate\Database\Seeder;
use ACT\Actadmin\Models\DataType;

class DataTypesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        $dataType = $this->dataType('slug', 'users');
        if (!$dataType->exists) {
            $dataType->fill([
                'name'                  => 'users',
                'display_name_singular' => __('Actadmin::seeders.data_types.user.singular'),
                'display_name_plural'   => __('Actadmin::seeders.data_types.user.plural'),
                'icon'                  => 'actadmin-person',
                'model_name'            => 'ACT\\Actadmin\\Models\\User',
                'policy_name'           => 'ACT\\Actadmin\\Policies\\UserPolicy',
                'controller'            => '',
                'generate_permissions'  => 1,
                'description'           => '',
            ])->save();
        }

        $dataType = $this->dataType('slug', 'menus');
        if (!$dataType->exists) {
            $dataType->fill([
                'name'                  => 'menus',
                'display_name_singular' => __('Actadmin::seeders.data_types.menu.singular'),
                'display_name_plural'   => __('Actadmin::seeders.data_types.menu.plural'),
                'icon'                  => 'actadmin-list',
                'model_name'            => 'ACT\\Actadmin\\Models\\Menu',
                'controller'            => '',
                'generate_permissions'  => 1,
                'description'           => '',
            ])->save();
        }

        $dataType = $this->dataType('slug', 'roles');
        if (!$dataType->exists) {
            $dataType->fill([
                'name'                  => 'roles',
                'display_name_singular' => __('Actadmin::seeders.data_types.role.singular'),
                'display_name_plural'   => __('Actadmin::seeders.data_types.role.plural'),
                'icon'                  => 'actadmin-lock',
                'model_name'            => 'ACT\\Actadmin\\Models\\Role',
                'controller'            => '',
                'generate_permissions'  => 1,
                'description'           => '',
            ])->save();
        }
    }

    /**
     * [dataType description].
     *
     * @param [type] $field [description]
     * @param [type] $for   [description]
     *
     * @return [type] [description]
     */
    protected function dataType($field, $for)
    {
        return DataType::firstOrNew([$field => $for]);
    }
}
