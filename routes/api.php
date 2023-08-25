<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivitiesController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\SearchEngineController;

use Stichoza\GoogleTranslate\GoogleTranslate;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/show-trends', [ActivitiesController::class, 'showTrends']);
Route::get('/show-trends/{id}', [ActivitiesController::class, 'showTrendsById']);

Route::get('/search-tags', [ArticleController::class, 'searchTags']);
Route::get('/search-countries', [ArticleController::class, 'searchCountries']);

Route::get('/articles/{article_id}/comments/{commentId}/showReplies', [ArticleController::class, 'showRepliesByCommentId']);

Route::post('/search-articles', [SearchEngineController::class, 'search']);

Route::get('/translate', function(){
    $lang = new GoogleTranslate("en");
    return $lang->setSource('en')->setTarget('ar')->translate("hello world");
});


Route::get('/tags/most-used',[ActivitiesController::class, 'getMostUsedTags'] );
Route::get('/tags/{tag}',[ActivitiesController::class, 'getArticlesByTag'] );


Route::post('/views', [ArticleController::class, 'setView']);
Route::get('articles/{id}/statistics', [ArticleController::class, 'viewStatistics']);

//auth users
Route::middleware('auth:sanctum')->group(function() {
        Route::post('/logout', [UserController::class, 'logout']);
        Route::get('/users/{id}/info', [UserController::class, 'showInfo']);
        Route::post('/users/{id}/edit-info', [UserController::class, 'editInfo']);
        Route::post('/users/{id}/reset-pass', [UserController::class, 'resetPassword']);
        Route::get('/users/{id}/reads-statistics', [UserController::class, 'readsStatistics']);
        Route::get('/users/{id}/articles', [UserController::class, 'getUserArticles']);
        Route::get('/users/{id}/public-info', [UserController::class, 'showPublicInfo']);


        //article statistics
        Route::get('articles/{id}/views/months', [ArticleController::class, 'showStatisticsMonthly']);
        Route::get('articles/{id}/views/years', [ArticleController::class, 'showStatisticsYearly']);
        Route::get('articles/{id}/views/progresses', [ArticleController::class, 'showStatisticsProgress']);
        Route::get('articles/{id}/views/genders', [ArticleController::class, 'showStatisticsGender']);
        Route::get('articles/{id}/views/ages', [ArticleController::class, 'showStatisticsAge']);
        Route::get('articles/{id}/views/countries', [ArticleController::class, 'showStatisticsCountry']);

        Route::post('/users/{id}/following', [UserController::class, 'followUser']);
        Route::post('/users/{id}/unfollowing', [UserController::class, 'unfollowUser']);
        
        Route::get('articles/{id}/check-liked', [UserController::class, 'checkLiked']);
        Route::post('articles/{id}/like', [UserController::class, 'likeArticle']);
        Route::post('articles/{id}/remove-like', [UserController::class, 'RemoveLikeArticle']);

        //article routes
        Route::post('/write-article',[UserController::class,'writeArticle']);
        Route::post('/articles/{article_id}/update', [ArticleController::class, 'updateArticle']);
        Route::post('/articles/{article_id}/delete', [ArticleController::class, 'deleteArticle']);

        //comment routes
        Route::post('/articles/{article_id}/comments', [ArticleController::class, 'addComment']);
        Route::post('/articles/{article_id}/comments/{commentId}/update', [ArticleController::class, 'updateComment']);
        Route::post('/articles/{article_id}/comments/{commentId}/delete', [ArticleController::class, 'deleteComment']);

        //reply routes
        Route::post('/articles/{articleId}/comments/{commentId}/replies', [ArticleController::class, 'addReply']);
        Route::post('/articles/{articleId}/comments/{commentId}/replies/{replyId}/update', [ArticleController::class, 'updateReply']);
        Route::post('/articles/{articleId}/comments/{commentId}/replies/{replyId}/delete', [ArticleController::class, 'deleteReply']);


});