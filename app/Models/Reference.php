<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'description',
        'link', 
        'article_id',          //foriegn key
    ];
    
    ####################### Relations Begin #######################
    public function article(){
        return $this -> belongsTo('App\Models\Article','article_id');
    }
    
    #######################  Relations End  #######################

}
