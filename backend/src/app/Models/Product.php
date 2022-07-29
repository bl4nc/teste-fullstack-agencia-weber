<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'api.product';

    protected $primaryKey = 'product_id';

    protected $fillable = [
        "product_id",
        "product_name",
        "product_description",
        "quantity",
        "price",
        "category",
        "status",
        "picture",
        "created_at",
        "updated_at"
    ];
    protected $keyType = 'string';

    protected $casts = ['category_id' => 'string'];
}
