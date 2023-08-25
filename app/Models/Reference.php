<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'article_id',
        'tag_id', 
    ];
    
    ####################### Relations Begin #######################
    public function article(){
        return $this -> belongsTo('App\Models\Article','article_id');
    }
    public function tag(){
        return $this -> belongsTo('App\Models\Tag','tag_id');
    }
    
    #######################  Relations End  #######################

}
