<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'gender',
        'birth_date',
        'job_title',
        'img_src',
        'is_premium'       //foriegn key
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    ####################### Relations Begin #######################
    public function articles(){
        return $this -> hasMany('App\Models\Article','author_id');
    }
    
    public function comments(){
        return $this -> hasMany('App\Models\Comment','user_id');
    }

    public function replies(){
        return $this -> hasMany('App\Models\Reply','user_id');
    }

    public function fllows(){
        return $this -> hasMany('App\Models\Follow','user1_id');
    }

    public function fllows_by(){
        return $this -> hasMany('App\Models\Follow','user2_id');
    }

    public function sponsors(){
        return $this -> hasMany('App\Models\Sponsor','user_id');
    }

    public function views(){
        return $this -> hasMany('App\Models\View','user_id');
    }


    #######################  Relations End  #######################

}
