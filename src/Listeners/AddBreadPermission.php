<?php

namespace ACT\Actadmin\Listeners;

use ACT\Actadmin\Events\BreadAdded;
use ACT\Actadmin\Facades\Actadmin;
use ACT\Actadmin\Models\Permission;
use ACT\Actadmin\Models\Role;

class AddBreadPermission
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Create Permission for a given BREAD.
     *
     * @param BreadAdded $event
     *
     * @return void
     */
    public function handle(BreadAdded $bread)
    {
        if (config('actadmin.bread.add_permission') && file_exists(base_path('routes/web.php'))) {
            // Create permission
            //
            // Permission::generateFor(snake_case($bread->dataType->slug));
            $role = Role::where('name', config('actadmin.bread.default_role'))->firstOrFail();

            // Get permission for added table
            $permissions = Permission::where(['table_name' => $bread->dataType->name])->get()->pluck('id')->all();

            // Assign permission to admin
            $role->permissions()->attach($permissions);
        }
    }
}
