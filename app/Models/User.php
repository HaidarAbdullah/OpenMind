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
        'follows_id'          //foriegn key
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
    public function article(){
        return $this -> hasMany('App\Models\Article','User_id');
    }
    
    public function comment(){
        return $this -> hasMany('App\Models\Comment','user_id');
    }

    public function reply(){
        return $this -> hasMany('App\Models\Reply','user_id');
    }

    public function fllow(){
        return $this -> hasMany('App\Models\User','follows_id');
    }

    public function fllowed_by(){
        return $this -> belongsTo('App\Models\User','follows_id');
    }
    #######################  Relations End  #######################
}
