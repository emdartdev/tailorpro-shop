<?php

namespace App\Models;

use App\Enums\OrderCategory;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'order_number',
        'customer_id',
        'bd_number',
        'order_date',
        'delivery_date',
        'total_amount',
        'advance',
        'due',
        'measurement_notes',
        'extra_notes',
        'status',
        'delivered_date',
        'receiver_name',
        'receiver_phone',
        'order_category',
        'branch',
        'ribon',
        'running_year',
        'trade',
        'office',
        'downloaded_categories',
    ];

    protected function casts(): array
    {
        return [
            'order_number' => 'integer',
            'order_date' => 'date:Y-m-d',
            'delivery_date' => 'date:Y-m-d',
            'delivered_date' => 'date:Y-m-d',
            'total_amount' => 'decimal:2',
            'advance' => 'decimal:2',
            'due' => 'decimal:2',
            'status' => OrderStatus::class,
            'order_category' => OrderCategory::class,
            'ribon' => 'integer',
            'downloaded_categories' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentHistory(): HasMany
    {
        return $this->hasMany(PaymentHistory::class);
    }
}
