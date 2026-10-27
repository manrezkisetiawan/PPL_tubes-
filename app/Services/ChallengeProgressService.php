<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Menghitung progres keikutsertaan tantangan (PBI-15).
 *
 * Garis dasar adalah rata-rata emisi harian pengguna pada kategori tantangan
 * selama 30 hari sebelum periode tantangan, dihitung per hari yang tercatat.
 * Selama tantangan, setiap hari yang dicatat menyumbang penghematan sebesar
 * garis dasar dikurangi emisi kategori pada hari itu. Tantangan selesai ketika
 * total penghematan mencapai reduction_target (kg CO2e), dan gagal bila
 * periode berakhir sebelum target tercapai.
 */
class ChallengeProgressService
{
    public const BASELINE_DAYS = 30;

    public function __construct(private GamificationService $gamification) {}

    /**
     * @return array{period_start: Carbon, period_end: Carbon, baseline_daily: ?float, logged_days: int, actual_emission: float, saved: float, progress: float}
     */
    public function calculate(User $user, Challenge $challenge, Carbon $joinedAt): array
    {
        $joinedDay = $joinedAt->copy()->startOfDay();
        $periodStart = $joinedDay->greaterThan($challenge->start_date) ? $joinedDay : $challenge->start_date->copy();
        $periodEnd = $challenge->end_date->lessThan(today()) ? $challenge->end_date->copy() : today();

        $baselineFrom = $periodStart->copy()->subDays(self::BASELINE_DAYS);
        $baselineTo = $periodStart->copy()->subDay();
        $baselineDays = $this->loggedDays($user, $baselineFrom, $baselineTo);
        $baselineDaily = $baselineDays > 0
            ? $this->categoryEmission($user, $challenge->category_id, $baselineFrom, $baselineTo) / $baselineDays
            : null;

        $loggedDays = 0;
        $actual = 0.0;
        if ($periodEnd->greaterThanOrEqualTo($periodStart)) {
            $loggedDays = $this->loggedDays($user, $periodStart, $periodEnd);
            $actual = $this->categoryEmission($user, $challenge->category_id, $periodStart, $periodEnd);
        }

        $saved = $baselineDaily === null ? 0.0 : max(0.0, $baselineDaily * $loggedDays - $actual);
        $target = (float) $challenge->reduction_target;
        $progress = $target > 0 ? min(100.0, $saved / $target * 100) : 0.0;

        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'baseline_daily' => $baselineDaily === null ? null : round($baselineDaily, 4),
            'logged_days' => $loggedDays,
            'actual_emission' => round($actual, 4),
            'saved' => round($saved, 4),
            'progress' => round($progress, 2),
        ];
    }

    /**
     * Hitung ulang progres lalu perbarui status keikutsertaan yang masih
     * berjalan. Poin hanya diberikan sekali, saat status berubah menjadi
     * selesai.
     *
     * @return array{status: string, completed_now: bool, progress: float}
     */
    public function evaluate(User $user, Challenge $challenge, ChallengeUser $participation): array
    {
        if ($participation->status !== ChallengeUser::STATUS_RUNNING) {
            return [
                'status' => $participation->status,
                'completed_now' => false,
                'progress' => (float) $participation->progress,
            ];
        }

        $result = $this->calculate($user, $challenge, $participation->joined_at);

        $status = ChallengeUser::STATUS_RUNNING;
        if ($result['progress'] >= 100) {
            $status = ChallengeUser::STATUS_COMPLETED;
        } elseif ($challenge->hasEnded()) {
            $status = ChallengeUser::STATUS_FAILED;
        }

        $completedNow = DB::transaction(function () use ($user, $challenge, $status, $result) {
            $updated = ChallengeUser::query()
                ->where('user_id', $user->id)
                ->where('challenge_id', $challenge->id)
                ->where('status', ChallengeUser::STATUS_RUNNING)
                ->update(['status' => $status, 'progress' => $result['progress']]);

            if ($updated === 0 || $status !== ChallengeUser::STATUS_COMPLETED) {
                return false;
            }

            if ($challenge->reward_points > 0) {
                $this->gamification->awardPoints(
                    $user,
                    $challenge->reward_points,
                    "Menyelesaikan tantangan \"{$challenge->title}\""
                );
            }
            $this->gamification->evaluateBadges($user);

            return true;
        });

        return ['status' => $status, 'completed_now' => $completedNow, 'progress' => $result['progress']];
    }

    /**
     * Evaluasi seluruh tantangan berjalan milik pengguna.
     *
     * @return Collection<int, Challenge> tantangan yang baru saja diselesaikan
     */
    public function evaluateRunningFor(User $user): Collection
    {
        return $user->challenges()
            ->wherePivot('status', ChallengeUser::STATUS_RUNNING)
            ->get()
            ->filter(fn (Challenge $challenge) => $this->evaluate($user, $challenge, $challenge->pivot)['completed_now'])
            ->values();
    }

    private function loggedDays(User $user, Carbon $from, Carbon $to): int
    {
        return Activity::query()
            ->where('user_id', $user->id)
            ->whereBetween('activity_date', [$from->toDateString(), $to->toDateString()])
            ->distinct()
            ->count('activity_date');
    }

    private function categoryEmission(User $user, int $categoryId, Carbon $from, Carbon $to): float
    {
        return (float) Activity::query()
            ->where('user_id', $user->id)
            ->inCategory($categoryId)
            ->whereBetween('activity_date', [$from->toDateString(), $to->toDateString()])
            ->sum('emission');
    }
}
