<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    use HasFactory;
    protected $fillable = [
        'name'
    ];

    protected $hidden = [
        'created_at','updated_at','pivot',
    ];


//    protected $with = [
//        'departments',
//    ];


    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }


//    public function departments(): belongsToMany
//    {
//        return $this->belongsToMany(Department::class, 'departments_levels_relationship', 'level_id', 'department_id');
//    }

//    public function  toArray()
//    {
//        //return parent::toArray();
//        return [
//            'id'=>$this->id,
//            'name'=>$this->name
//        ];
//    }
}
