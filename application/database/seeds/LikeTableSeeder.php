<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class LikeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users    = \App\User::all()->toArray();
        $comments = \App\Comment::all()->toArray();
        $faker    = Faker\Factory::create();

        foreach ($comments as $comment) {

//            dd($comment);
            foreach ($users as $user) {
                $saveComment = rand(0, 2);
                if ($saveComment >= 1) {
                    DB::table('likes')->insert([
                        'id'         => Uuid::uuid4(),
                        'type'       => 'comment',
                        'comment_id' => $comment['id'],
                        'user_id'    => $user['id'],
                        'created_at' => $faker->dateTime,
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
