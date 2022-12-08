<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $table = "schedules";

    protected $fillable = [
        "id",
        "start_time",
        "end_time",
        "person_id",
        "status"
    ];
    
    public $timestamps = false;

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id', 'id');
    }
}
