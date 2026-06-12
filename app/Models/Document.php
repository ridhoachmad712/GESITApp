<?php

namespace App\Models;

use App\Http\Controllers\HomeController;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory, SoftDeletes;

    public const VISIBILITY_PUBLIC = 'public';

    public const VISIBILITY_MAHASISWA = 'mahasiswa';

    public const VISIBILITY_INTERNAL = 'internal';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ARCHIVED = 'archived';

    protected static function booted(): void
    {
        // Bersihkan cache beranda saat dokumen berubah secara berarti
        // (increment counter unduhan/lihat sengaja tidak ikut mem-bust)
        static::saved(function (Document $document): void {
            if ($document->wasRecentlyCreated || $document->wasChanged([
                'title', 'slug', 'status', 'visibility', 'category_id', 'is_featured', 'deleted_at',
            ])) {
                Cache::forget(HomeController::CACHE_KEY);
            }
        });

        // CLAUDE.md aturan 4 — penghapusan (soft delete) dicatat ke activity_logs.
        static::deleted(function (Document $document): void {
            Cache::forget(HomeController::CACHE_KEY);
            ActivityLog::create([
                'user_id' => auth()->id(),
                'document_id' => $document->isForceDeleting() ? null : $document->id,
                'action' => ActivityLog::ACTION_DELETE,
                'ip_address' => request()->ip(),
                'user_agent' => Str::limit((string) request()->userAgent(), 500),
            ]);
        });
    }

    protected $fillable = [
        'title',
        'slug',
        'description',
        'category_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'external_url',
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

    /**
     * Kriteria akreditasi yang dibuktikan dokumen ini (F3.3).
     */
    public function criteria(): BelongsToMany
    {
        return $this->belongsToMany(AccreditationCriterion::class, 'document_criteria', 'document_id', 'criteria_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Dokumen yang tersimpan di penyimpanan eksternal (mis. Google Drive),
     * bukan file di disk server.
     */
    public function isExternal(): bool
    {
        return filled($this->external_url);
    }

    /**
     * URL embed preview Google Drive, atau null bila bukan tautan Drive.
     */
    public function googleDriveEmbedUrl(): ?string
    {
        if (! $this->isExternal()) {
            return null;
        }

        if (preg_match('#https://(drive|docs)\.google\.com/(?:file/d/|document/d/|spreadsheets/d/|presentation/d/)([\w-]+)#', $this->external_url, $matches)) {
            $base = match (true) {
                str_contains($this->external_url, '/document/d/') => 'https://docs.google.com/document/d/',
                str_contains($this->external_url, '/spreadsheets/d/') => 'https://docs.google.com/spreadsheets/d/',
                str_contains($this->external_url, '/presentation/d/') => 'https://docs.google.com/presentation/d/',
                default => 'https://drive.google.com/file/d/',
            };

            return $base.$matches[2].'/preview';
        }

        return null;
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
