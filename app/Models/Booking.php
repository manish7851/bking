<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{   
    protected $table = 'bookings';
    
    protected $fillable = [
        'user_id', 'customer_id', 'route_id', 'bus_id', 'bus_number', 'bus_name', 
        'seat', 'price', 'status', 'contact_number', 'source', 'destination',
        'payment_status', 'payment_method', 'created_by_admin',
        'qr_code_path', 'verification_code', 'qr_generated_at', 'pickup_location', 'pickup_remark', 'dropoff_location', 'dropoff_remark'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }
}
