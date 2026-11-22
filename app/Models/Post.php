<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = ['title', 'description', 'body'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(now());
    }

    // اسلاگ فارسی (حروف و عددها می‌مونن، بقیه می‌شن خط تیره)
    public static function makeSlug(string $title): string
    {
        $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', mb_strtolower($title));
        $slug = trim(mb_substr(trim($slug, '-'), 0, 80), '-');

        return $slug !== '' ? $slug : 'post';
    }

    // توضیح کوتاه برای متا و کارت‌ها
    public function summary(int $limit = 160): string
    {
        if ($this->description) {
            return $this->description;
        }

        $text = preg_replace('/\s+/u', ' ', str_replace('## ', '', $this->body));

        return Str::limit(trim($text), $limit);
    }

    public function readingMinutes(): int
    {
        $words = count(preg_split('/\s+/u', trim($this->body), -1, PREG_SPLIT_NO_EMPTY));

        return max(1, (int) ceil($words / 200));
    }
}