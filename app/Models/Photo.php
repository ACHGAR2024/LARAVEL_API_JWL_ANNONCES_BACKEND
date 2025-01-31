<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'photo_path',
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}