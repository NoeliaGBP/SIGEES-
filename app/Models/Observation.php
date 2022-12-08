<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Observation extends Model
{
    use HasFactory;

    protected $table = "observations";

    protected $fillable = [
        "id",
        "description",
        "photo",
        "person_room_id",
        "status_id"
    ];
    
    public $timestamps = false;

    public function personRoom()
    {
        return $this->belongsTo(PersonRooms::class, 'person_room_id', 'id');
    }
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }
}
