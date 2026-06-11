<?php

namespace App\Models;

use Database\Factories\LecturerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    /** @use HasFactory<LecturerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'nidn',
        'position',
        'expertise',
        'email',
        'photo_path',
        'publication_url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }
}
