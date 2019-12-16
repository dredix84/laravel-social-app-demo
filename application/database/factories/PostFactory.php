<?php

/** @var Factory $factory */

use App\Post;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
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

$factory->define(Post::class, function (Faker $faker) {
    return [
        'id'      => Uuid::uuid4(),
        'title'     => $faker->sentence(rand(3, 7), true),
        'image_url' => "https://picsum.photos/id/".rand(1, 50)."/600/300",
//        'image_url' => $faker->imageUrl(600, 300),
        'body'      => $faker->text(),
        'user_id'   => rand(1, 8),
        'key'       => rand(10000, 99999),
    ];
});
