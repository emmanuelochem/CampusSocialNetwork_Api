<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'faculty_id'
    ];


    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }


//    public function levels(){
//        return $this->hasManyThrough(
//            Level::class, 'departments_levels_relationship', 'department_id', 'level_id');
//    }



    public function  toArray()
    {
        //return parent::toArray();
        return [
            'id'=>$this->id,
            'name'=>$this->name
        ];
    }
}
