<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

include 'LikeTableSeeder.php';
include 'PostTableSeeder.php';
include 'UserTableSeeder.php';
include 'CommentTableSeeder.php';

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        putenv("CACHE2DB=true");
//        Model::unguard();

        $this->call(UserTableSeeder::class);
        $this->call(PostTableSeeder::class);
        $this->call(LikeTableSeeder::class);

//        Model::reguard();
    }
}
