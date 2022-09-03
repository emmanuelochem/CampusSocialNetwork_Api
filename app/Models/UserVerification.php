<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVerification extends Model
{
    use HasFactory;
    protected $fillable = [
        'type','data','token','token_expires_at','is_verified'
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'is_verified' => 'boolean'
    ];

    protected $hidden = [
        'token'
    ];
}
