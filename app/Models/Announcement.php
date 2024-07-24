<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;


        protected $fillable = [
            'title','description','price','user_id','photo','publication_date','place'
        ];
        


    protected $casts = [
        'date_de_publication' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'ad_categories', 'announcement_id', 'category_id');
    }
}