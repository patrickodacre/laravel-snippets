<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Knight extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'knights';
    protected $guarded = [];
}
