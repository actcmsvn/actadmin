<?php

namespace ACT\Actadmin\Tests\Unit;

use Illuminate\Support\Facades\Config;
use ACT\Actadmin\Facades\Actadmin;
use ACT\Actadmin\Tests\TestCase;

class ActadminTest extends TestCase
{
    /**
     * Dimmers returns collection of widgets.
     *
     * This test will make sure that the dimmers method will give us a
     * collection of the configured widgets.
     */
    public function testDimmersReturnsCollectionOfConfiguredWidgets()
    {
        Config::set('actadmin.dashboard.widgets', [
            'ACT\\Actadmin\\Tests\\Stubs\\Widgets\\AccessibleDimmer',
            'ACT\\Actadmin\\Tests\\Stubs\\Widgets\\AccessibleDimmer',
        ]);

        $dimmers = Actadmin::dimmers();

        $this->assertEquals(2, $dimmers->count());
    }

    /**
     * Dimmers returns collection of widgets which should be displayed.
     *
     * This test will make sure that the dimmers method will give us a
     * collection of the configured widgets which also should be displayed.
     */
    public function testDimmersReturnsCollectionOfConfiguredWidgetsWhichShouldBeDisplayed()
    {
        Config::set('actadmin.dashboard.widgets', [
            'ACT\\Actadmin\\Tests\\Stubs\\Widgets\\AccessibleDimmer',
            'ACT\\Actadmin\\Tests\\Stubs\\Widgets\\InAccessibleDimmer',
            'ACT\\Actadmin\\Tests\\Stubs\\Widgets\\InAccessibleDimmer',
        ]);

        $dimmers = Actadmin::dimmers();

        $this->assertEquals(1, $dimmers->count());
    }
}
