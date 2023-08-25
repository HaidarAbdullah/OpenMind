<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;
    
   protected $table = "replies";
    protected $fillable = [
        'id',
        'user_id',
        'comment_id',      
        'content',        
    ];
    
    ####################### Relations Begin #######################
    public function user(){
        return $this -> belongsTo('App\Models\User','user_id');
    }
    
    public function comment(){
        return $this -> belongsTo('App\Models\Comment','comment_id');
    }
    
    #######################  Relations End  #######################

}