<?php

namespace Vanguard\Repositories\User;

use Carbon\Carbon;
use Vanguard\User;
use \Laravel\Socialite\Contracts\User as SocialUser;

interface UserRepository
{
    /**
     * Paginate registered users.
     *
     * @param $perPage
     * @param null $search
     * @param null $status
     * @return mixed
     */
    public function paginate($perPage, $search = null, $status = null);

    /**
     * Find user by its id.
     *
     * @param $id
     * @return null|User
     */
    public function find($id);

    /**
     * Find user by email.
     *
     * @param $email
     * @return null|User
     */
    public function findByEmail($email);

    /**
     * Find user registered via social network.
     *
     * @param $provider string Provider used for authentication.
     * @param $providerId string Provider's unique identifier for authenticated user.
     * @return mixed
     */
    public function findBySocialId($provider, $providerId);

    /**
     * Find user by specified session id.
     *
     * @param $sessionId
     * @return mixed
     */
    public function findBySessionId($sessionId);

    /**
     * Create new user.
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Update user specified by it's id.
     *
     * @param $id
     * @param array $data
     * @return User
     */
    public function update($id, array $data);

    /**
     * Delete user with provided id.
     *
     * @param $id
     * @return mixed
     */
    public function delete($id);

    /**
     * Associate account details returned from social network
     * to user with provided user id.
     *
     * @param $userId
     * @param $provider
     * @param SocialUser $user
     * @return mixed
     */
    public function associateSocialAccountForUser($userId, $provider, SocialUser $user);

    /**
     * Number of users in database.
     *
     * @return mixed
     */
    public function count();

    /**
     * Number of users registered during current month.
     *
     * @return mixed
     */
    public function newUsersCount();

    /**
     * Number of users with provided status.
     *
     * @param $status
     * @return mixed
     */
    public function countByStatus($status);

    /**
     * Count of registered users for every month within the
     * provided date range.
     *
     * @param $from
     * @param $to
     * @return mixed
     */
    public function countOfNewUsersPerMonth(Carbon $from, Carbon $to);

    /**
     * Get latest {$count} users from database.
     *
     * @param $count
     * @return mixed
     */
    public function latest($count = 20);

    /**
     * Set specified role to specified user.
     *
     * @param $userId
     * @param $roleId
     * @return mixed
     */
    public function setRole($userId, $roleId);

    /**
     * Change role for all users who has role $fromRoleId to $toRoleId.
     *
     * @param $fromRoleId Id of current role.
     * @param $toRoleId Id of new role.
     * @return mixed
     */
    public function switchRolesForUsers($fromRoleId, $toRoleId);

    /**
     * Get all users with provided role.
     *
     * @param $roleName
     * @return mixed
     */
    public function getUsersWithRole($roleName);

    /**
     * Get all social login records for specified user.
     *
     * @param $userId
     * @return mixed
     */
    public function getUserSocialLogins($userId);

    /**
     * Find user by confirmation token.
     *
     * @param $token
     * @return mixed
     */
    public function findByConfirmationToken($token);
}