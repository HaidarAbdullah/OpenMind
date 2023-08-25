<?php

namespace App\Http\Controllers;

use App\Models\Aritcle;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Reference;
use App\Models\Tag;
use App\Models\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    public function register(Request $request)
    {
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'User with this email already exists'
            ], 400);
        }
    
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'img_src' => 'nullable|image|max:5048',
            'contact_email' => 'nullable|email',
            'country' => 'nullable',
            'gender' => 'nullable',
            'birth_date' => 'nullable|date',
        ]);
    
        $user = new User();
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->password = Hash::make($validatedData['password']);
        $user->contact_email = $validatedData['contact_email'];
        $user->country = $validatedData['country'];
        $user->gender = $validatedData['gender'];
        $user->birth_date = $validatedData['birth_date'];
    
        if ($request->hasFile('img_src')) {
            $image = $request->file('img_src');
            $filename = time() . '.' . $image->getClientOriginalExtension();
    
            $path = $image->storeAs('profile-pics', $filename);
            $user->img_src = $filename;
        }
    
        $user->save();
    
        $token = $user->createToken('token')->plainTextToken;
    
        $data = collect([$user])->map(function ($userdata) {
            $userdata->img_src = $userdata->img_src
                ? Storage::url('profile-pics/' . $userdata->img_src)
                : null;
            return $userdata;
        })->first(); // Get the first (and only) object from the collection;
    
        return response()->json([
            'success' => true,
            'message' => 'User successfully registered',
            'data' => $data,
            'token' => $token
        ], 200);
    }


    public function login(Request $request)
    {
        if (!Auth::attempt(['email' => $request['email'], 'password' => $request['password']])) {
            return response()->json([
                'success'=>false,
                'message'=>'credintials not match !'
            ], 401);
        }
        $token =  auth()->user()->createToken('token')->plainTextToken;
        $user_id = Auth::id();
        $user = User::find($user_id);
        
        $data = collect([$user])->map(function ($userdata) {
            $userdata->img_src = $userdata->img_src
                ? Storage::url('profile-pics/' . $userdata->img_src)
                : null;
            return $userdata;
        })->first(); // Get the first (and only) object from the collection;

        return response()->json([
            'success'=>true,
            'message'=>'logged in successfully',
            'data' => $data,
            'token' => $token
        ], 200);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            'success'=>true,
            'message'=>'logged out successfully',
            'data' => null,
            'token' => null
        ], 200);
    }
 


   public function writeArticle(Request $request){
        $author_id =Auth::id();
        $validatedData = $request->validate([
            'title' => 'required',
            'abstract' => 'required',
            'content' => 'required',
            'img_src' => 'nullable|image|max:5048',
        ]);
    
        $article = new Article();
        $article->author_id =$author_id;
        $article->title = $validatedData['title'];
        $article->abstract = $validatedData['abstract'];
        $article->content = $validatedData['content'];
    

        // Check the value of the is_public field based on the selected option
        if ($request->input('selectChoices') == 'Leads to Magazine') {
            $article->is_public = 0;
            $article->magazine_url = $request->input('magazine_url');
        } else {
            $article->is_public = 1;
        }



        if ($request->hasFile('img_src')) {
            $image = $request->file('img_src');
            $filename = time() . '.' . $image->getClientOriginalExtension();

            $path = $image->storeAs('article-pics', $filename);
            $article->img_src = $filename;
            //Storage::url($path)
        }
    
        $article->save();

        //for adding tags to article 
        $tags = json_decode($request->tags, true);
        foreach ($tags as $tag) {
            // Check if the tag already exists in the tags table
            $existingTag = Tag::where('keyword', $tag['keyword'])->first();
            if (!$existingTag) {
                // If the tag doesn't exist, create a new tag
                $newTag = Tag::create(['keyword' => $tag['keyword']]);
                $tag_id = $newTag->id;
            } else {
                $tag_id = $existingTag->id;
            }

            // Check if the reference already exists in the references table
            $existingReference = Reference::where('article_id', $article->id)
                ->where('tag_id', $tag_id)
                ->first();
            if (!$existingReference) {
                // If the reference doesn't exist, create a new reference
                $reference = new Reference([
                    'article_id' => $article->id,
                    'tag_id' => $tag_id,
                ]);
                $reference->save();
            }
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Article created successfully',
            'data' => $article
        ], 201);
  
    }

public function showInfo($id)
{
    $authenticatedUser = Auth::user();

    // Check if the authenticated user exists and matches the requested user ID
    if (!$authenticatedUser || $authenticatedUser->id != $id) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user = User::findOrFail($id);

    // Retrieve the socials record for the user from the socials table
    $socials = DB::table('socials')->where('user_id', $user->id)->first();

    // Attach the socials record to the user object
    $user->socials = $socials;

    // Modify the image source to the real URL if it exists
    if ($user->img_src) {
        $user->img_src = Storage::url('profile-pics/' . $user->img_src);
    }

    // Return the user information as a JSON response
    return response()->json($user);
}


public function editInfo(Request $request, $id)
{
    // Retrieve the user from the database
    $user = User::findOrFail($id);

    // Check if the authenticated user owns the account
    if ($request->user()->id !== $user->id) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Update the user's information
    $user->update($request->except(['password', 'email', 'is_premium']));

    // Store the social media information in the socials table
    $socialMedia = $request->only(['twitter', 'facebook', 'instagram', 'linkedin']);

    DB::table('socials')->updateOrInsert(
        ['user_id' => $user->id],
        $socialMedia
    );

    // Store the new profile picture if provided
    if ($request->hasFile('img_src')) {
        $image = $request->file('img_src');
        $filename = time() . '.' . $image->getClientOriginalExtension();

        $path = $image->storeAs('profile-pics', $filename);
        $user->img_src = $filename;
        $user->save();
    }

    // Retrieve the updated user data with the real image URL
    $data = collect([$user])->map(function ($userData) {
        $userData->img_src = $userData->img_src ? Storage::url('profile-pics/' . $userData->img_src) : null;
        return $userData;
    })->first();

    // Return the updated user data as a JSON response
    return response()->json(['data' => $data]);
}

public function resetPassword(Request $request, $id)
{
    // Retrieve the user from the database
    $user = User::findOrFail($id);

    // Check if the authenticated user owns the account
    if ($request->user()->id !== $user->id) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Validate the old and new passwords
    $validatedData = $request->validate([
        'old_password' => 'required',
        'new_password' => 'required|min:8',
        'verify_password' => 'required|same:new_password',
    ]);

    // Verify the old password
    if (!Hash::check($validatedData['old_password'], $user->password)) {
        return response()->json(['message' => 'Old password is incorrect'], 400);
    }

    // Update the user's password
    $user->password = Hash::make($validatedData['new_password']);
    $user->save();

    return response()->json(['message' => 'Password reset successful']);
}


public function readsStatistics($id)
{
    $user = User::findOrFail($id);

    $sevenDaysAgo = Carbon::now()->subDays(7);

    $views = View::where('user_id', $user->id)
        ->where('created_at', '>=', $sevenDaysAgo)
        ->orderBy('created_at')
        ->get();

    $totalViews = $views->count();

    $scale25Count = 0;
    $scale50Count = 0;
    $scale75Count = 0;

    $response = [];

    foreach ($views as $view) {
        $dayName = $view->created_at->format('l');

        if ($view->progress > $user->progress + 25 && $view->progress < $user->progress + 50) {
            $scale25Count++;
            $response['25_percent'][] = [
                'count' => $scale25Count,
                'day' => $dayName,
            ];
        }

        if ($view->progress > $user->progress + 50 && $view->progress < $user->progress + 75) {
            $scale50Count++;
            $response['50_percent'][] = [
                'count' => $scale50Count,
                'day' => $dayName,
            ];
        }

        if ($view->progress > $user->progress + 75) {
            $scale75Count++;
            $response['75_percent'][] = [
                'count' => $scale75Count,
                'day' => $dayName,
            ];
        }
    }

    return response()->json($response);
}


public function getUserArticles($id)
{
    $perPage = 3; // Set the number of items per page to 10

    $page = request()->input('page', 1); // Get the current page number from the request parameters

    $articles = Article::where('author_id', $id)
        ->with('author', 'comments', 'references.tag')
        ->select('id', 'author_id', 'title', 'abstract', 'content', 'img_src', 'likes', 'views', 'is_public', 'magazine_url', 'created_at')
        ->orderBy('created_at', 'asc')
        ->paginate($perPage, ['*'], 'page', $page); // Paginate the results with default values

    $data = $articles->map(function ($article) {
        // Get the number of comments for the article
        $commentCount = $article->comments()->count();

        // Convert the created_at timestamp to the time ago format
        if ($article->created_at) {
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
            $article->time_ago = null;
        }

        if ($article->created_at) {
            $article->art_time_format = $article->created_at->format('M jS \'y');
        } else {
            $article->art_time_format = null;
        }

        $article->img_src = $article->img_src ? Storage::url('article-pics/' . $article->img_src) : null;

        if ($article->author) {
            if (filter_var($article->author->img_src, FILTER_VALIDATE_URL)) {
                $article->author->img_src = $article->author->img_src;
            } else {
                $article->author->img_src = $article->author->img_src
                    ? Storage::url('profile-pics/' . $article->author->img_src)
                    : null;
            }
        }

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
        'message' => 'Successfully get user articles',
        'data' => $data,
        'pagination' => $pagination,
        'token' => null
    ], 200)->header('Content-Type', 'application/json');
}


public function showPublicInfo($id)
{
    $user = User::withCount('fllows_by as followers_count', 'articles')->findOrFail($id);

    // Modify the image source to the real URL if it exists
    if ($user->img_src) {
        $user->img_src = Storage::url('profile-pics/' . $user->img_src);
    }

    // Check if the authenticated user is following the specified user
    $authUser = Auth::user();
    $isFollowing = false;

    if ($authUser) {
        $follow = Follow::where('user1_id', $authUser->id)->where('user2_id', $user->id)->first();
        $isFollowing = $follow ? true : false;
    }

    $data = [
        'name' => $user->name,
        'contact_email' => $user->contact_email,
        'job_title' => $user->job_title,
        'country' => $user->country,
        'followers_count' => $user->followers_count,
        'article_count' => $user->articles_count,
        'img_src' => $user->img_src,
        'is_following' => $isFollowing,
    ];

    return response()->json([
        'success' => true,
        'message' => 'Successfully get user public info',
        'data' => $data,
    ], 200);
}

public function followUser(Request $request, $id)
{
    // Get the authenticated user
    $user = Auth::user();

    // Find the user to follow
    $userToFollow = User::findOrFail($id);

    // Create a new follow record
    $follow = new Follow();
    $follow->user1_id = $user->id;
    $follow->user2_id = $userToFollow->id;
    $follow->save();

    // Get the user information of the followed user
    $followedUser = $follow->fllowed_by_user;

    // Return the user information as a JSON response
    return response()->json($followedUser);
}

public function unfollowUser($id)
{
    // Get the authenticated user
    $user = Auth::user();

    // Find the user being unfollowed
    $userToUnfollow = User::findOrFail($id);

    // Find the follow record
    $follow = Follow::where('user1_id', $user->id)
        ->where('user2_id', $userToUnfollow->id)
        ->first();

    if ($follow) {
        // Delete the follow record
        $follow->delete();

        // Return a success message
        return response()->json([
            'success' => true,
            'message' => 'Successfully unfollowed the user.',
        ]);
    }

    // Return an error message if the follow record doesn't exist
    return response()->json([
        'success' => false,
        'message' => 'You are not following this user.',
    ], 400);
}

public function likeArticle(Request $request, $id)
{
    $user = Auth::user(); // Get the authenticated user

    if (!$user) {
        // User is not authenticated, return a response indicating that
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }

    $article = Article::findOrFail($id);

    // Check if the user has already liked the article
    $existingLike = Like::where('user_id', $user->id)
        ->where('article_id', $article->id)
        ->first();

    if ($existingLike) {
        // User has already liked the article, return a response indicating that
        return response()->json(['message' => 'You have already liked this article.'], 400);
    }

    // Create a new like for the article
    $like = new Like();
    $like->user_id = $user->id;
    $like->article_id = $article->id;
    $like->save();

    // Increment the article's likes count
    $article->increment('likes');

    // Return a success response
    return response()->json(['message' => 'Article liked successfully.']);
}

public function removeLikeArticle(Request $request, $id)
{
    $user = Auth::user(); // Get the authenticated user

    if (!$user) {
        // User is not authenticated, return a response indicating that
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }

    $article = Article::findOrFail($id);

    // Check if the user has liked the article
    $like = Like::where('user_id', $user->id)
        ->where('article_id', $article->id)
        ->first();

    if (!$like) {
        // User hasn't liked the article, return a response indicating that
        return response()->json(['message' => 'You have not liked this article.'], 400);
    }

    // Delete the like record
    $like->delete();

    // Decrement the article's likes count
    $article->decrement('likes');

    // Return a success response
    return response()->json(['message' => 'Like removed successfully.']);
}

public function checkLiked($id)
{
    // Get the authenticated user
    $user = Auth::user();

    // Find the article by ID
    $article = Article::find($id);

    if (!$user || !$article) {
        // User or article not found
        return response()->json(['error' => 'User or article not found'], 404);
    }

    // Check if the user has liked the article
    $liked = Like::where('user_id', $user->id)
        ->where('article_id', $article->id)
        ->exists();

    return response()->json(['liked' => $liked]);
}


}
