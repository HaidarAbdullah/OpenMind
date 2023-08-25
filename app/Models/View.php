<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class View extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'user_id',
        'article_id',
        'progress',
        'time',
        'elapsed_time'    
    ];

    
    ####################### Relations Begin #######################
    public function user(){
        return $this -> belongsTo('App\Models\User','user_id');
    }

    public function article(){
        return $this -> belongsTo('App\Models\Article','article_id');
    }
    
    #######################  Relations End  #######################


}
