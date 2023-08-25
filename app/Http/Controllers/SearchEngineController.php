<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Article;
use Laravel\Scout\Builder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Stichoza\GoogleTranslate\GoogleTranslate;
use MeiliSearch\Client;

class SearchEngineController extends Controller
{

    /*
    public function search(Request $request)
{
    $validator = Validator::make($request->all(), [
        'query' => 'required|string'
    ]);
    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    // Translate the user's search query to English
    $translateToEnglish = new GoogleTranslate();
    $queryInEnglish = $translateToEnglish->setSource('auto')->setTarget('en')->translate($request->input('query'));

    // Search for articles in the user's language
    $query = $request->input('query');
    $articlesInUserLanguage = Article::where('title', 'LIKE', "%$query%")->get();

    // Translate the articles in the user's language to English
    $translateToEnglish = new GoogleTranslate();
    $articlesInEnglish = [];
    foreach ($articlesInUserLanguage as $article) {
        $translatedContent = $translateToEnglish->setSource('auto')->setTarget('en')->translate($article->title);
        $article->title = $translatedContent;
        $articlesInEnglish[] = $article;
    }

    // Search for articles in English
    $articlesInEnglish = array_merge($articlesInEnglish, Article::search($queryInEnglish)->get()->toArray());

    // Translate the content of the articles in English back to user's language
    $translateToUserLanguage = new GoogleTranslate();
    $translatedArticles = [];
    foreach ($articlesInEnglish as $article) {
        $translatedContent = $translateToUserLanguage->setSource('en')->setTarget('auto')->translate($article['title']);
        $article['title'] = $translatedContent;
        $translatedArticles[] = $article;
    }

    return response()->json(['data' => $translatedArticles], 200);
}
*/
/*public function search(Request $request)
{
    $validator = Validator::make($request->all(), [
        'query' => 'required|string'
    ]);
    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    // Translate the user's search query to English
    $translateToEnglish = new GoogleTranslate();
    $queryInEnglish = $translateToEnglish->setSource('auto')->setTarget('en')->translate($request->input('query'));

    // Search for articles in the user's language
    $query = $request->input('query');
    $articlesInUserLanguage = Article::where('title', 'LIKE', "%$query%")->with('author')->get();

    // Translate the articles in the user's language to English
    $translateToEnglish = new GoogleTranslate();
    $articlesInEnglish = [];
    foreach ($articlesInUserLanguage as $article) {
        $translatedContent = $translateToEnglish->setSource('auto')->setTarget('en')->translate($article->title);
        $article->title = $translatedContent;
        $article->img_src = $article->img_src ? Storage::url('article-pics/' . $article->img_src) : null;
        if ($article->author) {
            if (filter_var($article->author->img_src, FILTER_VALIDATE_URL)) {
                $article->author->img_src = $article->author->img_src;
            } else {
                $article->author->img_src = $article->author->img_src ? Storage::url('profile-pics/' . $article->author->img_src) : null;
            }
        }
        $articlesInEnglish[] = $article;
    }

    // Search for articles in English
    $articlesInEnglish = Article::search($queryInEnglish)->paginate(6);
    $articlesInEnglish->load('author');

    // Translate the content of the articles in English back to user's language
    $translateToUserLanguage = new GoogleTranslate();
    $translatedArticles = [];
    foreach ($articlesInEnglish as $article) {
        $translatedContent = $translateToUserLanguage->setSource('en')->setTarget('auto')->translate($article->title);
        $article->title = $translatedContent;
        $article->img_src = $article->img_src ? Storage::url('article-pics/' . $article->img_src) : null;
        if ($article->author) {
            if (filter_var($article->author->img_src, FILTER_VALIDATE_URL)) {
                $article->author->img_src = $article->author->img_src;
            } else {
                $article->author->img_src = $article->author->img_src ? Storage::url('profile-pics/' . $article->author->img_src) : null;
            }
        }
        $translatedArticles[] = $article;
    }

    return response()->json([
        'data' => $translatedArticles,
        'meta' => [
            'current_page' => $articlesInEnglish->currentPage(),
            'last_page' => $articlesInEnglish->lastPage(),
            'per_page' => $articlesInEnglish->perPage(),
            'total' => $articlesInEnglish->total(),
        ],
    ], 200);
}*/

/******************using scout for searching */
public function search(Request $request)
{
    $validator = Validator::make($request->all(), [
        'query' => 'required|string'
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    // Get the user's search query
    $query = $request->input('query');

    // Get the user's language
    $userLanguage = $request->header('Accept-Language');
    if (!$userLanguage) {
        // If the user's language is not set, default to English
        $userLanguage = 'en';
    }

    if($userLanguage=='en'){
        $foriengLanguage='ar';
    }else{
        $foriengLanguage='en';
    }

    // Search for articles in Meilisearch
    $client = new Client('http://localhost:7700', 'masterKey');
    $index = $client->getIndex('articles');
    $searchResults = $index->search($query);

    // Filter the search results based on the user's language
    $articlesInUserLanguage = [];
    foreach ($searchResults->getHits() as $hit) {
        $article = Article::find($hit['id']);
       // if ($article->language === $userLanguage) {
            $articlesInUserLanguage[] = $article;
       // }
    }


    $articlesInForiegnLanguage=[];
    $translateToForiegnLang = new GoogleTranslate();
    $newSearchResults=$index->search( $translateToForiegnLang->setSource($userLanguage)->setTarget($foriengLanguage)->translate($query));
    foreach ($newSearchResults->getHits() as $hit) {
        $article = Article::find($hit['id']);
        $articlesInForiegnLanguage[] = $article; 
    }


    $perPage = 6;
    $page = $request->query('page') ?: 1; // get the current page number from the query string
    
    $allArticles = array_merge($articlesInUserLanguage, $articlesInForiegnLanguage);
    
   // $totalArticles = count($allArticles);
    
    $allArticlesPaginated = collect($allArticles)->forPage($page, $perPage);
    

    foreach ($allArticlesPaginated->values() as $article) {
        if ($article) {
            $article->img_src = $article->img_src ? Storage::url('article-pics/' . $article->img_src) : null;
            if ($article->author) {
                if (filter_var($article->author->img_src, FILTER_VALIDATE_URL)) {
                    $article->author->img_src = $article->author->img_src;
                } else {
                    $article->author->img_src = $article->author->img_src ? Storage::url('profile-pics/' . $article->author->img_src) : null;
                }
            }
            $article->references=$article->references()->with('tag')->get();
            
            // Check if created_at is not null before calculating article time fromat
            if ($article->created_at) {
                $article->art_time_format = $article->created_at->format('M jS \'y');
            } else {
                $article->art_time_format = null;
            }
            
        }
    }

    $filteredArticles = $allArticlesPaginated->filter(function ($article) {
        return $article !== null;
    });
    $totalArticles = count($filteredArticles);


    return response()->json([
        'data' => $filteredArticles->values(),
        'meta' => [
            'current_page' => $page,
            'last_page' => ceil($totalArticles / $perPage),
            'per_page' => $perPage,
            'total' => $totalArticles,
        ],
    ], 200);
}


/*
public function search(Request $request)
{
    $validator = Validator::make($request->all(), [
        'query' => 'required|string'
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    // Get the user's search query
    $query = $request->input('query');

    // Get the user's language
    $userLanguage = $request->header('Accept-Language') ?? 'en';

    // Configure the index
    $client = new Client('http://localhost:7700', 'masterKey');
    $index = $client->getIndex('articles');

    // Set up the search filters
    $filters = [
        'title.' . $userLanguage . ':' . $query,
        'content.' . $userLanguage . ':' . $query
    ];

    // Search for articles
    $searchResults = $index->search($query, [
        'filter' => implode(' OR ', $filters),
        'attributesToRetrieve' => ['id', 'title.' . $userLanguage, 'content.' . $userLanguage, 'language', 'img_src', 'author_id'],
        'attributesToHighlight' => ['title.' . $userLanguage, 'content.' . $userLanguage],
        'highlightPreTag' => '<strong>',
        'highlightPostTag' => '</strong>',
        'filter' => "title.$userLanguage=\"*$query*\" OR content.$userLanguage=\"*$query*\"", // Only return articles in the user's language
        'sort' => ['_score:desc', 'published_date:desc'] // Sort by relevance score and publication date
    ]);

    // Get the articles based on the search results
    $articles = [];
    foreach ($searchResults['hits'] as $hit) {
        $article = Article::find($hit['id']);
        if ($article) {
            // Translate the article's title to the user's language
            if ($article->language !== $userLanguage) {
                $translateToUserLanguage = new GoogleTranslate();
                $article->title = $translateToUserLanguage->setSource($article->language)->setTarget($userLanguage)->translate($article->title);
            }

            // Get the article's image URL
            $article->img_src = $article->img_src ? Storage::url('article-pics/' . $article->img_src) : null;

            // Get the author's image URL
            if ($article->author) {
                if (filter_var($article->author->img_src, FILTER_VALIDATE_URL)) {
                    $article->author->img_src = $article->author->img_src;
                } else {
                    $article->author->img_src = $article->author->img_src ? Storage::url('profile-pics/' . $article->author->img_src) : null;
                }
            }

            // Highlight the search terms in the article's title and content
            $article->title = $hit['_highlightResult']['title.' . $userLanguage]['value'] ?? $article->title;
            $article->content = $hit['_highlightResult']['content.' . $userLanguage]['value'] ?? $article->content;

            $articles[] = $article;
        }
    }

    return response()->json([
        'data' => $articles,
        'meta' => [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => count($articles),
            'total' => count($articles),
        ],
    ], 200);
}*/




}
