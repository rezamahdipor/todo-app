<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'title', 'is_done', 'priority', 'position',
        'category_id', 'due_date', 'completed_at',
    ];

    protected $casts = [
        'is_done'      => 'boolean',
        'due_date'     => 'date:Y-m-d',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}