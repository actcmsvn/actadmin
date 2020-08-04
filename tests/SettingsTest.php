<?php

namespace ACT\Actadmin\Tests;

use Illuminate\Support\Facades\Auth;
use ACT\Actadmin\Models\Setting;

class SettingsTest extends TestCase
{
    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->user = Auth::loginUsingId(1);
    }

    public function testCanUpdateSettings()
    {
        $key = 'site.title';
        $newTitle = 'Just Another Laravelactadmin.com Site';

        $this->visit(route('actadmin.settings.index'))
             ->seeInField($key, Setting::where('key', '=', $key)->first()->value)
             ->type($newTitle, $key)
             ->seeInElement('button', __('Actadmin::settings.save'))
             ->press(__('Actadmin::settings.save'))
             ->seePageIs(route('actadmin.settings.index'));

        $this->assertEquals(
            Setting::where('key', '=', $key)->first()->value,
            $newTitle
        );
    }
}
