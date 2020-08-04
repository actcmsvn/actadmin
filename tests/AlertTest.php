<?php

namespace ACT\Actadmin\Tests;

use ACT\Actadmin\Alert;
use ACT\Actadmin\Facades\Actadmin;

class AlertTest extends TestCase
{
    public function testAlertsAreRegistered()
    {
        $alert = (new Alert('test', 'warning'))
            ->title('Title');

        Actadmin::addAlert($alert);

        $alerts = Actadmin::alerts();

        $this->assertCount(1, $alerts);
    }

    public function testComponentRenders()
    {
        Actadmin::addAlert((new Alert('test', 'warning'))
            ->title('Title')
            ->text('Text')
            ->button('Button', 'http://example.com', 'danger'));

        $alerts = Actadmin::alerts();

        $this->assertEquals('<strong>Title</strong>', $alerts[0]->components[0]->render());
        $this->assertEquals('<p>Text</p>', $alerts[0]->components[1]->render());
        $this->assertEquals("<a href='http://example.com' class='btn btn-danger'>Button</a>", $alerts[0]->components[2]->render());
    }

    public function testAlertsRenders()
    {
        Actadmin::addAlert((new Alert('test', 'warning'))
            ->title('Title')
            ->text('Text')
            ->button('Button', 'http://example.com', 'danger'));

        Actadmin::addAlert((new Alert('foo'))
            ->title('Bar')
            ->text('Foobar')
            ->button('Link', 'http://example.org'));

        $this->assertEquals(
            file_get_contents(__DIR__.'/rendered_alerts.html'),
            view('Actadmin::alerts')->render()
        );
    }
}
