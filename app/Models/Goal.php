<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'updated_at'
    ];
}
