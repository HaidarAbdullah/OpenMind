<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aritcle extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'abstract',
        'content',
        'user_id'          //foriegn key
    ];

}


