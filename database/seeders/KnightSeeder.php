<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Services\Knights;
use App\Models\Knight;

class KnightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $knights = [
            ['name' => "White Knight"],
            ['name' => "Black Knight"],
            ['name' => "Grey Knight"],
            ['name' => "Red Knight"],
        ];

        Knight::upsert($knights, []);
    }
}
