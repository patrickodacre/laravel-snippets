<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function index()
    {
        return Inertia::render('Clients', [
            'clients' => Client::all(),
        ]);
    }

    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'name_first' => 'required',
        ])->validate();

        $client = Client::create([
            'name_first' => $request->get('name_first'),
            'name_last' => $request->get('name_last'),
            'email' => $request->get('email'),
            'description' => $request->get('description'),
        ]);

        return response()
            ->json([
                'client' => $client,
            ], Response::HTTP_CREATED);
    }

    public function update(Request $request, Client $client)
    {
        // name_first is required
        $name_first = $request->get('name_first', $client->name_first);

        $name_last = $request->get('name_last', null);
        $email = $request->get('email', null);
        $descripion = $request->get('description', null);

        $client->name_first = $name_first;
        $client->name_last = $name_last;
        $client->email = $email;
        $client->description = $descripion;

        $client->save();

        return response()
            ->json([
                'client' => $client
            ], Response::HTTP_OK);
    }

    public function delete(Request $request, Client $client)
    {
        $client->delete();

        return response()
            ->json([
                'client' => $client->id
            ], Response::HTTP_OK);
    }
}
