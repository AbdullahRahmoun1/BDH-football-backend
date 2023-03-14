<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ValidatedInput;
use Illuminate\Validation\ValidationException;

class userController extends Controller
{
    public function signup(Request $request){
        try{
            $body=$request->validate(User::validationRules());
        }catch(ValidationException $e){
            return response(['msg'=>$e->getMessage()]);
        }
        $user=User::create($body);
        $token=$user->createToken('guestToken',['guest']);
        return [
            'message'=>'success',
            'token'=>$token->plainTextToken
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
        return response([
            'message'=>'wrong password'
        ],401);
        //TODO::determin if he is an admin or not
        $tokenType=$user->player==null?'guest':'player';
        $token=$user->createToken('lsjfs',[$tokenType]);
        return response([
            'message'=>'success',
            'token'=>$token->plainTextToken,
            'tokenType'=>$tokenType
        ]);
    }
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return ['message'=>'success'];
    }
}
