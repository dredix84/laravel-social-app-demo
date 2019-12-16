<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(\App\Comment::class, function (Faker $faker) {
    return [
        'id'      => Uuid::uuid4(),
        'body'    => $faker->sentence(rand(3, 10), true),
        'user_id' => rand(1, 3),
//        'post_id' => rand(1, 30),
        'key'     => rand(10000, 99999),
    ];
});
