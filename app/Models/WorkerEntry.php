<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerEntry extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'worker_id',
        'entry_date',
        'khoroch_date',
        'serial',
        'name',
        'item',
        'quantity',
        'unit_price',
        'total_taka',
        'khoroch',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date:Y-m-d',
            'khoroch_date' => 'date:Y-m-d',
            'serial' => 'integer',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_taka' => 'decimal:2',
            'khoroch' => 'decimal:2',
        ];
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class)->withTrashed();
    }
}
