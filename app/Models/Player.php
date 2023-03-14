<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Player extends Model
{
    use HasFactory;
    protected $hidden=['created_at','updated_at']; 
    protected $fillable=[
        'name',
        'team_id',
        'position'
    ];
    const NORMAL=1,GOAL_KEEPER=2,CAPTAIN=3;
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function team(){
        return $this->belongsTo(Team::class);
    }
    public function redCards(){
        return $this->hasMany(RedCard::class);
    }
    public function yellowCards(){
        return $this->hasMany(YellowCard::class);
    }

}
