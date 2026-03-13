<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class station extends Model
{
  protected $fillable = [
        'name',
        'location',
        'connector_type',
        'power_kw',
        'available'
    ];

    public function reservations()
{
    return $this->hasMany(Reservation::class);
}
}
