<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use GuzzleHttp\Psr7\Request;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    const userNameMinLength=5,userNameMaxLength=30;
    const passwordMinLength=5,passwordMaxLength=20;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'userName',
        'password',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'created_at',
        'updated_at',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        
    ];
    protected function password() : Attribute {
        return new Attribute(
            set:fn($value)=>bcrypt($value)
        );
    }
    public static function validationRules(){
        $nameLngthValid='between:'.self::userNameMinLength.','.self::userNameMaxLength;
        $passLngthValid='between:'.self::passwordMinLength.','.self::passwordMaxLength;
        return ([
            'userName'=>['required','unique:users,userName',$nameLngthValid],
            'password'=>['required',$passLngthValid]
        ]);
        }
}
