<?php

namespace App\Models;

use App\Models\Player;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Goal extends Model
{
    use HasFactory;
    protected $fillable=[
        'player_id',
        'contest_id',
        'team_id'
    ];
    protected $hidden=[
        'created_at',
        'updated_at',
        'player_id',
        'contest_id',
        'id'
    ];
    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
