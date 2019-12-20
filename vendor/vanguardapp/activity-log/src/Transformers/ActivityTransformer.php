<?php

namespace Vanguard\UserActivity\Transformers;

use League\Fractal\TransformerAbstract;
use Vanguard\UserActivity\Activity;
use Vanguard\Transformers\UserTransformer;

class ActivityTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['user'];

    public function transform(Activity $activity)
    {
        $agent = app('agent');
        $agent->setUserAgent($activity->user_agent);

        return [
            'id' => (int) $activity->id,
            'user_id' => (int) $activity->user_id,
            'ip_address' => $activity->ip_address,
            'user_agent' => $activity->user_agent,
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
            'device' => $agent->device(),
            'description' => $activity->description,
            'created_at' => (string) $activity->created_at
        ];
    }

    public function includeUser(Activity $activity)
    {
        return $this->item($activity->user, new UserTransformer);
    }
}
