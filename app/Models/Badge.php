<?php

namespace App\Models;

use App\Services\GamificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    /**
     * Jenis syarat perolehan lencana beserta labelnya.
     */
    public const TYPES = [
        'activities_logged' => 'Jumlah aktivitas tercatat',
        'streak' => 'Hari pencatatan beruntun',
        'challenges_completed' => 'Tantangan diselesaikan',
        'points' => 'Total EcoPoint',
    ];

    /**
     * Ikon bawaan per jenis ketika lencana belum memiliki gambar.
     */
    public const DEFAULT_ICONS = [
        'activities_logged' => '📝',
        'streak' => '🔥',
        'challenges_completed' => '🏆',
        'points' => '⭐',
    ];

    protected $fillable = ['name', 'description', 'icon', 'type', 'required_value', 'is_active'];

    protected function casts(): array
    {
        return [
            'required_value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(BadgeUser::class)->withPivot('earned_at');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function checkEligibility(User $user): bool
    {
        return app(GamificationService::class)->metricValue($user, $this->type) >= (float) $this->required_value;
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::get(fn () => self::TYPES[$this->type] ?? $this->type);
    }

    protected function iconUrl(): Attribute
    {
        return Attribute::get(fn () => $this->icon ? asset('storage/'.$this->icon) : null);
    }

    protected function defaultIcon(): Attribute
    {
        return Attribute::get(fn () => self::DEFAULT_ICONS[$this->type] ?? '🏅');
    }

    /**
     * Syarat dalam kalimat, misalnya "Mencatat 10 aktivitas".
     */
    protected function requirementText(): Attribute
    {
        return Attribute::get(function () {
            $value = rtrim(rtrim(number_format((float) $this->required_value, 2, ',', '.'), '0'), ',');

            return match ($this->type) {
                'activities_logged' => "Mencatat {$value} aktivitas",
                'streak' => "Mencatat {$value} hari berturut-turut",
                'challenges_completed' => "Menyelesaikan {$value} tantangan",
                'points' => "Mengumpulkan {$value} EcoPoint",
                default => $value,
            };
        });
    }
}
