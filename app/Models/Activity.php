<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'emission_factor_id', 'amount', 'emission', 'activity_date', 'note'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'emission' => 'decimal:4',
        ];
    }

    /**
     * Disimpan sebagai 'Y-m-d' murni agar kueri rentang tanggal konsisten di
     * MySQL maupun SQLite.
     */
    protected function activityDate(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : Carbon::parse($value),
            set: fn ($value) => $value === null ? null : Carbon::parse($value)->toDateString(),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emissionFactor(): BelongsTo
    {
        return $this->belongsTo(EmissionFactor::class);
    }

    public function scopeInCategory(Builder $query, int $categoryId): void
    {
        $query->whereHas('emissionFactor', fn (Builder $q) => $q->where('category_id', $categoryId));
    }
}
