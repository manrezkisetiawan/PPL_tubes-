<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Services\ChallengeProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PBI-14 Manajemen Tantangan: daftar tantangan bagi pengguna.
 */
class ChallengeController extends Controller
{
    public function index(Request $request): View
    {
        $base = fn () => Challenge::with('category')
            ->withCount(['participants as active_participants_count' => fn ($q) => $q->where('challenge_user.status', '!=', ChallengeUser::STATUS_CANCELLED)]);

        return view('challenges.index', [
            'running' => $base()->running()->orderBy('end_date')->get(),
            'upcoming' => $base()->upcoming()->orderBy('start_date')->get(),
            'participations' => $request->user()->challenges()->get()
                ->mapWithKeys(fn (Challenge $challenge) => [$challenge->id => $challenge->pivot]),
        ]);
    }

    public function show(Request $request, Challenge $challenge, ChallengeProgressService $progress): View
    {
        $user = $request->user();
        $challenge->load('category')->loadCount([
            'participants as active_participants_count' => fn ($q) => $q->where('challenge_user.status', '!=', ChallengeUser::STATUS_CANCELLED),
        ]);

        $participation = $challenge->participationOf($user);
        $breakdown = null;
        if ($participation?->status === ChallengeUser::STATUS_RUNNING) {
            $progress->evaluate($user, $challenge, $participation);
            $participation = $challenge->participationOf($user);
            $breakdown = $progress->calculate($user, $challenge, $participation->joined_at);
        }

        return view('challenges.show', [
            'challenge' => $challenge,
            'participation' => $participation,
            'breakdown' => $breakdown,
            'canJoin' => $challenge->validateParticipation($user),
            'baselineDays' => ChallengeProgressService::BASELINE_DAYS,
        ]);
    }
}
