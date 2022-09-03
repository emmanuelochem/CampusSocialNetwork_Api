<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConnectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('connections', function (Blueprint $table) {
            $table->unsignedBigInteger('follower_id')->index();
            $table->unsignedBigInteger('following_id')->index();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('follower_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->foreign('following_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->primary(array('follower_id', 'following_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('connections');
    }
}
