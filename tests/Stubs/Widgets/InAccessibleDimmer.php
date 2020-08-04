<?php

namespace ACT\Actadmin\Tests\Stubs\Widgets;

use Arrilot\Widgets\AbstractWidget;

class InAccessibleDimmer extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        return view('Actadmin::dimmer', array_merge($this->config, [
            'icon'   => '',
            'title'  => '',
            'text'   => '',
            'button' => [
                'text' => '',
                'link' => '',
            ],
            'image' => '',
        ]));
    }

    /**
     * Determine if the widget should be displayed.
     *
     * @return bool
     */
    public function shouldBeDisplayed()
    {
        return false;
    }
}
