<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Services\ChallengeProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PBI-15 Keikutsertaan Tantangan: ikuti, pantau, perbarui, dan mundur.
 */
class ChallengeParticipationController extends Controller
{
    public function index(Request $request, ChallengeProgressService $progress): View
    {
        $user = $request->user();
        $justCompleted = $progress->evaluateRunningFor($user);

        $status = array_key_exists($request->query('status'), ChallengeUser::STATUSES)
            ? $request->query('status')
            : null;

        $all = $user->challenges()->with('category')
            ->orderByPivot('joined_at', 'desc')
            ->get();

        return view('participations.index', [
            'participations' => $status ? $all->where('pivot.status', $status)->values() : $all,
            'statusCounts' => $all->countBy(fn (Challenge $challenge) => $challenge->pivot->status),
            'total' => $all->count(),
            'status' => $status,
            'justCompleted' => $justCompleted,
        ]);
    }

    public function store(Request $request, Challenge $challenge, ChallengeProgressService $progress): RedirectResponse
    {
        $user = $request->user();

        if ($challenge->hasEnded()) {
            return back()->with('error', 'Tantangan ini sudah berakhir sehingga tidak dapat diikuti.');
        }

        $participation = $challenge->participationOf($user);
        if ($participation && $participation->status !== ChallengeUser::STATUS_CANCELLED) {
            return back()->with('error', 'Kamu sudah terdaftar pada tantangan ini.');
        }

        $attributes = ['status' => ChallengeUser::STATUS_RUNNING, 'progress' => 0, 'joined_at' => now()];
        if ($participation) {
            $user->challenges()->updateExistingPivot($challenge->id, $attributes);
        } else {
            $user->challenges()->attach($challenge->id, $attributes);
        }

        return redirect()->route('participations.index')
            ->with('success', "Kamu resmi mengikuti tantangan \"{$challenge->title}\". Catat aktivitasmu setiap hari agar progres terhitung.");
    }

    public function update(Request $request, Challenge $challenge, ChallengeProgressService $progress): RedirectResponse
    {
        $user = $request->user();
        $participation = $challenge->participationOf($user);
        abort_if($participation === null, 404);

        if ($participation->status !== ChallengeUser::STATUS_RUNNING) {
            return back()->with('error', 'Progres hanya dapat diperbarui untuk tantangan yang masih berjalan.');
        }

        $result = $progress->evaluate($user, $challenge, $participation);
        $progressText = number_format($result['progress'], 2, ',', '.');

        $message = match ($result['status']) {
            ChallengeUser::STATUS_COMPLETED => "Selamat! Tantangan \"{$challenge->title}\" selesai dan kamu mendapat {$challenge->reward_points} EcoPoint.",
            ChallengeUser::STATUS_FAILED => "Periode tantangan \"{$challenge->title}\" telah berakhir sebelum target tercapai.",
            default => "Progres tantangan \"{$challenge->title}\" diperbarui: {$progressText}%.",
        };

        return back()->with($result['status'] === ChallengeUser::STATUS_FAILED ? 'error' : 'success', $message);
    }

    public function destroy(Request $request, Challenge $challenge): RedirectResponse
    {
        $user = $request->user();
        $participation = $challenge->participationOf($user);
        abort_if($participation === null, 404);

        if ($participation->status !== ChallengeUser::STATUS_RUNNING) {
            return back()->with('error', 'Hanya tantangan yang masih berjalan yang dapat ditinggalkan.');
        }

        $user->challenges()->updateExistingPivot($challenge->id, ['status' => ChallengeUser::STATUS_CANCELLED]);

        return redirect()->route('participations.index')
            ->with('success', "Kamu mengundurkan diri dari tantangan \"{$challenge->title}\". Progres tidak lagi dihitung.");
    }
}
