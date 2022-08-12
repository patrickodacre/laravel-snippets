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

    public function grid()
    {
        $client_tags = TagCategory::where('type_id', TagCategory::TYPE_CLIENT)
            ->with('tags')
            ->get()
            ->reduce(function ($carry, $category) {

                $carry = array_merge($carry, $category->tags->toArray());

                return $carry;
            }, []);


        $data = [
            'tags' => $client_tags,
        ];

        return Inertia::render('ClientGrid', $data);
    }

    // POST request
    public function gridData(Request $request)
    {
        $filters = $request->get('filters', []);
        $config = $request->get('config', []);

        $query = Client::with(['tags']);

        // apply filters
        if (count($filters) > 0)
        {

            if ($search = ($filters['search'] ?? null))
            {

                // allow alphanumeric and characters used in emails
                $terms_cleaned = preg_replace("/[^a-zA-Z0-9\(\)\-\+\_@\.]+/", " ", $search);

                $terms = array_reduce(
                    explode(" ", $terms_cleaned),
                    function($carry, $term) {
                        $term = trim($term);

                        if(!empty($term))
                        {
                            $carry[] = strtolower($term);
                        }

                        return $carry;
                    },
                    []
                );

                if (count($terms) > 0)
                {
                    $query->where(function ($q) use ($terms) {

                        $whereType = 'where';
                        foreach ($terms as $term)
                        {

                            $q->{$whereType}('name_first', 'LIKE', '%' . $term . '%');

                            $whereType = 'orWhere';

                            $q->{$whereType}('name_last', 'LIKE', '%' . $term . '%');
                            $q->{$whereType}('email', 'LIKE', '%' . $term . '%');
                            $q->{$whereType}('description', 'LIKE', '%' . $term . '%');

                        }

                    });

                }

            }

            if ($tags = ($filters['tags'] ?? null))
            {
                $whereType = $search ? 'orWhere' : 'where';

                $query->{$whereType}(function ($query) use ($tags) {
                    $query->whereHas('tags', function ($q) use ($tags) {
                        $q->whereIn('tags.id', $tags);
                    });
                });
            }
        }

        $clients = $query->paginate($config['per_page'] ?? 1);

        return response()
            ->json([
                'clients' => $clients,
            ], Response::HTTP_OK);
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
