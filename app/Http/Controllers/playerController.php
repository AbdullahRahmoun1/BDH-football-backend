<?php
namespace App\Http\Controllers;

use App\Models\Player;

class playerController extends Controller
{
    public function show(Player $id)
    {
        return $id;
    }
}