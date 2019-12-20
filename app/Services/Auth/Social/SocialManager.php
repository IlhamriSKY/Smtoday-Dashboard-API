<?php

namespace Vanguard\Services\Auth\Social;

use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialUser;
use Vanguard\Repositories\Role\RoleRepository;
use Vanguard\Repositories\User\UserRepository;
use Vanguard\Support\Enum\UserStatus;

class SocialManager
{
    use ManagesSocialAvatarSize;

    /**
     * @var UserRepository
     */
    private $users;
    /**
     * @var RoleRepository
     */
    private $roles;

    /**
     * SocialManager constructor.
     * @param UserRepository $users
     * @param RoleRepository $roles
     */
    public function __construct(UserRepository $users, RoleRepository $roles)
    {
        $this->users = $users;
        $this->roles = $roles;
    }

    /**
     * Associate social user with given provider. If user with the same email address
     * retrieved from social network exists in our database, we will just associate it
     * with provided social account. If not, user will be created.
     *
     * @param SocialUser $socialUser
     * @param string $provider
     * @return mixed|null|\Vanguard\User
     */
    public function associate(SocialUser $socialUser, $provider)
    {
        $user = $this->users->findByEmail($socialUser->getEmail());

        if (! $user) {
            // User with email retrieved from social auth provider does not
            // exist in our database. That means that we have to create new user here
            list($firstName, $lastName) = $this->parseUserFullName($socialUser);

            $role = $this->roles->findByName('User');

            $user = $this->users->create([
                'email' => $socialUser->getEmail(),
                'password' => Str::random(10),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'status' => UserStatus::ACTIVE,
                'avatar' => $this->getAvatarForProvider($provider, $socialUser),
                'role_id' => $role->id
            ]);
        }

        // Associate social account with user account inside our application
        $this->users->associateSocialAccountForUser($user->id, $provider, $socialUser);

        return $user;
    }

    /**
     * Parse User's name from his social network account.
     *
     * @param SocialUser $user
     * @return array
     */
    private function parseUserFullName(SocialUser $user)
    {
        $name = $user->getName();

        if (strpos($name, " ") !== false) {
            return explode(" ", $name, 2);
        }

        return [$name, ''];
    }
}
