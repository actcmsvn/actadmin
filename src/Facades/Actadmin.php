<?php

namespace ACT\Actadmin\Facades;

use Illuminate\Support\Facades\Facade;

class Actadmin extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'actadmin';
    }
}
