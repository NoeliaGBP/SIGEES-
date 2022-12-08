<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $table = "persons";

    protected $fillable = [
        "id",
        "name",
        "surname",
        "second_surname",
        "status"
    ];
    
    public $timestamps = false;

    public function person_room()
    {
        return $this->hasMany(PersonRooms::class, 'person_room_id', 'id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'schedule_id', 'id');
    }
}
