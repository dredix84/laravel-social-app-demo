<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name'              => 'Andre D',
            'email'             => 'admin@test.com',
            'password'          => bcrypt('test123'),
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10),
        ]);
        DB::table('users')->insert([
            'name'              => 'Other User',
            'email'             => 'other@test.com',
            'password'          => bcrypt('test123'),
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10),
        ]);
        DB::table('users')->insert([
            'name'              => 'Other User2',
            'email'             => 'other2@test.com',
            'password'          => bcrypt('test123'),
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10),
        ]);

        factory(App\User::class, 5)->create()->each(function ($u) {
            $u->save();
        });
    }
}
