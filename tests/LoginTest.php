<?php

namespace ACT\Actadmin\Tests;

class LoginTest extends TestCase
{
    public function testSuccessfulLoginWithDefaultCredentials()
    {
        $this->visit(route('actadmin.login'))
             ->type('admin@admin.com', 'email')
             ->type('password', 'password')
             ->press(__('Actadmin::generic.login'))
             ->seePageIs(route('actadmin.dashboard'));
    }

    public function testShowAnErrorMessageWhenITryToLoginWithWrongCredentials()
    {
        $this->visit(route('actadmin.login'))
             ->type('john@Doe.com', 'email')
             ->type('pass', 'password')
             ->press(__('Actadmin::generic.login'))
             ->seePageIs(route('actadmin.login'))
             ->see(__('auth.failed'))
             ->seeInField('email', 'john@Doe.com');
    }
}
