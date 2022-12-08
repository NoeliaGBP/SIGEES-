<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonRooms extends Model
{
    use HasFactory;

    protected $table = "person_rooms";

    protected $fillable = [
        "id",
        "person_id",
        "room_id",
        "status_id",
        "updated_at"
    ];

    public $timestamps = false;

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }

    public function observations()
    {
        return $this->hasMany(Observation::class, 'id');
    }
}
