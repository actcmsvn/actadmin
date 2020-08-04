<?php

namespace ACT\Actadmin\Events;

use Illuminate\Queue\SerializesModels;

class RoutingAdminAfter
{
    use SerializesModels;

    public $router;

    public function __construct()
    {
        $this->router = app('router');

        // @deprecate
        //
        event('actadmin.admin.routing.after', $this->router);
    }
}
