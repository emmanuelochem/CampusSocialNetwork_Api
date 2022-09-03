<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacultiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('faculties')->insert([
                [
                    'name' => 'Engineering',
                ],
                [
                    'name' => 'Medicine',
                ],
                [
                    'name' => 'Arts',
                ],
                [
                    'name' => 'Management Sciences',
                ],
                [
                    'name' => 'Sciences',
                ],
                [
                    'name' => 'Vetenary Medicine',
                ],
                [
                    'name' => 'Law',
                ]
        ]
           );

    }
}
