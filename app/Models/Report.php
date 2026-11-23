<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    public const REASONS = [
        'spam'          => 'اسپم یا تبلیغ',
        'abuse'         => 'توهین یا آزار',
        'inappropriate' => 'محتوای نامناسب',
        'other'         => 'سایر',
    ];

    protected $fillable = ['user_id', 'type', 'target_id', 'reason', 'note'];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}