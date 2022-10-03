<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;


    protected $casts = [
        'receiver_id'=> 'integer',
        'sender_id'=> 'integer',
    ];


    protected $appends = [
        'is_mine',
    ];

    protected $hidden = [
        'pivot',
       // 'updated_at',
    ];

    // protected $with = [
    //     'user',
    // ];
    /**
     * Check if the conversation is group.
     *
     * @return bool
     */
    public function getIsMineAttribute(): bool
    {
        return $this->sender_id === auth()->id();
        //return false;
    }

    /**
     * Get the chat that holds this message
     */
    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    /**
     * Get the user who sent this message
     */
    public function sender() {
        $this->hasOne(User::class, 'sender_id');
    }

    /**
     * Get the user who received this message
     */
    public function receiver() {
        $this->hasOne(User::class, 'receiver_id');
    }

   



}
