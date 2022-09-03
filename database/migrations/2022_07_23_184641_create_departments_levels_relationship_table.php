<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentsLevelsRelationshipTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('departments_levels_relationship', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->index();
            $table->unsignedBigInteger('level_id')->index();
            $table->foreign('department_id')
                ->references('id')->on('departments')
                ->cascadeOnDelete();
            $table->foreign('level_id')
                ->references('id')->on('levels')
                ->cascadeOnDelete();
            $table->primary(array('department_id', 'level_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('departments_levels_relationship');
    }
}
