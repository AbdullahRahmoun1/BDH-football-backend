<?php

namespace App\Models;

use App\Models\Team;
use App\Models\RedCard;
use App\Models\YellowCard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contest extends Model
{
    use HasFactory;
    protected $fillable=[
        'firstTeam_id',
        'secondTeam_id',
        'place',
        'period',
        'league',
    ];
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
    const YARD='Schoolyard',PLAY_GROUND='School playground';


    public function firstTeam(){
        return $this->belongsTo(Team::class,'firstTeam_id');
    }
    public function secondTeam(){
        return $this->belongsTo(Team::class,'secondTeam_id');
    }
    public function redCards(){
        return $this->hasMany(RedCard::class);
    }
    public function yellowCards(){
        return $this->hasMany(YellowCard::class);
    }
}
