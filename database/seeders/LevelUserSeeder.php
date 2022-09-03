<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        //

        DB::table('level_user_relationship')->insert([
                [
                    'user_id' => 6,
                    'level_id' => 1,
                ],
                [
                    'user_id' => 6,
                    'level_id' => 2,
                ],
            ]
        );
    }
}
