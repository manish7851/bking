<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'isadmin',
        'email',
        'alert_id',
        'delivered',
    ];

    public function alert()
    {
        return $this->belongsTo(Alert::class);
    }
}
