<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\ChallengeUser;
use App\Models\PointLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Logika lintas model untuk poin, streak, dan lencana (Modul Gamifikasi).
 */
class GamificationService
{
    public function awardPoints(User $user, int $points, string $reason): PointLog
    {
        return DB::transaction(function () use ($user, $points, $reason) {
            $log = $user->pointLogs()->create(['points' => $points, 'reason' => $reason]);
            $user->increment('points', $points);

            return $log;
        });
    }

    /**
     * Hitung ulang jumlah hari pencatatan beruntun yang berakhir hari ini
     * (atau kemarin, jika hari ini belum mencatat).
     */
    public function updateStreak(User $user): int
    {
        $dates = $user->activities()
            ->whereDate('activity_date', '<=', today())
            ->distinct()
            ->orderByDesc('activity_date')
            ->pluck('activity_date')
            ->mapWithKeys(fn ($date) => [Carbon::parse($date)->toDateString() => true]);

        $day = today();
        if (! $dates->has($day->toDateString())) {
            $day->subDay();
        }

        $streak = 0;
        while ($dates->has($day->toDateString())) {
            $streak++;
            $day->subDay();
        }

        if ($user->streak_count !== $streak) {
            $user->forceFill(['streak_count' => $streak])->save();
        }

        return $streak;
    }

    /**
     * Nilai capaian pengguna untuk satu jenis syarat lencana.
     */
    public function metricValue(User $user, string $type): float
    {
        return match ($type) {
            'activities_logged' => (float) $user->activities()->count(),
            'streak' => (float) $user->streak_count,
            'challenges_completed' => (float) $user->challenges()
                ->wherePivot('status', ChallengeUser::STATUS_COMPLETED)
                ->count(),
            'points' => (float) $user->points,
            default => 0.0,
        };
    }

    /**
     * @return array<string, float>
     */
    public function metrics(User $user): array
    {
        return collect(array_keys(Badge::TYPES))
            ->mapWithKeys(fn (string $type) => [$type => $this->metricValue($user, $type)])
            ->all();
    }

    /**
     * Berikan semua lencana aktif yang syaratnya sudah terpenuhi namun belum
     * dimiliki pengguna.
     *
     * @return Collection<int, Badge> lencana yang baru diraih
     */
    public function evaluateBadges(User $user): Collection
    {
        $candidates = Badge::active()
            ->whereNotIn('id', $user->badges()->select('badges.id'))
            ->get();

        if ($candidates->isEmpty()) {
            return $candidates;
        }

        $metrics = $this->metrics($user->refresh());
        $earned = $candidates->filter(
            fn (Badge $badge) => ($metrics[$badge->type] ?? 0) >= (float) $badge->required_value
        );

        if ($earned->isNotEmpty()) {
            $user->badges()->attach(
                $earned->mapWithKeys(fn (Badge $badge) => [$badge->id => ['earned_at' => now()]])->all()
            );
        }

        return $earned->values();
    }
}
