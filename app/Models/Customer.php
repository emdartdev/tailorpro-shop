<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'name',
        'phone',
        'bd_number',
        'total_orders',
        'total_spent',
        'due_amount',
    ];

    protected function casts(): array
    {
        return [
            'total_orders' => 'integer',
            'total_spent' => 'decimal:2',
            'due_amount' => 'decimal:2',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
