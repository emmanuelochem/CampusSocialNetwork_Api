<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    use HasFactory;

//    protected $with = [
//        //'users:id,nickname,photo',
//        // 'messages'
//    ];



    protected $hidden = [
        'pivot',
        'updated_at',
    ];

    // protected $appends = [
    //     'is_group',
    // ];


    /**
     * Check if the conversation is group.
     *
     * @return bool
     */
    // public function getIsGroupAttribute(): bool
    // {
    //     return $this->type === 'group';
    // }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
 
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

//    /**
//     * The channels the user receives notification broadcasts on.
//     *
//     * @return string
//     */
//    public function receivesBroadcastNotificationsOn()
//    {
//        return 'conversations.'.$this->id;
//    }
}
