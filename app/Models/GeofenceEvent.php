<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeofenceEvent extends Model
{
    protected $fillable = [
        'bus_id', 
        'geofence_id', 
        'event_type', 
        'event_time',
        'latitude',
        'longitude',
        'speed'
    ];
    
    protected $casts = [
        'event_time' => 'datetime',
    ];
    
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }
    
    public function geofence()
    {
        return $this->belongsTo(Geofence::class);
    }
}
