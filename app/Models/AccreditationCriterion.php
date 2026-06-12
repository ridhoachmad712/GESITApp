<?php

namespace App\Models;

use Database\Factories\AccreditationCriterionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AccreditationCriterion extends Model
{
    /** @use HasFactory<AccreditationCriterionFactory> */
    use HasFactory;

    protected $table = 'accreditation_criteria';

    protected $fillable = [
        'code',
        'name',
        'instrument',
        'sort_order',
    ];

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_criteria', 'criteria_id', 'document_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }

    /**
     * Label ringkas: "K1 — Visi, Misi, …".
     */
    public function getFullLabelAttribute(): string
    {
        return $this->code.' — '.$this->name;
    }
}
