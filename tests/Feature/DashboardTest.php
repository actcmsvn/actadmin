<?php

namespace ACT\Actadmin\Tests\Feature;

use Illuminate\Support\Facades\Auth;
use ACT\Actadmin\Facades\Actadmin;
use ACT\Actadmin\Tests\TestCase;

class DashboardTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->install();
    }

    /**
     * Test Dashboard Widgets.
     *
     * This test will make sure the configured widgets are being shown on
     * the dashboard page.
     */
    public function testWidgetsAreBeingShownOnDashboardPage()
    {
        // We must first login and visit the dashboard page.
        Auth::loginUsingId(1);

        $this->visit(route('actadmin.dashboard'))
            ->see(__('Actadmin::generic.dashboard'));

        // Test UserDimmer widget
        $this->see(trans_choice('Actadmin::dimmer.user', 1))
             ->click(__('Actadmin::dimmer.user_link_text'))
             ->seePageIs(route('actadmin.users.index'))
             ->click(__('Actadmin::generic.dashboard'))
             ->seePageIs(route('actadmin.dashboard'));

        // Test PostDimmer widget
        $this->see(trans_choice('Actadmin::dimmer.post', 4))
             ->click(__('Actadmin::dimmer.post_link_text'))
             ->seePageIs(route('actadmin.posts.index'))
             ->click(__('Actadmin::generic.dashboard'))
             ->seePageIs(route('actadmin.dashboard'));

        // Test PageDimmer widget
        $this->see(trans_choice('Actadmin::dimmer.page', 1))
             ->click(__('Actadmin::dimmer.page_link_text'))
             ->seePageIs(route('actadmin.pages.index'))
             ->click(__('Actadmin::generic.dashboard'))
             ->seePageIs(route('actadmin.dashboard'))
             ->see(__('Actadmin::generic.dashboard'));
    }

    /**
     * UserDimmer widget isn't displayed without the right permissions.
     */
    public function testUserDimmerWidgetIsNotShownWithoutTheRightPermissions()
    {
        // We must first login and visit the dashboard page.
        $user = \Auth::loginUsingId(1);

        // Remove `browse_users` permission
        $user->role->permissions()->detach(
            $user->role->permissions()->where('key', 'browse_users')->first()
        );

        $this->visit(route('actadmin.dashboard'))
            ->see(__('Actadmin::generic.dashboard'));

        // Test UserDimmer widget
        $this->dontSee('<h4>1 '.trans_choice('Actadmin::dimmer.user', 1).'</h4>')
             ->dontSee(__('Actadmin::dimmer.user_link_text'));
    }

    /**
     * PostDimmer widget isn't displayed without the right permissions.
     */
    public function testPostDimmerWidgetIsNotShownWithoutTheRightPermissions()
    {
        // We must first login and visit the dashboard page.
        $user = \Auth::loginUsingId(1);

        // Remove `browse_users` permission
        $user->role->permissions()->detach(
            $user->role->permissions()->where('key', 'browse_posts')->first()
        );

        $this->visit(route('actadmin.dashboard'))
            ->see(__('Actadmin::generic.dashboard'));

        // Test PostDimmer widget
        $this->dontSee('<h4>1 '.trans_choice('Actadmin::dimmer.post', 1).'</h4>')
             ->dontSee(__('Actadmin::dimmer.post_link_text'));
    }

    /**
     * PageDimmer widget isn't displayed without the right permissions.
     */
    public function testPageDimmerWidgetIsNotShownWithoutTheRightPermissions()
    {
        // We must first login and visit the dashboard page.
        $user = \Auth::loginUsingId(1);

        // Remove `browse_users` permission
        $user->role->permissions()->detach(
            $user->role->permissions()->where('key', 'browse_pages')->first()
        );

        $this->visit(route('actadmin.dashboard'))
            ->see(__('Actadmin::generic.dashboard'));

        // Test PageDimmer widget
        $this->dontSee('<h4>1 '.trans_choice('Actadmin::dimmer.page', 1).'</h4>')
             ->dontSee(__('Actadmin::dimmer.page_link_text'));
    }

    /**
     * Test See Correct Footer Version Number.
     *
     * This test will make sure the footer contains the correct version number.
     */
    public function testSeeingCorrectFooterVersionNumber()
    {
        // We must first login and visit the dashboard page.
        Auth::loginUsingId(1);

        $this->visit(route('actadmin.dashboard'))
             ->see(Actadmin::getVersion());
    }
}
