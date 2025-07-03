<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'bus_id',
        'type', // 'overspeed', 'deviation', 'idle', 'geofence', 'battery', etc.
        'message',
        'data', // JSON for extra info
        'severity', // 'info', 'warning', 'critical'
        'is_read',
        'latitude',
        'longitude'
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];
    
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
