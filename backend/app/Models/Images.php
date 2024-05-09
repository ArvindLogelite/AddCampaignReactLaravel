<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Images extends Model
{
    use HasFactory;
    protected $table = 'images';
    protected $fillable = [
        'image1',
        'image2',
        'image3',
        'image4',
        'image5',
        'image6',
    ];
}
