<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'user_id',            //foriegn key
        'article_id',          //foriegn key
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
