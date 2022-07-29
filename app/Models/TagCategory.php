<?php

namespace App\Models;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagCategory extends Model
{
    use HasFactory;

    protected $table = 'tag_categories';

    protected $guarded = [];


    public function tags()
    {
        return $this->hasMany(Tag::class, 'category_id');
    }
}
