<?php

namespace ACT\Actadmin\Tests;

use Illuminate\Support\Facades\Auth;
use ACT\Actadmin\Facades\Actadmin;
use ACT\Actadmin\Models\Permission;
use ACT\Actadmin\Models\Role;

class PermissionTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Auth::loginUsingId(1);
    }

    public function testPermissionNotExisting()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Permission does not exist');

        Actadmin::can('test');
    }

    public function testNotHavingPermission()
    {
        Permission::create(['key' => 'test']);

        $this->assertFalse(Actadmin::can('test'));
    }

    public function testHavingPermission()
    {
        $role = Role::find(1)
            ->permissions()
            ->create(['key' => 'test']);

        $this->assertTrue(Actadmin::can('test'));
    }
}
