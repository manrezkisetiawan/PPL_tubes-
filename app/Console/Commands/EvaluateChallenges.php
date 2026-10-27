<?php

namespace App\Console\Commands;

use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Models\User;
use App\Services\ChallengeProgressService;
use Illuminate\Console\Command;

class EvaluateChallenges extends Command
{
    protected $signature = 'challenges:evaluate';

    protected $description = 'Hitung ulang progres seluruh keikutsertaan tantangan yang masih berjalan';

    public function handle(ChallengeProgressService $progress): int
    {
        $summary = [ChallengeUser::STATUS_RUNNING => 0, ChallengeUser::STATUS_COMPLETED => 0, ChallengeUser::STATUS_FAILED => 0];

        User::query()
            ->whereHas('challenges', fn ($q) => $q->where('challenge_user.status', ChallengeUser::STATUS_RUNNING))
            ->each(function (User $user) use ($progress, &$summary) {
                $user->challenges()
                    ->wherePivot('status', ChallengeUser::STATUS_RUNNING)
                    ->get()
                    ->each(function (Challenge $challenge) use ($user, $progress, &$summary) {
                        $summary[$progress->evaluate($user, $challenge, $challenge->pivot)['status']]++;
                    });
            });

        $this->info(sprintf(
            'Dievaluasi: %d masih berjalan, %d selesai, %d gagal.',
            $summary[ChallengeUser::STATUS_RUNNING],
            $summary[ChallengeUser::STATUS_COMPLETED],
            $summary[ChallengeUser::STATUS_FAILED],
        ));

        return self::SUCCESS;
    }
}
