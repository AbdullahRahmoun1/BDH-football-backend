<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ValidatedInput;
use Illuminate\Validation\ValidationException;

class userController extends Controller
{
    public function createGuest(Request $request){
        try{
            $body=$request->validate(User::validationRules());
        }catch(ValidationException $e){
            return response(['msg'=>$e->getMessage()]);
        }
        $user=User::create($body);
        $user->owner_type=config('consts.guest');
        $user->save();
        return [
            'message'=>'success',
        ];
    }
    public function createAdmin(Request $request){
        try{
            $body=$request->validate(User::validationRules());
        }catch(ValidationException $e){
            return response(['msg'=>$e->getMessage()]);
        }
        $user=User::create($body);
        $user->owner_type=config('consts.admin');
        $user->save();
        return [
            'message'=>'success',
        ];
    }
    public function login(Request $request)
    {
        try{
            $body=$request->validate([
                'userName'=>['required','exists:users,userName'],
                'password'=>['required']
            ]);
        }catch(ValidationException $e){
            return response(['meesage'=>$e->getMessage()]);
        }
        $user=User::where('userName',$body['userName'])->first();
        if(!Hash::check($body['password'],$user->password))
        abort(401,'Wrong password');
        $token=$user->createToken($request->ip(),[$user->owner_type]);
        return response([
            'message'=>'success',
            'token'=>$token->plainTextToken,
            'tokenType'=>$user->owner_type
        ]);
    }
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return ['message'=>'success'];
    }
    public function self(Request $request){
        return $request->user();
    }
}
