<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fname');
            $table->string('lname');
            $table->string('phone')->unique();
            $table->string('otp')->nullable();
            $table->string('gender')->nullable();
            $table->string('photo')->default('https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y&s=480');
            $table->unsignedBigInteger('department')->index();
            $table->foreign('department')->references('id')->on('departments')->cascadeOnDelete();
            $table->unsignedBigInteger('level')->index();
            $table->foreign('level')->references('id')->on('levels')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
