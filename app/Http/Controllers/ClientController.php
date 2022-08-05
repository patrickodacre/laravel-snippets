<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\TagCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function index()
    {

        $client_tags = TagCategory::where('type_id', TagCategory::TYPE_CLIENT)
            ->with('tags')
            ->get()
            ->reduce(function ($carry, $category) {

                $carry = array_merge($carry, $category->tags->toArray());

                return $carry;
            }, []);

        $clients = Client::with('tags')->get();

        return Inertia::render('Clients', [
            'clients' => $clients,
            'tags' => $client_tags,
        ]);
    }

    public function store(Request $request)
    {

        $data = $request->all();

        Validator::make($data, [
            'name_first' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:clients',
        ])->validate();

        try {
            DB::beginTransaction();

            $client = Client::create([
                'name_first' => $data['name_first'],
                'email' => $data['email'],
                'name_last' => $data['name_last'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            // array of tag ids
            $tags = $data['tags'] ?? null;

            if ($tags && count($tags) > 0)
            {
                $client->tags()->attach($tags);
            }

            DB::commit();

            $client->load('tags');

            return response()
                ->json([
                    'client' => $client,
                ], Response::HTTP_CREATED);


        } catch (\Throwable $e) {

            Log::error($e);

            DB::rollback();
        }
    }

    public function update(Request $request, Client $client)
    {
        // name_first is required
        $name_first = $request->get('name_first', $client->name_first);
        $email = $request->get('email', $client->email);

        $name_last = $request->get('name_last', null);
        $description = $request->get('description', null);

        // replace the client completely
        $client->name_first = $name_first;
        $client->name_last = $name_last;
        $client->email = $email;
        $client->description = $description;

        $tags = $request->get('tags', null);

        if ($tags && count($tags) > 0)
        {
            $client->tags()->sync($tags);
        }
        else
        {
            // this is a PUT request, not a PATCH
            $client->tags()->detach();
        }

        $client->save();

        $client->load('tags');

        return response()
            ->json([
                'client' => $client
            ], Response::HTTP_OK);
    }

    public function delete(Request $request, Client $client)
    {

        $client->tags()->detach();

        $client->delete();

        return response()
            ->json([
                'client' => $client->id
            ], Response::HTTP_OK);
    }
}
