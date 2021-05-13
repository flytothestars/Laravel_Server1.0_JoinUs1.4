<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Log;

class AuthController extends Controller
{

    public function login(Request $request){
        
        $creds = $request->only(['email', 'password']);
        // Log::info($request);
        if(!$token=auth()->attempt($creds)){
            
            return response()->json([
                'success' => false,
                'message' => 'invalid credintials'
            ]);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => Auth::user()
        ]);
    }

    public function register(Request $request){

        $encryptPass = Hash::make($request->password);
        $user = new User;

        try{
            //$user->name = $request->name;
            $user->email = $request->email;
            $user->password = $encryptPass;
            $user->save();
            return $this->login($request);
        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => ''.$e
            ]);
        }


    }

    public function logout(Request $request){
        try{
            JWTAuth::invalidate(JWTAuth::parseToken($request->token));
            return response()->json([
                'success' => true,
                'message' => 'logout success'
            ]);
        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => ''.$e
            ]);
        }

    }

    public function saveUserInfo(Request $request){
        $user = User::find(Auth::user()->id);
        $user->name = $request->name;
        $photo = '';

        if($request->photo != ''){
            $photo = time().'.jpg';
            file_put_contents('storage/profiles/'.$photo,base64_decode($request->photo));
            $user->photo = $photo;
        }

        $user->update();

        return response()->json([
            'success' => true,
            'photo' => $photo
        ]);
    }

    public function getUser(){
        return response()->json([
            'user' => Auth::user()
        ]);
    }
    public function getAllUser(){
        $users = User::orderBy('id','desc')->get();
        try{
            return response()->json([
                'success' => true,
                'user' => $users
            ]);
        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'error' => ''.$e
            ]);
        }
    }

}
