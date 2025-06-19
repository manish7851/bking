<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $table = 'routes';

    protected $fillable = [
        'source',
        'destination',
        'trip_date',
        'price',
        'bus_id'
    ];

    protected $casts = [
        'trip_date' => 'datetime'
    ];

    public function bus() 
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    

    

    
}
