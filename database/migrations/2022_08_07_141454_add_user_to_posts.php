<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserToPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
                        //
                        $table->longText('caption')->change();
                        $table->longText('image')->change();
                        $table->string('audience')->default('followers')->change();
                        $table->string('allowcomment')->default('yes')->change();
                        $table->boolean('is_deleted')->default(false);
                        $table->unsignedBigInteger('user_id');
                        $table->foreign('user_id')
                            ->references('id')
                            ->on('users')
                            ->cascadeOnDelete();
                    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            //
        });
    }
}
