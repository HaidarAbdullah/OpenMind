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
    
    ####################### Relations Begin #######################
    public function user(){
        return $this -> belongsTo('App\Models\User','User_id');
    }
    
    public function comment(){
        return $this -> belongsTo('App\Models\Comment','comment_id');
    }
    
    #######################  Relations End  #######################

}