<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'api.category';

    protected $primaryKey = 'category_id';

    protected $fillable = [
        "category_id",
        "category_name",
        "created_at",
        "updated_at"
    ];
    protected $keyType = 'string';

    protected $casts = ['category_id' => 'string'];

}
