<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        //Engineering Seeder
        DB::table('departments')->insert([
                [
                    'name' => 'Mechanical Engineering',
                    'faculty_id' => 1,
                ],
                [
                    'name' => 'Chemical Engineering',
                    'faculty_id' => 1,
                ],
                [
                    'name' => 'Civil Engineering',
                    'faculty_id' => 1,
                ],
                [
                    'name' => 'Electrical Engineering',
                    'faculty_id' => 1,
                ]
            ]
        );
    }
}
