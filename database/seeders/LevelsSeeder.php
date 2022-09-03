<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('levels')->insert([
                [
                    'name' => 'Remedial',
                ],
                [
                    'name' => '100 Level',
                ],
                [
                    'name' => '200 Level',
                ],
                [
                    'name' => '300 Level',
                ],
                [
                    'name' => '400 Level',
                ],
                [
                    'name' => '500 Level',
                ],
                [
                    'name' => '600 Level',
                ],
                [
                    'name' => 'Post Graduate',
                ]
            ]
        );
    }
}
