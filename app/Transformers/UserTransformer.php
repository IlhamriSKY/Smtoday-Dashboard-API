<?php

namespace Vanguard\Transformers;

use League\Fractal\TransformerAbstract;
use Vanguard\Repositories\Country\CountryRepository;
use Vanguard\Repositories\Role\RoleRepository;
use Vanguard\User;

class UserTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['role', 'country'];

    public function transform(User $user)
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->present()->avatar,
            'address' => $user->address,
            'country_id' => $user->country_id ? (int) $user->country_id : null,
            'role_id' => (int) $user->role_id,
            'status' => $user->status,
            'birthday' => $user->birthday ? $user->birthday->format('Y-m-d') : null,
            'last_login' => (string) $user->last_login,
            'two_factor_country_code' => (int) $user->two_factor_country_code,
            'two_factor_phone' => (string) $user->two_factor_phone,
            'two_factor_options' => json_decode($user->two_factor_options, true),
            'email_verified_at' => $user->email_verified_at ? (string) $user->email_verified_at : null,
            'created_at' => (string) $user->created_at,
            'updated_at' => (string) $user->updated_at
        ];
    }

    public function includeRole(User $user)
    {
        if (! auth('api')->user()->hasPermission('roles.manage')) {
            return null;
        }

        return $this->item($user->role, new RoleTransformer);
    }

    public function includeCountry(User $user)
    {
        return $user->country
            ? $this->item($user->country, new CountryTransformer)
            : null;
    }
}
