<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Vanguard\Permission::class, function (Faker $faker) {
    return [
        'name' => Str::random(5),
        'display_name' => implode(" ", $faker->words(2)),
        'description' => substr($faker->paragraph, 0, 191),
        'removable' => true
    ];
});
