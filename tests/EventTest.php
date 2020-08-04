<?php

namespace ACT\Actadmin\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use ACT\Actadmin\Events\BreadAdded;
use ACT\Actadmin\Events\BreadDataAdded;
use ACT\Actadmin\Events\BreadDataDeleted;
use ACT\Actadmin\Events\BreadDataUpdated;
use ACT\Actadmin\Events\BreadDeleted;
use ACT\Actadmin\Events\BreadImagesDeleted;
use ACT\Actadmin\Events\BreadUpdated;
use ACT\Actadmin\Events\FileDeleted;
use ACT\Actadmin\Events\TableAdded;
use ACT\Actadmin\Events\TableDeleted;
use ACT\Actadmin\Events\TableUpdated;
use ACT\Actadmin\Models\DataType;
use ACT\Actadmin\Models\Page;

class EventTest extends TestCase
{
    use DatabaseTransactions;

    public function testBreadAddedEvent()
    {
        Event::fake();
        Auth::loginUsingId(1);

        $this->post(route('actadmin.bread.store'), [
            'name'                  => 'Toast',
            'slug'                  => 'toast',
            'display_name_singular' => 'toast',
            'display_name_plural'   => 'toasts',
            'icon'                  => 'fa fa-toast',
            'description'           => 'This is a toast',
        ]);

        Event::assertDispatched(BreadAdded::class, function ($event) {
            return $event->dataType->name === 'Toast'
                || $event->dataType->slug === 'toast'
                || $event->dataType->display_name_singular === 'toast'
                || $event->dataType->display_name_plural === 'toasts'
                || $event->dataType->icon === 'fa fa-toast'
                || $event->dataType->description === 'This is a toast';
        });
    }

    public function testBreadUpdatedEvent()
    {
        Event::fake();
        Auth::loginUsingId(1);

        $this->post(route('actadmin.bread.store'), [
            'name'                  => 'Toast',
            'slug'                  => 'toast',
            'display_name_singular' => 'toast',
            'display_name_plural'   => 'toasts',
            'icon'                  => 'fa fa-toast',
            'description'           => 'This is a toast',
        ]);

        Event::assertNotDispatched(BreadUpdated::class);
        $dataType = DataType::where('slug', 'toast')->firstOrFail();

        $this->put(route('actadmin.bread.update', [$dataType->id]), [
            'name'                  => 'Test',
            'slug'                  => 'test',
            'display_name_singular' => 'test',
            'display_name_plural'   => 'tests',
            'icon'                  => 'fa fa-test',
            'description'           => 'This is a test',
        ]);

        Event::assertDispatched(BreadUpdated::class, function ($event) {
            return $event->dataType->name === 'Test'
                || $event->dataType->slug === 'test'
                || $event->dataType->display_name_singular === 'test'
                || $event->dataType->display_name_plural === 'tests'
                || $event->dataType->icon === 'fa fa-test'
                || $event->dataType->description === 'This is a test';
        });
    }

    public function testBreadDeletedEvent()
    {
        Event::fake();
        Auth::loginUsingId(1);

        $this->post(route('actadmin.bread.store'), [
            'name'                  => 'Toast',
            'slug'                  => 'toast',
            'display_name_singular' => 'toast',
            'display_name_plural'   => 'toasts',
            'icon'                  => 'fa fa-toast',
            'description'           => 'This is a toast',
        ]);

        Event::assertNotDispatched(BreadDeleted::class);
        $dataType = DataType::where('slug', 'toast')->firstOrFail();

        $this->delete(route('actadmin.bread.delete', [$dataType->id]));

        Event::assertDispatched(BreadDeleted::class);
    }

    public function testBreadDataAddedEvent()
    {
        Event::fake();
        Auth::loginUsingId(1);

        $this->post(route('actadmin.pages.store'), [
            'title'  => 'Toast',
            'slug'   => 'toasts',
            'status' => 'active',
        ]);

        Event::assertDispatched(BreadDataAdded::class);
    }

    public function testBreadDataUpdatedEvent()
    {
        Event::fake();
        Auth::loginUsingId(1);

        $this->post(route('actadmin.pages.store'), [
            'title'  => 'Toast',
            'slug'   => 'toasts',
            'status' => 'active',
        ]);

        Event::assertNotDispatched(BreadDataUpdated::class);

        $page = Page::where('slug', 'toasts')->firstOrFail();

        $this->put(route('actadmin.pages.update', [$page->id]), [
            'title'  => 'Test',
            'slug'   => 'tests',
            'status' => 'pending',
        ]);

        Event::assertDispatched(BreadDataUpdated::class);
    }

    public function testBreadDataDeletedEvent()
    {
        Event::fake();
        Auth::loginUsingId(1);

        $this->post(route('actadmin.pages.store'), [
            'title'  => 'Toast',
            'slug'   => 'toasts',
            'status' => 'active',
        ]);

        Event::assertNotDispatched(BreadDataDeleted::class);

        $page = Page::where('slug', 'toasts')->firstOrFail();

        $this->delete(route('actadmin.pages.destroy', [$page->id]));

        Event::assertDispatched(BreadDataDeleted::class);
    }

    public function testBreadImagesDeletedEvent()
    {
        Event::fake();
        Auth::loginUsingId(1);
        Storage::fake(config('filesystems.default'));

        $image = UploadedFile::fake()->image('test.png');

        $this->call('POST', route('actadmin.pages.store'), [
            'title'  => 'Toast',
            'slug'   => 'toasts',
            'status' => 'active',
        ], [], [
            'image' => $image,
        ]);

        Event::assertNotDispatched(BreadImagesDeleted::class);

        $page = Page::where('slug', 'toasts')->firstOrFail();

        $this->delete(route('actadmin.pages.destroy', [$page->id]));

        Event::assertDispatched(BreadImagesDeleted::class);
    }

    public function testFileDeletedEvent()
    {
        Event::fake();
        Auth::loginUsingId(1);
        Storage::fake(config('filesystems.default'));

        $image = UploadedFile::fake()->image('test.png');

        $this->call('POST', route('actadmin.pages.store'), [
            'title'  => 'Toast',
            'slug'   => 'toasts',
            'status' => 'active',
        ], [], [
            'image' => $image,
        ]);

        Event::assertNotDispatched(FileDeleted::class);

        $page = Page::where('slug', 'toasts')->firstOrFail();

        $this->delete(route('actadmin.pages.destroy', [$page->id]));

        Event::assertDispatched(FileDeleted::class);
    }

    public function testTableAddedEvent()
    {
        Event::fake();
        Auth::loginUsingId(1);

        $this->post(route('actadmin.database.store'), [
            'table' => [
                'name'    => 'test',
                'columns' => [
                    [
                        'name' => 'id',
                        'type' => [
                            'name' => 'integer',
                        ],
                    ],
                ],
                'indexes'     => [],
                'foreignKeys' => [],
                'options'     => [],
            ],
        ]);

        Event::assertDispatched(TableAdded::class);
    }

    public function testTableUpdatedEvent()
    {
        Event::fake();
        Auth::loginUsingId(1);

        $this->post(route('actadmin.database.store'), [
            'table' => [
                'name'    => 'test',
                'columns' => [
                    [
                        'name' => 'id',
                        'type' => [
                            'name' => 'integer',
                        ],
                    ],
                ],
                'indexes'     => [],
                'foreignKeys' => [],
                'options'     => [],
            ],
        ]);

        Event::assertNotDispatched(TableUpdated::class);

        $this->put(route('actadmin.database.update', ['test']), [
            'table' => json_encode([
                'name'    => 'test',
                'oldName' => 'test',
                'columns' => [
                    [
                        'name'    => 'id',
                        'oldName' => 'id',
                        'type'    => [
                            'name' => 'integer',
                        ],
                    ],
                ],
                'indexes'     => [],
                'foreignKeys' => [],
                'options'     => [],
            ]),
        ]);

        Event::assertDispatched(TableUpdated::class);
    }

    public function testTableDeletedEvent()
    {
        Event::fake();
        Auth::loginUsingId(1);

        $this->post(route('actadmin.database.store'), [
            'table' => [
                'name'    => 'test',
                'columns' => [
                    [
                        'name' => 'id',
                        'type' => [
                            'name' => 'integer',
                        ],
                    ],
                ],
                'indexes'     => [],
                'foreignKeys' => [],
                'options'     => [],
            ],
        ]);

        Event::assertNotDispatched(TableDeleted::class);

        $this->delete(route('actadmin.database.destroy', ['test']));

        Event::assertDispatched(TableDeleted::class);
    }
}
