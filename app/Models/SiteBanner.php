<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteBanner extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'cta_label',
        'cta_link',
        'active_from',
        'active_until',
        'is_enabled',
    ];

    protected $casts = [
        'active_from'  => 'datetime',
        'active_until' => 'datetime',
        'is_enabled'   => 'boolean',
    ];

    /**
     * Returns the first banner that should be shown right now, or null.
     * Result is an array ready to be shared via Inertia.
     *
     * @return array<string,mixed>|null
     */
    public static function getActive(): ?array
    {
        $now = now();

        $banner = static::where('is_enabled', true)
            ->where(fn ($q) => $q->whereNull('active_from')->orWhere('active_from', '<=', $now))
            ->where(fn ($q) => $q->whereNull('active_until')->orWhere('active_until', '>=', $now))
            ->orderByDesc('active_from')
            ->first();

        if (! $banner) {
            return null;
        }

        return [
            'title'       => $banner->title,
            'description' => $banner->description,
            'image'       => $banner->image ? Storage::url($banner->image) : null,
            'cta_label'   => $banner->cta_label,
            'cta_link'    => $banner->cta_link,
            'date_start'  => $banner->active_from?->toDateString(),
            'date_end'    => $banner->active_until?->toDateString(),
        ];
    }
}
