<?php

namespace Vanguard\UserActivity\Widgets;

use Auth;
use Carbon\Carbon;
use Vanguard\Plugins\Widget;
use Vanguard\User;
use Vanguard\UserActivity\Repositories\Activity\ActivityRepository;

class ActivityWidget extends Widget
{
    /**
     * {@inheritdoc}
     */
    public $width = '12';

    /**
     * @var ActivityRepository
     */
    private $activities;

    /**
     * @var array The list of user activity records.
     */
    private $userActivity;

    public function __construct(ActivityRepository $activities)
    {
        $this->activities = $activities;

        $this->permissions(function (User $user) {
            return $user->hasRole('User');
        });
    }

    public function render()
    {
        return view('user-activity::widgets.user-activity', [
            'activities' => $this->getActivity()
        ]);
    }

    public function scripts()
    {
        return view('user-activity::widgets.user-activity-scripts', [
            'activities' => $this->getActivity()
        ]);
    }

    private function getActivity()
    {
        if ($this->userActivity) {
            return $this->userActivity;
        }

        return $this->userActivity = $this->activities->userActivityForPeriod(
            Auth::user()->id,
            Carbon::now()->subWeeks(2),
            Carbon::now()
        )->toArray();
    }
}
