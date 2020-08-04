<?php

namespace ACT\Actadmin\Widgets;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use ACT\Actadmin\Facades\Actadmin;

class PageDimmer extends BaseDimmer
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
        $count = Actadmin::model('Page')->count();
        $string = trans_choice('Actadmin::dimmer.page', $count);

        return view('Actadmin::dimmer', array_merge($this->config, [
            'icon'   => 'actadmin-file-text',
            'title'  => "{$count} {$string}",
            'text'   => __('Actadmin::dimmer.page_text', ['count' => $count, 'string' => Str::lower($string)]),
            'button' => [
                'text' => __('Actadmin::dimmer.page_link_text'),
                'link' => route('actadmin.pages.index'),
            ],
            'image' => Actadmin_asset('images/widget-backgrounds/03.jpg'),
        ]));
    }

    /**
     * Determine if the widget should be displayed.
     *
     * @return bool
     */
    public function shouldBeDisplayed()
    {
        return Auth::user()->can('browse', Actadmin::model('Page'));
    }
}
