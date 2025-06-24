<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
   

protected $fillable = [
    'bus_name',
    'bus_number',
    'imei',
    'latitude',
    'longitude',
    'speed',
    'heading',
    'last_tracked_at',
    'tracking_enabled',
    'current_tracking_id',
    'status',
    'custom_path_source',
    'custom_path_destination'
];


 public function routes()
    {
        return $this->hasMany(Route::class);
    }


    public function bookings()
{
    return $this->hasMany(Booking::class, 'bus_number', 'bus_number');
}

    public function locations()
    {
        return $this->hasMany(BusLocation::class);
    }
    
    public function enableTracking()
    {
        $this->tracking_enabled = true;
        $this->last_tracked_at = now();
        $this->save();
    }

    public function disableTracking()
    {
        $this->tracking_enabled = false;
        $this->save();
    }    public function updateLocation($latitude, $longitude, $speed = null, $heading = null)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        
        if ($speed !== null) {
            $this->speed = $speed;
        }
        
        if ($heading !== null) {
            $this->heading = $heading;
        }
        
        $this->last_tracked_at = now();
        $this->status = ($speed > 2) ? 'moving' : 'stopped';
        $this->save();
        
        // Broadcast location update for real-time tracking
        event(new \App\Events\BusLocationUpdated($this));
    }

    public function scopeTrackingEnabled($query)
    {
        return $query->where('tracking_enabled', true);
    }

    public function currentTracking()
    {
        return $this->belongsTo(BusTracking::class, 'current_tracking_id');
    }
}
