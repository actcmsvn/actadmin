<?php

namespace ACT\Actadmin\Widgets;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use ACT\Actadmin\Facades\Actadmin;

class PostDimmer extends BaseDimmer
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
        $count = Actadmin::model('Post')->count();
        $string = trans_choice('Actadmin::dimmer.post', $count);

        return view('Actadmin::dimmer', array_merge($this->config, [
            'icon'   => 'actadmin-news',
            'title'  => "{$count} {$string}",
            'text'   => __('Actadmin::dimmer.post_text', ['count' => $count, 'string' => Str::lower($string)]),
            'button' => [
                'text' => __('Actadmin::dimmer.post_link_text'),
                'link' => route('actadmin.posts.index'),
            ],
            'image' => Actadmin_asset('images/widget-backgrounds/02.jpg'),
        ]));
    }

    /**
     * Determine if the widget should be displayed.
     *
     * @return bool
     */
    public function shouldBeDisplayed()
    {
        return Auth::user()->can('browse', Actadmin::model('Post'));
    }
}
