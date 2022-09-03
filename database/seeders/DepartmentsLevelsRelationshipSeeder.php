<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentsLevelsRelationshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        DB::table('departments_levels_relationship')->insert([
                [
                    'department_id' => 1,
                    'level_id' => 1,
                ],
                [
                    'department_id' => 1,
                    'level_id' => 2,
                ],
                [
                    'department_id' => 1,
                    'level_id' => 3,
                ],
                [
                    'department_id' => 1,
                    'level_id' => 4,
                ],
                [
                    'department_id' => 1,
                    'level_id' => 5,
                ],
                [
                    'department_id' => 1,
                    'level_id' => 6,
                ],
                [
                    'department_id' => 1,
                    'level_id' => 7,
                ],
                [
                    'department_id' => 1,
                    'level_id' => 8,
                ],
                //Chemical
                [
                    'department_id' => 2,
                    'level_id' => 1,
                ],

                //Civil
                [
                    'department_id' => 3,
                    'level_id' => 2,
                ],

                //Elect
                [
                    'department_id' => 4,
                    'level_id' => 3,
                ],
            ]
        );
    }
}
