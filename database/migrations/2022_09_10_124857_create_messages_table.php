<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_id');
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->longText('content');
            $table->enum('receipt', ['sent','delivered', 'read'])->default('sent');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
            $table->foreign('sender_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->foreign('receiver_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->foreign('chat_id')
            ->references('id')
            ->on('chats')
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
        Schema::dropIfExists('messages');
    }
}
