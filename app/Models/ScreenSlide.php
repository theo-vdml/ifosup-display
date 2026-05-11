<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ScreenSlide extends Model
{
    use HasFactory;

    public const TYPE_WELCOME = 'welcome';
    public const TYPE_SCHEDULE = 'schedule';
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';

    protected $fillable = [
        'type',
        'position',
        'motd',
        'duration',
        'image_path',
        'video_path',
        'is_locked',
    ];

    protected $casts = [
        'position' => 'integer',
        'duration' => 'integer',
        'is_locked' => 'boolean',
    ];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    public static function ensureDefaultSlides(): void
    {
        if (self::query()->exists()) {
            return;
        }

        self::query()->create([
            'type' => self::TYPE_WELCOME,
            'position' => 0,
            'is_locked' => true,
        ]);

        self::query()->create([
            'type' => self::TYPE_SCHEDULE,
            'position' => 1,
            'is_locked' => false,
        ]);
    }

    public function imageUrl(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return Storage::url($this->image_path);
    }

    public function videoUrl(): ?string
    {
        if (!$this->video_path) {
            return null;
        }

        return Storage::url($this->video_path);
    }
}
