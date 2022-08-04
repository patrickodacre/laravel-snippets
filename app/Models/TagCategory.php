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
    protected $appends = [
        'type',
    ];

    public const TYPE_CLIENT = 1;
    public const TYPE_PROJECT = 2;





    // TYPES
    public static function types()
    {
        return [
            self::TYPE_CLIENT => $client = [
                'id' => self::TYPE_CLIENT,
                'tag' => 'client',
                'name' => 'Client',
            ],
            'client' => $client,

            self::TYPE_PROJECT => $project = [
                'id' => self::TYPE_PROJECT,
                'tag' => 'project',
                'name' => 'Project',
            ],
            'project' => $project,
        ];
    }

    public function getTypeAttribute()
    {
        return self::type($this->type_id);
    }

    public static function type($type)
    {
        return data_get(self::types(), $type);
    }

    public static function typesById()
    {
        return array_filter(self::types(), fn ($config, $type_id) => is_numeric($type_id), ARRAY_FILTER_USE_BOTH);
    }

    public static function typesByTag()
    {
        return array_filter(self::types(), fn ($config, $type_id) => !is_numeric($type_id), ARRAY_FILTER_USE_BOTH);
    }




    // RELATIONSHIPS

    public function tags()
    {
        return $this->hasMany(Tag::class, 'category_id');
    }
}
