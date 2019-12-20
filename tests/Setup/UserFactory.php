<?php

namespace Tests\Setup;

use Vanguard\Role;
use Vanguard\Support\Enum\UserStatus;
use Vanguard\User;
use \Facades\Tests\Setup\RoleFactory;

class UserFactory
{
    protected $params = [];
    protected $role;

    public function withCredentials($username, $password)
    {
        $this->params['username'] = $username;
        $this->params['password'] = $password;

        return $this;
    }

    public function banned()
    {
        $this->params['status'] = UserStatus::BANNED;

        return $this;
    }

    public function unconfirmed()
    {
        $this->params['status'] = UserStatus::UNCONFIRMED;

        return $this;
    }

    public function unverified()
    {
        $this->params['email_verified_at'] = null;

        return $this;
    }

    public function role(Role $role)
    {
        $this->role = $role;

        return $this;
    }

    public function admin()
    {
        return $this->role(Role::where('name', 'Admin')->first());
    }

    public function user()
    {
        return $this->role(Role::where('name', 'User')->first());
    }

    public function email($email)
    {
        $this->params['email'] = $email;

        return $this;
    }

    public function twoFactor($countryCode, $phone)
    {
        $this->params['two_factor_country_code'] = $countryCode;
        $this->params['two_factor_phone'] = $phone;

        return $this;
    }

    public function withPermissions($permissions)
    {
        $permissions = func_get_args();

        $this->role(
            RoleFactory::withPermissions($permissions)->create()
        );

        return $this;
    }

    public function create(array $overrides = [])
    {
        if ($this->role) {
            $this->params['role_id'] = $this->role;
        }

        return factory(User::class)->create(array_merge($this->params, $overrides));
    }
}
