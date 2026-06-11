<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    public const VISIBILITY_PUBLIC = 'public';

    public const VISIBILITY_MAHASISWA = 'mahasiswa';

    public const VISIBILITY_INTERNAL = 'internal';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'category_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'visibility',
        'academic_year',
        'semester',
        'course_name',
        'lecturer_name',
        'expires_at',
        'uploaded_by',
        'is_featured',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'expires_at' => 'date',
            'download_count' => 'integer',
            'view_count' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', self::VISIBILITY_PUBLIC);
    }

    /**
     * Dokumen yang boleh dilihat user sesuai hierarki visibility:
     * publik < mahasiswa < internal (dosen/admin).
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        $allowed = match (true) {
            $user === null => [self::VISIBILITY_PUBLIC],
            $user->isMahasiswa() => [self::VISIBILITY_PUBLIC, self::VISIBILITY_MAHASISWA],
            default => [self::VISIBILITY_PUBLIC, self::VISIBILITY_MAHASISWA, self::VISIBILITY_INTERNAL],
        };

        return $query->whereIn('visibility', $allowed);
    }
}
