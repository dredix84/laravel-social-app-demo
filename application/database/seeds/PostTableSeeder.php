<?php

use Illuminate\Database\Seeder;

class PostTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Post::class, 30)->create()->each(function ($p) {
            $faker = Faker\Factory::create();
            $p->save();
            for ($x = 0; $x < rand(0, 5); $x++) {
                $comment = \App\Comment::create([
                    'id'      => \Ramsey\Uuid\Uuid::uuid4(),
                    'post_id' => $p->toArray()['id'],
                    'body'    => $faker->sentence,
                    'user_id' => rand(1, 8)
                ]);
            }
        });
    }
}
