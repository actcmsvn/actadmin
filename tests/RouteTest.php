<?php

namespace ACT\Actadmin\Tests;

class RouteTest extends TestCase
{
    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testGetRoutes()
    {
        $this->disableExceptionHandling();

        $this->visit(route('actadmin.login'));
        $this->type('admin@admin.com', 'email');
        $this->type('password', 'password');
        $this->press(__('Actadmin::generic.login'));

        $urls = [
            route('actadmin.dashboard'),
            route('actadmin.media.index'),
            route('actadmin.settings.index'),
            route('actadmin.roles.index'),
            route('actadmin.roles.create'),
            route('actadmin.roles.show', ['role' => 1]),
            route('actadmin.roles.edit', ['role' => 1]),
            route('actadmin.users.index'),
            route('actadmin.users.create'),
            route('actadmin.users.show', ['user' => 1]),
            route('actadmin.users.edit', ['user' => 1]),
            route('actadmin.posts.index'),
            route('actadmin.posts.create'),
            route('actadmin.posts.show', ['post' => 1]),
            route('actadmin.posts.edit', ['post' => 1]),
            route('actadmin.pages.index'),
            route('actadmin.pages.create'),
            route('actadmin.pages.show', ['page' => 1]),
            route('actadmin.pages.edit', ['page' => 1]),
            route('actadmin.categories.index'),
            route('actadmin.categories.create'),
            route('actadmin.categories.show', ['category' => 1]),
            route('actadmin.categories.edit', ['category' => 1]),
            route('actadmin.menus.index'),
            route('actadmin.menus.create'),
            route('actadmin.menus.show', ['menu' => 1]),
            route('actadmin.menus.edit', ['menu' => 1]),
            route('actadmin.database.index'),
            route('actadmin.bread.edit', ['table' => 'categories']),
            route('actadmin.database.edit', ['table' => 'categories']),
            route('actadmin.database.create'),
        ];

        foreach ($urls as $url) {
            $response = $this->call('GET', $url);
            $this->assertEquals(200, $response->status(), $url.' did not return a 200');
        }
    }
}
