<?php

namespace Tests\Unit\Presenters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;
use Vanguard\Presenters\UserPresenter;
use Vanguard\Support\Enum\UserStatus;

class UserPresenterTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new PresentableUser;
    }

    /** @test */
    public function name()
    {
        $this->assertEquals('John Doe', $this->user->present()->name);
    }

    /** @test */
    public function nameOrEmail()
    {
        $this->assertEquals('John Doe', $this->user->present()->nameOrEmail);

        $this->user->first_name = '';
        $this->assertEquals('Doe', $this->user->present()->nameOrEmail);

        $this->user->last_name = '';
        $this->assertEquals('john.doe@test.com', $this->user->present()->nameOrEmail);
    }

    /** @test */
    public function avatar()
    {
        $this->assertEquals(url('assets/img/profile.png'), $this->user->present()->avatar);

        $this->user->avatar = "//www.gravatar.com/avatar";
        $this->assertEquals("//www.gravatar.com/avatar", $this->user->present()->avatar);

        $this->user->avatar = "http://somewebsite.com/avatar.png";
        $this->assertEquals("http://somewebsite.com/avatar.png", $this->user->present()->avatar);

        $this->user->avatar = "foo.png";
        $this->assertEquals(url("upload/users/foo.png"), $this->user->present()->avatar);
    }

    /** @test */
    public function birthday()
    {
        $this->assertEquals('N/A', $this->user->present()->birthday);

        $this->user->birthday = Carbon::now()->subYears(20);
        $this->assertEquals($this->user->birthday->toDateString(), $this->user->present()->birthday);
    }

    /** @test */
    public function fullAddress()
    {
        $this->assertEquals('Some Country', $this->user->present()->fullAddress);

        $this->user->address = 'Street 123';
        $this->assertEquals('Street 123, Some Country', $this->user->present()->fullAddress);

        $this->user->country_id = null;
        $this->user->country = null;
        $this->assertEquals('Street 123', $this->user->present()->fullAddress);

        $this->user->address = '';
        $this->assertEquals('N/A', $this->user->present()->fullAddress);
    }

    /** @test */
    public function lastLogin()
    {
        $this->assertEquals('N/A', $this->user->present()->lastLogin);

        $date = Carbon::now()->subDay(1);
        $this->user->last_login = $date->copy();
        $this->assertEquals($date->diffForHumans(), $this->user->present()->lastLogin);
    }

    /** @test */
    public function labelClass()
    {
        $this->assertEquals('success', $this->user->present()->labelClass);

        $this->user->status = UserStatus::BANNED;
        $this->assertEquals('danger', $this->user->present()->labelClass);

        $this->user->status = UserStatus::UNCONFIRMED;
        $this->assertEquals('warning', $this->user->present()->labelClass);
    }
}

class PresentableUser extends Model
{
    use \Vanguard\Presenters\Traits\Presentable;

    protected $presenter = UserPresenter::class;

    public $first_name = 'John';
    public $last_name = 'Doe';
    public $email = 'john.doe@test.com';
    public $avatar = null;
    public $birthday = null;
    public $country_id = 1;
    public $country;
    public $address = '';
    public $last_login = null;
    public $status = UserStatus::ACTIVE;

    public function __construct()
    {
        $this->country = new StubCountry;
    }
}

class StubCountry
{
    public $id = 1;
    public $name = 'Some Country';
}
