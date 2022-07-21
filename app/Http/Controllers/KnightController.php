<?php

namespace App\Http\Controllers;

use App\Models\Knight;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class KnightController extends Controller
{

    public function index(Team $team)
    {
        return Inertia::render('Knights', [
            'team' => $team,
            'knights' => Knight::all(),
        ]);
    }

    public function store(Request $request, Team $team)
    {
        Validator::make($request->all(), [
            'name' => 'required',
        ])->validate();

        $knight = Knight::create([
            'name' => $request->get('name'),
        ]);

        return response()
            ->json([
                'knight' => $knight,
            ], Response::HTTP_CREATED);
    }

    public function update(Request $request, Knight $knight)
    {
        // name is required
        $name = $request->get('name', $knight->name);

        $knight->name = $name;

        $knight->save();

        return response()
            ->json([
                'knight' => $knight
            ], Response::HTTP_OK);
    }

    public function delete(Request $request, Knight $knight)
    {
        $knight->delete();

        return response()
            ->json([
                'knight' => $knight->id
            ], Response::HTTP_OK);
    }
}
