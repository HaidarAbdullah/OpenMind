<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'user_id',          //foriegn key
        'brand_id',          //foriegn key
    ];
    
    ####################### Relations Begin #######################
    public function user(){
        return $this -> belongsTo('App\Models\User','User_id');
    }
    
    public function brand(){
        return $this -> belongsTo('App\Models\Brand','brand_id');
    }
    
    #######################  Relations End  #######################

}
