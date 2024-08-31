<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadedFood extends Model
{
    use HasFactory;

    protected $fillable = [
        'uploaded_by',
        'food_items',
        'image',
        'description',
        'location',
        'is_accepted',
        'accepted_by',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
