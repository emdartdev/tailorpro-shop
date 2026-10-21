<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPayment extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'worker_id',
        'amount',
        'payment_date',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date:Y-m-d',
            'created_at' => 'datetime',
        ];
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class)->withTrashed();
    }
}
