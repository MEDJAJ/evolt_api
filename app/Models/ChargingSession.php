<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChargingSession extends Model
{

   

    protected $fillable = ['reservation_id', 'actual_start_time', 'actual_end_time', 'energy_delivered'];

public function reservation()
{
    return $this->belongsTo(Reservation::class);
}
}
