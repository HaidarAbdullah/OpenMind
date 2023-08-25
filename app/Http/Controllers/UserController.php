<?php

namespace App\Http\Controllers;

use App\Models\Aritcle;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Article;
class UserController extends Controller
{

    public function register(Request $request) {

        $user = User::create($request->all());
        $user->password = Hash::make($user->password);
        $user->save();

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'success'=>true,
            'message'=>'User successfully registered',
            'data' => $user,
            'token' => $token
        ], 200);
    }


    public function login(Request $request)
    {
        if (!Auth::attempt(['email' => $request['email'], 'password' => $request['password']])) {
            return response()->json([
                'success'=>false,
                'message'=>'credintials not match !',
                'data' => null,
                'token' => null
            ], 401);
        }
        $token =  auth()->user()->createToken('token')->plainTextToken;
        $user_id = Auth::id();
        $data = User::find($user_id);
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
 

     public function showTrends()
    {
      /*
      $trends = Article::select('id', 'author_id' ,'abstract' , 'content' ,'img_src', 'likes' , 'views')
            ->selectRaw('SUM(views + likes) as total')
            ->groupBy('id')
            ->orderBy('total', 'desc')
            ->get();
      */
      
      $trends = Article::with('author' , 'comments' , 'references')->select('id', 'author_id' ,'abstract' , 'content' ,'img_src', 'likes' , 'views')
      ->selectRaw('SUM(views + likes) as total')
      ->groupBy('id')
      ->orderBy('total', 'desc')
      ->get();

      return response()->json([
        'success'=>true,
        'message'=>'sucessfully get trends',
        'data' => $trends,
        'token' => null
    ], 200);

    }


    





}
