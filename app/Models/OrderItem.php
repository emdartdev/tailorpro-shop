<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'order_id',
        'item_type',
        'item_name',
        'measurements',
        'notes',
        'iv_number',
        'cutting_slip_downloaded',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'measurements' => 'array',
            'cutting_slip_downloaded' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
