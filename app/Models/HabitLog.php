<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HabitLog extends Model
{
    protected $fillable = ['done_on'];

    protected $casts = [
        'done_on' => 'date:Y-m-d',
    ];

    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class);
    }
}