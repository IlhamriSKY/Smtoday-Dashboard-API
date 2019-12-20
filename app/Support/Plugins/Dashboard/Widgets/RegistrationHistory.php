<?php

namespace Vanguard\Support\Plugins\Dashboard\Widgets;

use Carbon\Carbon;
use Vanguard\Plugins\Widget;
use Vanguard\Repositories\User\UserRepository;

class RegistrationHistory extends Widget
{
    /**
     * {@inheritdoc}
     */
    public $width = '8';

    /**
     * @var string
     */
    protected $permissions = 'users.manage';

    /**
     * @var UserRepository
     */
    private $users;

    /**
     * @var array Count of new users per month.
     */
    protected $usersPerMonth;

    /**
     * RegistrationHistory constructor.
     * @param UserRepository $users
     */
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return view('plugins.dashboard.widgets.registration-history', [
            'usersPerMonth' => $this->getUsersPerMonth()
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function scripts()
    {
        return view('plugins.dashboard.widgets.registration-history-scripts', [
            'usersPerMonth' => $this->getUsersPerMonth()
        ]);
    }

    private function getUsersPerMonth()
    {
        if ($this->usersPerMonth) {
            return $this->usersPerMonth;
        }

        return $this->usersPerMonth = $this->users->countOfNewUsersPerMonth(
            Carbon::now()->subYear()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );
    }
}
