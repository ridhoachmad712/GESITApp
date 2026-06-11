<?php

namespace App\Models;

use Database\Factories\AgreementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agreement extends Model
{
    /** @use HasFactory<AgreementFactory> */
    use HasFactory;

    public const TYPES = ['MoU', 'MoA', 'IA'];

    protected $fillable = [
        'title',
        'partner_name',
        'type',
        'start_date',
        'end_date',
        'description',
        'document_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * File perjanjian lengkap (dokumen ber-visibility internal).
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Masih berlaku jika tanpa tanggal berakhir atau belum lewat.
     */
    public function isActive(): bool
    {
        return $this->end_date === null || ! $this->end_date->isPast() || $this->end_date->isToday();
    }
}
