<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
class Article extends Model
{
    use HasFactory;
    use Searchable;
    protected $fillable = [
        'id',
        'author_id',
        'title',
        'abstract',
        'content',
        'img_src',
          'likes',
          'views',
          'is_public',
          'magazine_url',       
    ];

    

    public function toSearchableArray()
    {
        $array = $this->toArray();

        // Strip HTML tags from the content field
        $array['content'] = strip_tags($array['content']);

        return $array;
    }

    protected static function boot()
    {
        parent::boot();
    
        // Delete the image file, comments, views, references, and likes when the article is deleted
        static::deleting(function ($article) {
            if ($article->img_src) {
                Storage::delete('article-pics/' . $article->img_src);
            }
    
            $article->comments()->delete();
            $article->views()->delete();
            $article->references()->delete();
            $article->likes()->delete();
        });
    }

    ####################### Relations Begin #######################
    public function author(){
        return $this -> belongsTo('App\Models\User','author_id');
    }
    
    public function comments(){
        return $this -> hasMany('App\Models\Comment','article_id');
    }

    public function references(){
        return $this -> hasMany('App\Models\Reference','article_id');
    }

    public function views(){
        return $this -> hasMany('App\Models\View','article_id');
    }
    public function likes(){
        return $this -> hasMany('App\Models\Like','article_id');
    }

    #######################  Relations End  #######################



}
//1689669916