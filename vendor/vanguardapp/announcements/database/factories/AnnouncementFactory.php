<?php

use Faker\Generator as Faker;
use Vanguard\Announcements\Announcement;

$factory->define(Announcement::class, function (Faker $faker) {
    return [
        'title' => $faker->title,
        'body' => $faker->paragraph(2),
        'user_id' => function () {
            return factory(\Vanguard\User::class)->create()->id;
        },
    ];
});
