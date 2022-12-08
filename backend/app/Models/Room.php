<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = "rooms";

    protected $fillable = [
        "id",
        "name",
        "building_id",
        "status_id"
    ];
    
    public $timestamps = false;

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }

    public function observations()
    {
        return $this->hasMany(Observation::class, 'id', 'observation_id');
    }
}
