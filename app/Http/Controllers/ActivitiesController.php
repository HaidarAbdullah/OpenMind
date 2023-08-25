<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Like;
use App\Models\Reference;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Illuminate\Pagination\Paginator;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivitiesController extends Controller
{


    public function showTrendsById($id)
{
    $article = Article::with('author', 'comments.user','comments.replies', 'references.tag')
        ->select('id', 'author_id', 'title', 'abstract', 'content', 'img_src', 'likes', 'views','is_public','magazine_url', 'created_at')
        ->selectRaw('SUM(views + likes) as total')
        ->where('id', $id) // Filter by the specified ID
        ->groupBy('id')
        ->first(); // Retrieve the first matching result

    if (!$article) {
        return response()->json([
            'success' => false,
            'message' => 'Article not found',
        ], 404)->header('Content-Type', 'application/json');
    }

        // Get the number of comments for the article
       $commentCount = $article->comments()->count();
       
       
       $article->comments()->orderBy('created_at', 'asc')->get();

        // Convert the created_at timestamp to the time ago format
        if ($article->created_at) { // Check if created_at is not null or false
            $created_at = Carbon::parse($article->created_at);
            $now = Carbon::now();
            $diff = $created_at->diff($now);

            if ($diff->y > 0) {
                $time_ago = $diff->y . " year" . ($diff->y > 1 ? "s" : "") . " ago";
            } elseif ($diff->m > 0) {
                $time_ago = $diff->m . " month" . ($diff->m > 1 ? "s" : "") . " ago";
            } elseif ($diff->d > 0) {
                $time_ago = $diff->d . " day" . ($diff->d > 1 ? "s" : "") . " ago";
            } elseif ($diff->h > 0) {
                $time_ago = $diff->h . " hour" . ($diff->h > 1 ? "s" : "") . " ago";
            } elseif ($diff->i > 0) {
                $time_ago = $diff->i . " minute" . ($diff->i > 1 ? "s" : "") . " ago";
            } else {
                $time_ago = "Just now";
            }

            $article->time_ago = $time_ago;
        } else {
            $article->time_ago = null; // Set time_ago to null if created_at is null or false
        }

    

    $article->img_src = $article->img_src
        ? Storage::url('article-pics/' . $article->img_src)
        : null;

    if ($article->author) {
        if (filter_var($article->author->img_src, FILTER_VALIDATE_URL)) {
            $article->author->img_src = $article->author->img_src;
        } else {
            $article->author->img_src = $article->author->img_src
                ? Storage::url('profile-pics/' . $article->author->img_src)
                : null;
        }
    }

    foreach ($article->comments as $comment) {
        $comment->replies_count = $comment->replies()->count();
        if ($comment->user) { // Check if the comment has a user
            if ($comment->user->img_src) { // Check if user has an img_src
                if (filter_var($comment->user->img_src, FILTER_VALIDATE_URL)) {
                    $comment->user->img_src = $comment->user->img_src;
                } else {
                    $comment->user->img_src = Storage::url('profile-pics/' . $comment->user->img_src);
                }
            } else {
                $comment->user->img_src = null; // Set img_src to null if it's empty
            }
        }

           // Check if created_at is not null before calculating time_ago
    if ($comment->created_at) {
        $comment->time_ago = $comment->created_at->diffForHumans();
    } else {
        $comment->time_ago = null;
    }
    }

  

    // Add the comment count to the article object
    $article->comment_count = $commentCount;


        // Get the likes count for the article
        $likesCount = Like::where('article_id', $article->id)->count();

      
        // Addthe likes count and isLiked attributes to the article object
        $article->likes_count = $likesCount;
      
    return response()->json([
        'success' => true,
        'message' => 'Successfully get article',
        'data' => $article,
        'token' => null
    ], 200)->header('Content-Type', 'application/json');
}


   
    public function showTrends(Request $request)
    {
        $perPage = 7; // Set the number of items per page to 10

        $page = $request->input('page', 1); // Get the current page number from the request parameters
    

        $trends = Article::with('author', 'comments', 'references.tag')
            ->select('id', 'author_id', 'title', 'abstract', 'content', 'img_src', 'likes', 'views','is_public','magazine_url','created_at')
            ->selectRaw('SUM(views + likes) as total')
            ->groupBy('id')
            ->orderBy('total', 'desc')
            ->paginate($perPage, ['*'], 'page', $page); // Paginate the results with default values
   
        
        $data = $trends->map(function ($article) {

            // Get the number of comments for the article
             $commentCount = $article->comments()->count();

            // Convert the created_at timestamp to the time ago format
            if ($article->created_at) { // Check if created_at is not null or false
                $created_at = Carbon::parse($article->created_at);
                $now = Carbon::now();
                $diff = $created_at->diff($now);

                if ($diff->y > 0) {
                    $time_ago = $diff->y . " year" . ($diff->y > 1 ? "s" : "") . " ago";
                } elseif ($diff->m > 0) {
                    $time_ago = $diff->m . " month" . ($diff->m > 1 ? "s" : "") . " ago";
                } elseif ($diff->d > 0) {
                    $time_ago = $diff->d . " day" . ($diff->d > 1 ? "s" : "") . " ago";
                } elseif ($diff->h > 0) {
                    $time_ago = $diff->h . " hour" . ($diff->h > 1 ? "s" : "") . " ago";
                } elseif ($diff->i > 0) {
                    $time_ago = $diff->i . " minute" . ($diff->i > 1 ? "s" : "") . " ago";
                } else {
                    $time_ago = "Just now";
                }

                $article->time_ago = $time_ago;
            } else {
                $article->time_ago = null; // Set time_ago to null if created_at is null or false
            }

            // Check if created_at is not null before calculating article time fromat
            if ($article->created_at) {
                $article->art_time_format = $article->created_at->format('M jS \'y');
            } else {
                $article->art_time_format = null;
            }

            $article->img_src = $article->img_src
                ? Storage::url('article-pics/' . $article->img_src)
                : null;
        
            if ($article->author) {
                if (filter_var($article->author->img_src, FILTER_VALIDATE_URL)) {
                    $article->author->img_src = $article->author->img_src;
                } else {
                    $article->author->img_src = $article->author->img_src
                        ? Storage::url('profile-pics/' . $article->author->img_src)
                        : null;
                }
            }
        
            // Add the comment count to the article object
            $article->comment_count = $commentCount;
          
            return $article;
        });

        $pagination = [
            'current_page' => $trends->currentPage(),
            'last_page' => $trends->lastPage(),
            'prev_page_url' => $trends->previousPageUrl(),
            'next_page_url' => $trends->nextPageUrl(),
            'total' => $trends->total(),
            'per_page' => $trends->perPage(),
            'links' => $trends->links()->toHtml(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Successfully get trends',
            'data' => $data,
            'pagination' => $pagination,
            'token' => null
        ], 200)->header('Content-Type', 'application/json');
    }




    public function getMostUsedTags()
    {
        $tags = Tag::withCount('references')
            ->orderByDesc('references_count')
            ->limit(8)
            ->get();

        return response()->json($tags);
    }


public function getArticlesByTag(Request $request, Tag $tag)
{
    $perPage = 7; // Set the number of items per page to 7
    $page = $request->input('page', 1); // Get the current page number from the request parameters

    $articleIds = Reference::where('tag_id', $tag->id)->pluck('article_id');
    $articles = Article::with('author', 'comments', 'references.tag')
        ->whereIn('id', $articleIds)
        ->select('id', 'author_id', 'title', 'abstract', 'content', 'img_src', 'likes', 'views', 'is_public', 'magazine_url', 'created_at')
        ->selectRaw('SUM(views + likes) as total')
        ->groupBy('id')
        ->orderBy('total', 'desc')
        ->paginate($perPage, ['*'], 'page', $page); // Paginate the results with default values

    $data = $articles->map(function ($article) {
        // Get the number of comments for the article
        $commentCount = $article->comments()->count();

        $article->img_src = $article->img_src
            ? Storage::url('article-pics/' . $article->img_src)
            : null;

        if ($article->author) {
            if (filter_var($article->author->img_src, FILTER_VALIDATE_URL)) {
                $article->author->img_src = $article->author->img_src;
            } else {
                $article->author->img_src = $article->author->img_src
                    ? Storage::url('profile-pics/' . $article->author->img_src)
                    : null;
            }
        }

        // Add the comment count to the article object
        $article->comment_count = $commentCount;

        return $article;
    });

    $pagination = [
        'current_page' => $articles->currentPage(),
        'last_page' => $articles->lastPage(),
        'prev_page_url' => $articles->previousPageUrl(),
        'next_page_url' => $articles->nextPageUrl(),
        'total' => $articles->total(),
        'per_page' => $articles->perPage(),
        'links' => $articles->links()->toHtml(),
    ];

    return response()->json([
        'success' => true,
        'message' => 'Successfully get articles by tag',
        'data' => $data,
        'pagination' => $pagination,
        'token' => null
    ], 200)->header('Content-Type', 'application/json');
}
}
