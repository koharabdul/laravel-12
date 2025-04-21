<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = "product";
    protected $primaryKey = "id";
    protected $fillable = [
        'id',
        'name',
        'slug',
        'product_category',
        'description',
        'price'
    ];
}
