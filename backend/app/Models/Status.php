<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $table = "status";

    protected $fillable = [
        "id",
        "name",
        "status"
    ];
    
    public $timestamps = false;

    public function rooms()
    {
        return $this->hasMany(Room::class, 'id', 'room_id');
    }

    public function person_rooms()
    {
        return $this->hasMany(PersonRooms::class, 'id', 'person_room_id');
    }

    public function observations()
    {
        return $this->hasMany(Observation::class, 'id', 'observation_id');
    }
}
