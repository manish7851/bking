<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusLocation extends Model
{
    protected $fillable = [
        'bus_id',
        'bus_tracking_id',
        'latitude',
        'longitude',
        'speed',
        'heading',
        'recorded_at',
        'address'
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function busTracking()
    {
        return $this->belongsTo(BusTracking::class);
    }
}
