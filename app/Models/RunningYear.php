<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RunningYear extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'running_years';

    protected $fillable = [
        'id',
        'year_label',
    ];
}
