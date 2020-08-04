<?php

namespace ACT\Actadmin\Events;

use Illuminate\Queue\SerializesModels;

class RoutingAfter
{
    use SerializesModels;

    public $router;

    public function __construct()
    {
        $this->router = app('router');

        // @deprecate
        //
        event('actadmin.routing.after', $this->router);
    }
}
