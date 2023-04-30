<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'address',
        'description'          
    ];
    
    ####################### Relations Begin #######################
    public function sponsors(){
        return $this -> hasMany('App\Models\Sponsor','brand_id');
    }
    
    #######################  Relations End  #######################

}
