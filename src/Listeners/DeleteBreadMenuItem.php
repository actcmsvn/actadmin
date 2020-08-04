<?php

namespace ACT\Actadmin\Listeners;

use ACT\Actadmin\Events\BreadDeleted;
use ACT\Actadmin\Models\MenuItem;

class DeleteBreadMenuItem
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
     * Delete a MenuItem for a given BREAD.
     *
     * @param BreadDeleted $bread
     *
     * @return void
     */
    public function handle(BreadDeleted $bread)
    {
        if (config('actadmin.bread.add_menu_item')) {
            $menuItem = MenuItem::where('route', 'actadmin.'.$bread->dataType->slug.'.index');

            if ($menuItem->exists()) {
                $menuItem->delete();
            }
        }
    }
}
