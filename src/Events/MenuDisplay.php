<?php

namespace ACT\Actadmin\Events;

use Illuminate\Queue\SerializesModels;
use ACT\Actadmin\Models\Menu;

class MenuDisplay
{
    use SerializesModels;

    public $menu;

    public function __construct(Menu $menu)
    {
        $this->menu = $menu;

        // @deprecate
        //
        event('actadmin.menu.display', $menu);
    }
}
