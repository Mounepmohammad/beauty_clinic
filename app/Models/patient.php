<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id', 'name', 'service_name', 'phone', 'location',
    ];

    public function doctor()
    {
        return $this->belongsTo(doctor::class);
    }

    public function records()
    {
        return $this->hasMany(record::class);
    }
}
