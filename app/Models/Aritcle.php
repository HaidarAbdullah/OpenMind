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

    ####################### Relations Begin #######################
    public function user(){
        return $this -> belongsTo('App\Models\User','User_id');
    }
    
    public function comments(){
        return $this -> hasMany('App\Models\Comment','article_id');
    }

    public function tags(){
        return $this -> hasMany('App\Models\Tag','article_id');
    }

    public function references(){
        return $this -> hasMany('App\Models\Reference','article_id');
    }

    #######################  Relations End  #######################

}


