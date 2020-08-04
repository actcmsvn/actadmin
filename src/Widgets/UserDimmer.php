<?php

namespace ACT\Actadmin\Widgets;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use ACT\Actadmin\Facades\Actadmin;

class UserDimmer extends BaseDimmer
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
        $count = Actadmin::model('User')->count();
        $string = trans_choice('Actadmin::dimmer.user', $count);

        return view('Actadmin::dimmer', array_merge($this->config, [
            'icon'   => 'actadmin-group',
            'title'  => "{$count} {$string}",
            'text'   => __('Actadmin::dimmer.user_text', ['count' => $count, 'string' => Str::lower($string)]),
            'button' => [
                'text' => __('Actadmin::dimmer.user_link_text'),
                'link' => route('actadmin.users.index'),
            ],
            'image' => Actadmin_asset('images/widget-backgrounds/01.jpg'),
        ]));
    }

    /**
     * Determine if the widget should be displayed.
     *
     * @return bool
     */
    public function shouldBeDisplayed()
    {
        return Auth::user()->can('browse', Actadmin::model('User'));
    }
}
