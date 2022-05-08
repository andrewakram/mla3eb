<?php

namespace Database\Seeders;

use App\Models\Stadium;
use Illuminate\Database\Seeder;

class StadiumSeeder extends Seeder
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
                'name' => 'stadium1',
            ],
            [
                'name' => 'stadium2',
            ],
            [
                'name' => 'stadium3',
            ],
        ];
        foreach ($data as $get) {
            Stadium::updateOrCreate($get);
        }
    }
}
