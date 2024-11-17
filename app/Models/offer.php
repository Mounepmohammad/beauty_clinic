<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'photo',
    ];

    // تضمين URL الكامل للصورة
    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? Storage::url($this->photo) : null;
    }
}
