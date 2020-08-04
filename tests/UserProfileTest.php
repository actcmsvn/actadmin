<?php

namespace ACT\Actadmin\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use ACT\Actadmin\Models\Role;
use ACT\Actadmin\Models\User;

class UserProfileTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected $editPageForTheCurrentUser;

    protected $listOfUsers;

    public function setUp()
    {
        parent::setUp();

        $this->user = Auth::loginUsingId(1);

        $this->editPageForTheCurrentUser = route('actadmin.users.edit', ['user' => $this->user->id]);

        $this->listOfUsers = route('actadmin.users.index');

        $this->withFactories(__DIR__.'/database/factories');
    }

    public function testCanSeeTheUserInfoOnHisProfilePage()
    {
        $this->visit(route('actadmin.profile'))
             ->seeInElement('h4', $this->user->name)
             ->seeInElement('.user-email', $this->user->email)
             ->seeLink(__('Actadmin::profile.edit'));
    }

    public function testCanEditUserName()
    {
        $this->visit(route('actadmin.profile'))
             ->click(__('Actadmin::profile.edit'))
             ->see(__('Actadmin::profile.edit_user'))
             ->seePageIs($this->editPageForTheCurrentUser)
             ->type('New Awesome Name', 'name')
             ->press(__('Actadmin::generic.save'))
             ->seePageIs($this->listOfUsers)
             ->seeInDatabase(
                 'users',
                 ['name' => 'New Awesome Name']
             );
    }

    public function testCanEditUserEmail()
    {
        $this->visit(route('actadmin.profile'))
             ->click(__('Actadmin::profile.edit'))
             ->see(__('Actadmin::profile.edit_user'))
             ->seePageIs($this->editPageForTheCurrentUser)
             ->type('another@email.com', 'email')
             ->press(__('Actadmin::generic.save'))
             ->seePageIs($this->listOfUsers)
             ->seeInDatabase(
                 'users',
                 ['email' => 'another@email.com']
             );
    }

    public function testCanEditUserPassword()
    {
        $this->visit(route('actadmin.profile'))
             ->click(__('Actadmin::profile.edit'))
             ->see(__('Actadmin::profile.edit_user'))
             ->seePageIs($this->editPageForTheCurrentUser)
             ->type('actadmin-rocks', 'password')
             ->press(__('Actadmin::generic.save'))
             ->seePageIs($this->listOfUsers);

        $updatedPassword = DB::table('users')->where('id', 1)->first()->password;
        $this->assertTrue(Hash::check('actadmin-rocks', $updatedPassword));
    }

    public function testCanEditUserAvatar()
    {
        $this->visit(route('actadmin.profile'))
             ->click(__('Actadmin::profile.edit'))
             ->see(__('Actadmin::profile.edit_user'))
             ->seePageIs($this->editPageForTheCurrentUser)
             ->attach($this->newImagePath(), 'avatar')
             ->press(__('Actadmin::generic.save'))
             ->seePageIs($this->listOfUsers)
             ->dontSeeInDatabase(
                 'users',
                 ['id' => 1, 'avatar' => 'user/default.png']
             );
    }

    public function testCanEditUserEmailWithEditorPermissions()
    {
        $user = factory(\ACT\Actadmin\Models\User::class)->create();
        $editPageForTheCurrentUser = route('actadmin.users.edit', ['user' => $user->id]);
        $roleId = $user->role_id;
        $role = Role::find($roleId);
        // add permissions which reflect a possible editor role
        // without permissions to edit  users
        $role->permissions()->attach(\ACT\Actadmin\Models\Permission::whereIn('key', [
            'browse_admin',
            'browse_users',
        ])->get()->pluck('id')->all());
        Auth::onceUsingId($user->id);
        $this->visit(route('actadmin.profile'))
             ->click(__('Actadmin::profile.edit'))
             ->see(__('Actadmin::profile.edit_user'))
             ->seePageIs($editPageForTheCurrentUser)
             ->type('another@email.com', 'email')
             ->press(__('Actadmin::generic.save'))
             ->seePageIs($this->listOfUsers)
             ->seeInDatabase(
                 'users',
                 ['email' => 'another@email.com']
             );
    }

    public function testCanSetUserLocale()
    {
        $this->visit(route('actadmin.profile'))
             ->click(__('Actadmin::profile.edit'))
             ->see(__('Actadmin::profile.edit_user'))
             ->seePageIs($this->editPageForTheCurrentUser)
             ->select('de', 'locale')
             ->press(__('Actadmin::generic.save'));

        $user = User::find(1);
        $this->assertTrue(($user->locale == 'de'));
    }

    protected function newImagePath()
    {
        return realpath(__DIR__.'/temp/new_avatar.png');
    }
}
