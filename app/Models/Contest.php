<?php

namespace App\Models;

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
    const YARD='Schoolyard',PLAY_GROUND='School playground';


    protected function firstTeam(){
        return $this->belongsTo(Team::class,'firstTeam_id');
    }
    protected function secondTeam(){
        return $this->belongsTo(Team::class,'secondTeam_id');
    }
    public function redCards(){
        return $this->hasMany(RedCard::class);
    }
    public function yellowCards(){
        return $this->hasMany(YellowCard::class);
    }
}
