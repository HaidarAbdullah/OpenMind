<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'author_id',
        'abstract',
        'content',
        'img_src',
          'likes',
          'views'        
    ];

    ####################### Relations Begin #######################
    public function author(){
        return $this -> belongsTo('App\Models\User','author_id');
    }
    
    public function comments(){
        return $this -> hasMany('App\Models\Comment','article_id');
    }

    public function references(){
        return $this -> hasMany('App\Models\Reference','article_id');
    }

    public function views(){
        return $this -> hasMany('App\Models\View','article_id');
    }

    #######################  Relations End  #######################



}
