<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => 'user1',
                'email' => 'user1@gmail.com',
                'phone' => '01234561',
                'password' => '123456',
            ],
            [
                'name' => 'user2',
                'email' => 'user2@gmail.com',
                'phone' => '01234562',
                'password' => '123456',
            ],
            [
                'name' => 'user3',
                'email' => 'user3@gmail.com',
                'phone' => '01234563',
                'password' => '123456',
            ],
        ];
        foreach ($data as $get) {
            User::updateOrCreate($get);
        }
    }
}
