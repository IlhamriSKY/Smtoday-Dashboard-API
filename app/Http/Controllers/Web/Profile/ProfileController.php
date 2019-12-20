<?php

namespace Vanguard\Http\Controllers\Web\Profile;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Repositories\Country\CountryRepository;
use Vanguard\Repositories\Role\RoleRepository;
use Vanguard\Repositories\User\UserRepository;
use Vanguard\Support\Enum\UserStatus;

/**
 * Class ProfileController
 * @package Vanguard\Http\Controllers
 */
class ProfileController extends Controller
{
    /**
     * @var UserRepository
     */
    private $users;
    /**
     * @var RoleRepository
     */
    private $roles;
    /**
     * @var CountryRepository
     */
    private $countries;

    /**
     * @param UserRepository $users
     * @param RoleRepository $roles
     * @param CountryRepository $countries
     */
    public function __construct(UserRepository $users, RoleRepository $roles, CountryRepository $countries)
    {
        $this->users = $users;
        $this->roles = $roles;
        $this->countries = $countries;
    }

    /**
     * Display user's profile page.
     *
     * @return Factory|View
     */
    public function show()
    {
        $roles = $this->roles->all()->filter(function ($role) {
            return $role->id == auth()->user()->role_id;
        })->pluck('name', 'id');

        return view('user.profile', [
            'user' => auth()->user(),
            'edit' => true,
            'roles' => $roles,
            'countries' => [0 => __('Select a Country')] + $this->countries->lists()->toArray(),
            'socialLogins' => $this->users->getUserSocialLogins(auth()->id()),
            'statuses' => UserStatus::lists()
        ]);
    }
}
