<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\TagCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    public function index()
    {

        return Inertia::render('Tags', [
            'categories' => TagCategory::with('tags')->get(),
            'types' => TagCategory::typesById(),
        ]);
    }


    // Categories

    public function createCategory(Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'required',
            'type_id' => 'required',
        ])->validate();

        $category = TagCategory::create([
            'name' => $request->get('name'),
            'description' => $request->get('description', null),
            'type_id' => $request->get('type_id'),
        ]);

        // categories are returned in index() with tags, so the UI expects this array
        $category->tags = [];

        return response()
            ->json([
                'category' => $category,
            ], Response::HTTP_CREATED);
    }

    public function updateCategory(Request $request, TagCategory $category)
    {

        $name = $request->get('name', $category->name);
        $description = $request->get('description', null);

        $category->name = $name;
        $category->description = $description;
        $category->save();

        return response()
            ->json([
                'category' => $category,
            ], Response::HTTP_OK);
    }

    public function deleteCategory(TagCategory $category)
    {

        if ($category->tags->count() > 0)
        {
            return response()
                ->json([
                    'message' => 'Cannot delete a category that has tags.'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        $category->delete();

        return response()
            ->json([
                'category' => $category->id
            ], Response::HTTP_OK);
    }


    // Tags

    public function createTag(Request $request)
    {
        Validator::make($request->all(), [
            'category_id' => 'required',
            'name' => 'required',
        ])->validate();

        $tag = Tag::create([
            'name' => $request->get('name'),
            'description' => $request->get('description', null),
            'category_id' => $request->get('category_id'),
        ]);

        return response()
            ->json([
                'tag' => $tag,
            ], Response::HTTP_CREATED);
    }

    public function updateTag(Request $request, Tag $tag)
    {

        $name = $request->get('name', $tag->name);
        $description = $request->get('description', null);

        $tag->name = $name;
        $tag->description = $description;
        $tag->save();

        return response()
            ->json([
                'tag' => $tag,
            ], Response::HTTP_OK);
    }

    public function deleteTag(Tag $tag)
    {
        $tag->delete();

        return response()
            ->json([
                'tag' => $tag->id
            ], Response::HTTP_OK);
    }
}
