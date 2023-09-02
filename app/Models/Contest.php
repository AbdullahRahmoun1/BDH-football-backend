<?php

namespace App\Models;

use App\Models\Team;
use App\Models\RedCard;
use App\Models\Prediction;
use App\Models\YellowCard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contest extends Model
{
    use HasFactory;
    protected $fillable = [
        'firstTeam_id',
        'secondTeam_id',
        'place',
        'period',
        'league',
        'date'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'period'
    ];
    const YARD = 'Schoolyard', PLAY_GROUND = 'School playground';


    public function firstTeam()
    {
        return $this->belongsTo(Team::class, 'firstTeam_id');
    }
    public function secondTeam()
    {
        return $this->belongsTo(Team::class, 'secondTeam_id');
    }
    public function redCards()
    {
        return $this->hasMany(RedCard::class);
    }
    public function yellowCards()
    {
        return $this->hasMany(YellowCard::class);
    }
    public function goals()
    {
        return $this->hasMany(Goal::class);
    }
    public function predictions()
    {
        return $this->hasMany(Prediction::class);
    }
    public function getStageAttribute($league)
    {
        return config('stage.' . $league);
    }
    public function scopeUndeclaredMatches($query)
    {
        $query->whereNull('date');
    }
    public function scopeDeclaredMatches($query)
    {
        $query->whereNotNull('date');
    }
    public function scopeFinishedMatches($query)
    {
        $query->whereNotNull('date')
            ->where('firstTeamScore', '>=0')
            ->where('secondTeamScore', '>=0');
    }
    public static function winner($match)
    {
        return $match->firstTeamScore > $match->SecondTeamScore ? 1 :
            ($match->firstTeamScore == $match->SecondTeamScore ? 0 : 2);
    }
    public static function determinState($match)
    {
        //takes a match..determines it's state and add a field representing this state
        if($match->date){
            if($match->firstTeamScore>=0 &&$match->secondTeamScore>=0)
                $state=config('consts.finished');
            elseif($match->firstTeamScore>=0 && $match->secondTeamScore<0
            ||$match->secondTeamScore>=0 && $match->firstTeamScore<0){
                $state=config('consts.matchError');
                //TODO: inform the admin that this error happened
            }else{
                $state=config('consts.declared');
            }
        }else {
            $state=config('consts.undeclared');
        }
        $match->state=$state;
    }
    /**
     * Get all of the comments for the Contest
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function predections()
    {
        return $this->hasMany(Prediction::class);
    }

}
