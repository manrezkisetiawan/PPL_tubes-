<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Challenge extends Model
{
    use HasFactory;

    public const PERIOD_UPCOMING = 'akan_datang';

    public const PERIOD_RUNNING = 'berjalan';

    public const PERIOD_ENDED = 'berakhir';

    public const PERIODS = [
        self::PERIOD_RUNNING => 'Sedang berjalan',
        self::PERIOD_UPCOMING => 'Akan datang',
        self::PERIOD_ENDED => 'Berakhir',
    ];

    protected $fillable = [
        'category_id', 'title', 'description', 'start_date', 'end_date', 'reduction_target', 'reward_points',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'reduction_target' => 'decimal:2',
            'reward_points' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(ChallengeUser::class)
            ->withPivot('status', 'progress', 'joined_at');
    }

    public function scopeRunning(Builder $query): void
    {
        $query->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today());
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->whereDate('start_date', '>', today());
    }

    public function scopeEnded(Builder $query): void
    {
        $query->whereDate('end_date', '<', today());
    }

    public function scopeNotEnded(Builder $query): void
    {
        $query->whereDate('end_date', '>=', today());
    }

    public function scopePeriod(Builder $query, ?string $period): void
    {
        match ($period) {
            self::PERIOD_RUNNING => $query->running(),
            self::PERIOD_UPCOMING => $query->upcoming(),
            self::PERIOD_ENDED => $query->ended(),
            default => null,
        };
    }

    protected function period(): Attribute
    {
        return Attribute::get(function () {
            if ($this->start_date->isAfter(today())) {
                return self::PERIOD_UPCOMING;
            }

            return $this->end_date->isBefore(today()) ? self::PERIOD_ENDED : self::PERIOD_RUNNING;
        });
    }

    protected function periodLabel(): Attribute
    {
        return Attribute::get(fn () => self::PERIODS[$this->period]);
    }

    protected function durationDays(): Attribute
    {
        return Attribute::get(fn () => (int) $this->start_date->diffInDays($this->end_date) + 1);
    }

    public function hasEnded(): bool
    {
        return $this->period === self::PERIOD_ENDED;
    }

    /**
     * Keikutsertaan pengguna pada tantangan ini, termasuk yang berstatus batal.
     */
    public function participationOf(User $user): ?ChallengeUser
    {
        return $this->participants()->whereKey($user->getKey())->first()?->pivot;
    }

    /**
     * Pengguna boleh mengikuti tantangan selama periodenya belum berakhir dan
     * ia belum terdaftar (atau sebelumnya mengundurkan diri).
     */
    public function validateParticipation(User $user): bool
    {
        if ($this->hasEnded()) {
            return false;
        }

        $participation = $this->participationOf($user);

        return $participation === null || $participation->status === ChallengeUser::STATUS_CANCELLED;
    }

    /**
     * Tantangan hanya boleh dihapus selama belum diikuti peserta aktif.
     */
    public function isDeletable(): bool
    {
        return ! $this->participants()
            ->wherePivot('status', '!=', ChallengeUser::STATUS_CANCELLED)
            ->exists();
    }
}
