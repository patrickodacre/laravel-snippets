<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class KnightController extends Controller
{

    public function index(Team $team)
    {
        return Inertia::render('Knights', [
            'team' => $team
        ]);
    }

}
