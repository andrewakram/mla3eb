<?php

namespace Database\Seeders;

use App\Models\Pitch;
use Illuminate\Database\Seeder;

class PitchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i < 4; $i++) {
            for ($j = 1; $j < 4; $j++) {
                Pitch::updateOrCreate([
                    'name' => 'Pitch'.$j. ' stadium'.$i,
                    'stadium_id' => $i,
                    'slot' => $j % 2 < 1 ? '60' : '90',
                ]);
            }
        }

    }
}
