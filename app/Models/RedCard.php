<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedCard extends Model
{
    use HasFactory;
    protected $fillable=[
        'player_id',
        'contest_id'
    ];
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
