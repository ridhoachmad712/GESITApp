<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'parent_id',
        'sort_order',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Kategori utama (tanpa induk), urut sesuai sort_order.
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id')->orderBy('sort_order');
    }

    /**
     * Opsi select bertingkat: kategori utama jadi optgroup,
     * kategori utama tanpa sub langsung bisa dipilih.
     */
    public static function groupedSelectOptions(): array
    {
        $options = [];

        foreach (static::with('children')->root()->get() as $root) {
            if ($root->children->isEmpty()) {
                $options[$root->id] = $root->name;

                continue;
            }

            $options[$root->name] = $root->children->pluck('name', 'id')->all();
        }

        return $options;
    }

    /**
     * Kategori utama beserta jumlah dokumen terbit yang boleh dilihat
     * $user (termasuk dokumen di sub-kategorinya), pada atribut
     * `visible_documents_count`.
     */
    public static function rootsWithVisibleDocumentCounts(?User $user): Collection
    {
        $counts = Document::published()->visibleTo($user)
            ->selectRaw('category_id, COUNT(*) as aggregate')
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id');

        return static::with('children')->root()->get()
            ->map(function (Category $category) use ($counts): Category {
                $category->visible_documents_count = ($counts[$category->id] ?? 0)
                    + $category->children->sum(fn (Category $child): int => $counts[$child->id] ?? 0);

                return $category;
            });
    }
}
