<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'keyword'
    ];
    

    ####################### Relations Begin #######################
    public function references(){
        return $this -> hasMany('App\Models\Reference','tag_id');
    }

    #######################  Relations End  #######################

}
