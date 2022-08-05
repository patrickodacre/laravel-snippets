<?php

namespace App\Models;

use App\Models\TagCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'tags';

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(TagCategory::class);
    }
}
