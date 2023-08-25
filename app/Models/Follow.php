<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'user1_id',         
        'user2_id',                  
    ];








    
    ####################### Relations Begin #######################
    public function follow_user(){
        return $this -> belongsTo('App\Models\User','user1_id');
    }

    public function fllowed_by_user(){
        return $this -> belongsTo('App\Models\User','user2_id');
    }

    #######################  Relations End  #######################

}
