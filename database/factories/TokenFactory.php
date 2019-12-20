<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(\Vanguard\Services\Auth\Api\Token::class, function (Faker $faker) {
    return [
        'id' => Str::random(40),
        'user_id' => function () {
            return factory(\Vanguard\User::class)->create()->id;
        },
        'ip_address' => $faker->ipv4,
        'user_agent' => substr($faker->userAgent, 0, 500),
        'expires_at' => \Carbon\Carbon::now()->addMinutes(60)
    ];
});
