<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'content',
        'user_id',          //foriegn key
        'comment_id',          //foriegn key
    ];
}
