<?php

namespace App\Models;

use Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;
    protected $fillable=[
        'name',
        'grade',
        'class',
        'logo'
    ];
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
    // protected $appends=[
    //     'points'
    // ];
    public function players(){
        return $this->hasMany(Player::class); 
    }
    public function contests(){
        return Contest::where('firstTeam_id',$this->id)
        ->orWhere('secondTeam_id',$this->id)
        ->get();
    }
    public function getLogoAttribute($logo){
        return "storage/logos/$logo";
    }
    public function setNameAttribute($name){
        $this->attributes['name']=trim($name);
    }
    public function getStageAttribute($league)
    {
        return config('stage.'.$league);
    }
    function scopePartOne($query) {
        $query->where('stage',config('stage.PART ONE'));
    }
    // public function getPointsAttribute() {
    //     return random_int(0,100);
    //     return $this->wins*config('consts.win') +
    //     $this->ties*config('consts.tie') +
    //     $this->losses*config('consts.loss');
    // }
    public static function selectViewFields() {
        return Team::select([
                'id','name','logo',
                'wins','ties','losses','points','diff'
        ]);
    }
    
}
