<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'title', 'is_done', 'status', 'priority', 'position',
        'category_id', 'due_date', 'completed_at',
    ];

    protected $casts = [
        'is_done'      => 'boolean',
        'due_date'     => 'date:Y-m-d',
        'completed_at' => 'datetime',
    ];

    // هر وقت is_done عوض بشه (از لیست، زیرتسک یا کانبان)، status هم هماهنگ می‌شه
    protected static function booted(): void
    {
        static::saving(function (Task $task) {
            if (! $task->isDirty('is_done')) {
                return;
            }

            if ($task->is_done) {
                $task->status = 'done';
            } elseif ($task->status === 'done') {
                $task->status = 'todo';
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class)
            ->orderBy('position')
            ->orderBy('id');
    }
}