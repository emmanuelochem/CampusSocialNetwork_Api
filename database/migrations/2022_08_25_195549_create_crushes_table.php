<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrushesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crushes', function (Blueprint $table) {
            $table->unsignedBigInteger('crusher_id')->index();
            $table->unsignedBigInteger('crushing_id')->index();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('crusher_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->foreign('crushing_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->primary(array('crusher_id', 'crushing_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crushes');
    }
}
