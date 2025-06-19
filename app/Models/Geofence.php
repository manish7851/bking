<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Geofence extends Model
{
    protected $fillable = [
        'name', 
        'center_lat', 
        'center_lng', 
        'radius', 
        'type',
        'description',
        'color'
    ];

    public function events()
    {
        return $this->hasMany(GeofenceEvent::class);
    }
    
    public function containsPoint($latitude, $longitude)
    {
        // Use the Haversine formula to calculate distance between points
        $earthRadius = 6371000; // in meters
        $latFrom = deg2rad($this->center_lat);
        $lonFrom = deg2rad($this->center_lng);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        $distance = $angle * $earthRadius;
        
        return $distance <= $this->radius;
    }
}
 

