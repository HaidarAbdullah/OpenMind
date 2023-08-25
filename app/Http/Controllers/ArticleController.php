<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Reference;
use App\Models\Reply;
use App\Models\Tag;
use App\Models\User;
use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    

    public function searchTags(Request $request){
    
        $searchTerm = $request->input('searchTerm');
    
        $tags = DB::table('tags')->where('keyword', 'LIKE', '%'.$searchTerm.'%')->get();
    
        return response()->json($tags);
    }

    ############################ search Countries ###########################################
    public function searchCountries(Request $request)
{
    $searchTerm = $request->input('searchTerm');

    // An array of country names to search through
    $countries = [
        'Afghanistan',
        'Albania',
        'Algeria',
        'Andorra',
        'Angola',
        'Antigua and Barbuda',
        'Argentina',
        'Armenia',
        'Australia',
        'Austria',
        'Azerbaijan',
        'Bahamas',
        'Bahrain',
        'Bangladesh',
        'Barbados',
        'Belarus',
        'Belgium',
        'Belize',
        'Benin',
        'Bhutan',
        'Bolivia',
        'Bosnia and Herzegovina',
        'Botswana',
        'Brazil',
        'Brunei',
        'Bulgaria',
        'Burkina Faso',
        'Burundi',
        'Cabo Verde',
        'Cambodia',
        'Cameroon',
        'Canada',
        'Central African Republic (CAR)',
        'Chad',
        'Chile',
        'China',
        'Colombia',
        'Comoros',
        'Congo, Democratic Republic of the',
        'Congo, Republic of the',
        'Costa Rica',
        'Cote d\'Ivoire',
        'Croatia',
        'Cuba',
        'Cyprus',
        'Czechia',
        'Denmark',
        'Djibouti',
        'Dominica',
        'Dominican Republic',
        'Ecuador',
        'Egypt',
        'El Salvador',
        'Equatorial Guinea',
        'Eritrea',
        'Estonia',
        'Eswatini (formerly Swaziland)',
        'Ethiopia',
        'Fiji',
        'Finland',
        'France',
        'Gabon',
        'Gambia',
        'Georgia',
        'Germany',
        'Ghana',
        'Greece',
        'Grenada',
        'Guatemala',
        'Guinea',
        'Guinea-Bissau',
        'Guyana',
        'Haiti',
        'Honduras',
        'Hungary',
        'Iceland',
        'India',
        'Indonesia',
        'Iran',
        'Iraq',
        'Ireland',
        'Italy',
        'Jamaica',
        'Japan',
        'Jordan',
        'Kazakhstan',
        'Kenya',
        'Kiribati',
        'Kosovo',
        'Kuwait',
        'Kyrgyzstan',
        'Laos',
        'Latvia',
        'Lebanon',
        'Lesotho',
        'Liberia',
        'Libya',
        'Liechtenstein',
        'Lithuania',
        'Luxembourg',
        'Madagascar',
        'Malawi',
        'Malaysia',
        'Maldives',
        'Mali',
        'Malta',
        'Marshall Islands',
        'Mauritania',
        'Mauritius',
        'Mexico',
        'Micronesia',
        'Moldova',
        'Monaco',
        'Mongolia',
        'Montenegro',
        'Morocco',
        'Mozambique',
        'Myanmar (formerly Burma)',
        'Namibia',
        'Nauru',
        'Nepal',
        'Netherlands',
        'New Zealand',
        'Nicaragua',
        'Niger',
        'Nigeria',
        'North Korea',
        'North Macedonia (formerly Macedonia)',
        'Norway',
        'Oman',
        'Pakistan',
        'Palau',
        'Palestine',
        'Panama',
        'Papua New Guinea',
        'Paraguay',
        'Peru',
        'Philippines',
        'Poland',
        'Portugal',
        'Qatar',
        'Romania',
        'Russia',
        'Rwanda',
        'Saint Kitts and Nevis',
        'Saint Lucia',
        'Saint Vincent and the Grenadines',
        'Samoa',
        'San Marino',
        'Sao Tome and Principe',
        'Saudi Arabia',
        'Senegal',
        'Serbia',
        'Seychelles',
        'Sierra Leone',
        'Singapore',
        'Slovakia',
        'Slovenia',
        'Solomon Islands',
        'Somalia',
        'South Africa',
        'South Korea',
        'South Sudan',
        'Spain',
        'Sri Lanka',
        'Sudan',
        'Suriname',
        'Sweden',
        'Switzerland',
        'Syria',
        'Taiwan',
        'Tajikistan',
        'Tanzania',
        'Thailand',
        'Timor-Leste (formerly East Timor)',
        'Togo',
        'Tonga',
        'Trinidad and Tobago',
        'Tunisia',
        'Turkey',
        'Turkmenistan',
        'Tuvalu',
        'Uganda',
        'Ukraine',
        'United Arab Emirates (UAE)',
        'United Kingdom (UK)',
        'United States of America (USA)',
        'Uruguay',
        'Uzbekistan',
        'Vanuatu',
        'Vatican City (Holy See)',
        'Venezuela',
        'Vietnam',
        'Yemen',
        'Zambia',
        'Zimbabwe',
    ];

    // Filter the countries array based on the search term
    $filteredCountries = array_filter($countries, function ($country) use ($searchTerm) {
        return stripos($country, $searchTerm) !== false;
    });

    // Get only the values of the $countries array
$countries_values = array_values($filteredCountries);

// Create a new associative array with the keys as "name" and the values as the country names
$countries_json = array_map(function ($country) {
    return ['name' => $country];
}, $countries_values);


 // Return the filtered countries as a JSON response
 return response()->json( $countries_json);
    
}

    ############################ Article Comments Functions #################################

    public function addComment(Request $request, $article_id)
    {
        $user_id = Auth::id();
        $user = User::find($user_id);

        $article = Article::find($article_id); // Find the article by ID

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found',
                'data' => null,
                'token' => null
            ], 404)->header('Content-Type', 'application/json');
        }

        $content = $request->input('content'); // Get the content of the comment from the request

        if (!$content) {
            return response()->json([
                'success' => false,
                'message' => 'write the comment first',
                'data' => null,
                'token' => null
            ], 404)->header('Content-Type', 'application/json');
        }

        $comment = new Comment([
            'user_id' => $user->id,
            'article_id' => $article->id,
            'content' => $content
        ]); // Create a new comment object      

        
        $comment->save(); // Save the comment to the database
        
        if ($comment->user) {
            if (filter_var($comment->user->img_src, FILTER_VALIDATE_URL)) {
                $comment->user->img_src = $comment->user->img_src;
            } else {
                $comment->user->img_src = $comment->user->img_src
                    ? Storage::url('profile-pics/' . $comment->user->img_src)
                    : null;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'data' => $comment,
            'token' => null
        ], 200)->header('Content-Type', 'application/json');
    }


    public function showRepliesByCommentId($article_id,$comment_id)
    {
    $comment = Comment::find($comment_id);
    if (!$comment) {       
       return response()->json([
            'success' => false,
            'message' => 'comment not found',
            'error' => 'Comment not found.',
            'token' => null
        ], 404)->header('Content-Type', 'application/json');
    }

    $replies = Reply::with('user', 'comment')
    ->orderBy('created_at', 'asc')
    ->where('comment_id', $comment_id)->get();

    

    foreach ($replies as $reply) {

        if ($reply->user->img_src) { // Check if user has an img_src
            if (filter_var($reply->user->img_src, FILTER_VALIDATE_URL)) {
                $reply->user->img_src = $reply->user->img_src;
            } else {
                $reply->user->img_src = Storage::url('profile-pics/' . $reply->user->img_src);
            }
        } else {
            $reply->user->img_src = null; // Set img_src to null if it's empty
        }

            
           // Check if created_at is not null before calculating time_ago
        if ($reply->created_at) {
            $reply->time_ago = $reply->created_at->diffForHumans();
        } else {
            $reply->time_ago = null;
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'replies for the selected comment',
        'data' => $replies,
        'token' => null
    ], 200)->header('Content-Type', 'application/json');
    }


    public function updateComment(Request $request, $article_id, $commentId)
    {
        $comment = Comment::find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        if ($comment->article_id != $article_id) {
            return response()->json(['message' => 'Comment not found in this article'], 404);
        }

        $comment->content = $request->input('content');
        $comment->save();

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully',
            'data' => $comment,
            'token' => null
        ], 200)->header('Content-Type', 'application/json');
    }


    public function deleteComment($article_id, $commentId)
    {
        $comment = Comment::find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        if ($comment->article_id != $article_id) {
            return response()->json(['message' => 'Comment not found in this article'], 404);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }

    public function addReply(Request $request, $articleId, $commentId)
    {
        $this->validate($request, [
            'content' => 'required|string|max:1000',
        ]);
    
        $user = auth()->user();
    
        $comment = Comment::find($commentId);
    
        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found',
                'error' => 'Comment not found.',
                'token' => null
            ], 404)->header('Content-Type', 'application/json');
        }
    
        $reply = new Reply([
            'user_id' => $user->id,
            'comment_id' => $commentId,
            'content' => $request->input('content'),
        ]);
    
        $reply->save();

        return response()->json([
            'success' => true,
            'message' => 'reply added successfully',
            'data' => $reply,
            'token' => null
        ], 201)->header('Content-Type', 'application/json');
    }

    public function updateReply(Request $request, $articleId, $commentId, $replyId)
{
    $this->validate($request, [
        'content' => 'required|string|max:1000',
    ]);

    $user = auth()->user();

    $reply = Reply::where('id', $replyId)
        ->where('user_id', $user->id)
        ->where('comment_id', $commentId)
        ->first();

    if (!$reply) {
        return response()->json([
            'success' => false,
            'message' => 'Reply not found',
            'error' => 'Replu not found.',
            'token' => null
        ], 404)->header('Content-Type', 'application/json');
    }

    $reply->content = $request->input('content');
    $reply->save();

    return response()->json([
        'success' => true,
        'message' => 'reply updated successfully',
        'data' => $reply,
        'token' => null
    ], 200)->header('Content-Type', 'application/json');

}

public function deleteReply(Request $request, $articleId, $commentId, $replyId)
    {
        $user = $request->user();

        // Check if the user is authenticated
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Find the reply
        $reply = Reply::find($replyId);

        // Check if the reply exists
        if (!$reply) {
            return response()->json([
                'success' => false,
                'message' => 'Reply not found',
                'error' => 'Replu not found.',
                'token' => null
            ], 404)->header('Content-Type', 'application/json');
            
        }

        // Check if the authenticated user is authorized to delete the reply
        if ($reply->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        // Delete the reply
        $reply->delete();

        return response()->json([
            'success' => true,
            'message' => 'reply deleted successfully',
            'data' => null,
            'token' => null
        ], 200)->header('Content-Type', 'application/json');
    }


    ##################################### Article functions ###################################
    public function updateArticle(Request $request, $article_id)
    {
        $article = Article::find($article_id);
    
        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found',
            ], 404);
        }
    
        // Check if the authenticated user is the author of the article
        if ($article->author_id != Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this article',
            ], 403);
        }
    
        $validatedData = $request->validate([
            'title' => 'required',
            'abstract' => 'required',
            'content' => 'required',
            'img_src' => 'nullable|image|max:5048',
        ]);
    
        $article->title = $validatedData['title'];
        $article->abstract = $validatedData['abstract'];
        $article->content = $validatedData['content'];
    
        // Check the value of the is_public field based on the selected option
        if ($request->input('selectChoices') == 'Leads to Magazine') {
            $article->is_public = 0;
            $article->magazine_url = $request->input('magazine_url');
        } else {
            $article->is_public = 1;
            $article->magazine_url = null;
        }
    
        if ($request->hasFile('img_src')) {
            $image = $request->file('img_src');
            $filename = time() . '.' . $image->getClientOriginalExtension();
    
            $path = $image->storeAs('article-pics', $filename);
            $article->img_src = $filename;
        }
    
        $article->save();
    
        // Delete the existing references for the article
        $article->references()->delete();
    
        // Rebuild the references for the article based on the tags received in the request
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
    
            // Create a new reference for the article and tag
            $reference = new Reference([
                'article_id' => $article->id,
                'tag_id' => $tag_id,
            ]);
            $reference->save();
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Article updated successfully',
            'data' => $article,
        ], 200);
    }



public function deleteArticle($article_id)
{
    $article = Article::find($article_id);

    if (!$article) {
        return response()->json([
            'success' => false,
            'message' => 'Article not found',
        ], 404);
    }

    // Check if the authenticated user is the author of the article
    if ($article->author_id != Auth::id()) {
        return response()->json([
            'success' => false,
            'message' => 'You are not authorized to delete this article',
        ], 403);
    }

    $article->delete();

    return response()->json([
        'success' => true,
        'message' => 'Article deleted successfully',
    ], 200);
}

##################################### Views functions ###################################
public function setView(Request $request)
{
    $validatedData = $request->validate([
        'article_id' => 'required|exists:articles,id',
        'time' => 'required',
        'progress' => 'nullable|numeric|min:0|max:100'
    ]);

    $userId = $request->user_id;
    $view = null;

    if ($userId) {
        // If the user is authenticated, update their existing view record
        $view = View::where('user_id', $userId)
                    ->where('article_id', $validatedData['article_id'])
                    ->latest()
                    ->first();

      
        $shouldUpdate = false;

        if ($view) {
                $shouldUpdate = true; 
        }else{
            $view = new View([
                'article_id' => $validatedData['article_id'],
                'user_id' => $userId,
                'time' => $validatedData['time'],
                'progress' => $validatedData['progress'],
                'elapsed_time' => $request->elapsed_time,
            ]);
            $view->save();
        }

        if ($shouldUpdate) {
            $view->time = $validatedData['time'];
            $view->progress =$validatedData['progress'] >= $view->progress? $validatedData['progress']:$view->progress;
            $view->elapsed_time += $request->elapsed_time;
            $view->save();
        }
    }else{
        $view = new View([
            'article_id' => $validatedData['article_id'],
            'user_id' => null, // Set user_id to null for non-authenticated users
            'time' => $validatedData['time'],
            'progress' => $validatedData['progress'],
            'elapsed_time' => $request->elapsed_time,
        ]);
        $view->save();
    }

    if (!$view) {
        // If no view record was created or updated, retrieve the latest view record for the article
        $view = View::where('article_id', $validatedData['article_id'])->latest()->first();
    }

    return response()->json([
        'status' => 'success',
        'view' => $view
    ]);
}


public function viewStatistics($articleId)
{
    $article = Article::findOrFail($articleId);

    $totalViews = $article->views()->count();
    $uniqueUsers = $article->views()->groupBy('user_id')->count();
    $averageProgress = $article->views()->avg('progress');
    $totalElapsedTimeInSeconds = $article->views()->sum('elapsed_time');

    // Convert total elapsed time to a more meaningful format
    $totalElapsedTime = [
        'hours' => floor($totalElapsedTimeInSeconds / 3600),
        'minutes' => floor(($totalElapsedTimeInSeconds % 3600) / 60),
        'seconds' => $totalElapsedTimeInSeconds % 60,
    ];

    $statistics = [
        'total_views' => $totalViews,
        'unique_users' => $uniqueUsers,
        'average_progress' => $averageProgress,
        'total_elapsed_time' => $totalElapsedTime,
    ];

    return response()->json($statistics);
}

public function showStatisticsMonthly($articleId)
{
    $article = Article::findOrFail($articleId);
    
    $views = $article->views()
        ->selectRaw('COUNT(*) as count, MONTH(created_at) as month')
        ->groupBy('month')
        ->get();

    $statistics = [];
    foreach ($views as $view) {
        $monthName = date('F', mktime(0, 0, 0, $view->month, 1));
        $statistics[$monthName] = $view->count;
    }

    return response()->json($statistics);
}

public function showStatisticsYearly($articleId){
    $article = Article::findOrFail($articleId);

    $views = $article->views()
        ->selectRaw('COUNT(*) as count, YEAR(created_at) as year')
        ->groupBy('year')
        ->get();

    $statistics = [];
    foreach ($views as $view) {
        $statistics[$view->year] = $view->count;
    }

    return response()->json($statistics);
}

public function showStatisticsProgress($articleId)
{
    $article = Article::findOrFail($articleId);

    $progressCounts = $article->views()
        ->selectRaw('COUNT(*) as count, CASE 
            WHEN progress >= 75 THEN "75% and above"
            WHEN progress >= 50 THEN "50% to 74%"
            WHEN progress >= 25 THEN "25% to 49%"
            ELSE "Less than 25%"
            END as progress_range')
        ->groupBy('progress_range')
        ->get();

    $statistics = [];
    foreach ($progressCounts as $progressCount) {
        $statistics[$progressCount->progress_range] = $progressCount->count;
    }

    return response()->json($statistics);
}


    public function showStatisticsGender($id)
    {
        $article = Article::findOrFail($id);

        $totalViews = $article->views()->count();
        $maleViews = $article->views()->whereHas('user', function ($query) {
            $query->where('gender', 'male');
        })->count();
        $femaleViews = $article->views()->whereHas('user', function ($query) {
            $query->where('gender', 'female');
        })->count();

        $unknownViews = $article->views()->where(function ($query) {
            $query->whereNull('user_id')->orWhereDoesntHave('user')->orWhereHas('user', function ($query) {
                $query->whereNull('gender');
            });
        })->count();

    
        $statistics = [
            'male' => $totalViews > 0 ? round(($maleViews / $totalViews) * 100, 2) : 0,
            'female' =>$totalViews > 0 ? round(($femaleViews / $totalViews) * 100, 2) : 0,
            'unknown' => round(($unknownViews / $totalViews) * 100, 2)
        ];

        return response()->json($statistics);
    }



public function showStatisticsAge($id)
{
    $article = Article::findOrFail($id);

    $under18Count = $article->views()
        ->leftJoin('users', 'views.user_id', '=', 'users.id')
        ->where(function ($query) {
            $query->where(DB::raw("TIMESTAMPDIFF(YEAR, users.birth_date, CURDATE())"), '<', 18);
        })
        ->count();

    $over18Under25Count = $article->views()
        ->join('users', 'views.user_id', '=', 'users.id')
        ->whereNotNull('users.birth_date')
        ->whereBetween(DB::raw("TIMESTAMPDIFF(YEAR, users.birth_date, CURDATE())"), [18, 25])
        ->count();

    $over25Under35Count = $article->views()
        ->join('users', 'views.user_id', '=', 'users.id')
        ->whereNotNull('users.birth_date')
        ->whereBetween(DB::raw("TIMESTAMPDIFF(YEAR, users.birth_date, CURDATE())"), [26, 35])
        ->count();

    $over35Under45Count = $article->views()
        ->join('users', 'views.user_id', '=', 'users.id')
        ->whereNotNull('users.birth_date')
        ->whereBetween(DB::raw("TIMESTAMPDIFF(YEAR, users.birth_date, CURDATE())"), [36, 45])
        ->count();

    $over45Count = $article->views()
        ->join('users', 'views.user_id', '=', 'users.id')
        ->whereNotNull('users.birth_date')
        ->where(DB::raw("TIMESTAMPDIFF(YEAR, users.birth_date, CURDATE())"), '>', 45)
        ->count();

    $unknownCount = $article->views()
        ->leftJoin('users', 'views.user_id', '=', 'users.id')
        ->where(function ($query) {
            $query->whereNull('users.birth_date')
                ->orWhereNull('views.user_id');
        })
        ->count();

    $totalViews = $article->views()->count();

    $statistics = [
        'under_18' => [
            'count' => $under18Count,
            'percentage' => $totalViews > 0 ? round(($under18Count / $totalViews) * 100, 2) : 0,
        ],
        'over_18_under_25' => [
            'count' => $over18Under25Count,
            'percentage' => $totalViews > 0 ? round(($over18Under25Count / $totalViews) * 100, 2) : 0,
        ],
        'over_25_under_35' => [
            'count' => $over25Under35Count,
            'percentage' => $totalViews > 0 ? round(($over25Under35Count / $totalViews) * 100, 2) : 0,
        ],
        'over_35_under_45' => [
            'count' => $over35Under45Count,
            'percentage' => $totalViews > 0 ? round(($over35Under45Count / $totalViews) * 100, 2) : 0,
        ],
        'over_45' => [
            'count' => $over45Count,
            'percentage' => $totalViews > 0 ? round(($over45Count / $totalViews) * 100, 2) : 0,
        ],
        'unknown' => [
            'count' => $unknownCount,
            'percentage' => $totalViews > 0 ? round(($unknownCount / $totalViews) * 100, 2) : 0,
        ],
    ];

    return response()->json($statistics);
}



public function showStatisticsCountry($id)
{
    $article = Article::findOrFail($id);
    $views = $article->views()->with('user')->get();

    $totalViews = $views->count();
    $knownViews = $views->whereNotNull('user.country');
    $knownCount = $knownViews->count();

    $countries = $knownViews->groupBy('user.country')->map(function ($group) use ($totalViews) {
        $count = $group->count();
        $percentage = round(($count / $totalViews) * 100, 2);
        return [
            'count' => $count,
            'percentage' => $percentage,
        ];
    });

    $unknownCount = $totalViews - $knownCount;
    $unknownPercentage = round(($unknownCount / $totalViews) * 100, 2);

    $countries['unknown'] = [
        'count' => $unknownCount,
        'percentage' => $unknownPercentage,
    ];

    $userCount = $views->pluck('user_id')->unique()->count();

    $response = [
        'countries' => $countries,
        'userCount' => $userCount,
    ];

    return response()->json($response);
}

}
