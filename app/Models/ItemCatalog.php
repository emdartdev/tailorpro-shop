<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCatalog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'item_catalog';

    protected $fillable = [
        'id',
        'name',
        'price',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }
}
