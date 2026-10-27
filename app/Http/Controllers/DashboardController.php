<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\ChallengeUser;
use App\Services\ChallengeProgressService;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, GamificationService $gamification, ChallengeProgressService $progress): View
    {
        $user = $request->user();
        $gamification->updateStreak($user);
        $progress->evaluateRunningFor($user);

        return view('dashboard', [
            'user' => $user->refresh(),
            'emissionToday' => (float) $user->activities()->whereDate('activity_date', today())->sum('emission'),
            'emissionMonth' => (float) $user->activities()
                ->whereBetween('activity_date', [today()->startOfMonth()->toDateString(), today()->toDateString()])
                ->sum('emission'),
            'runningChallenges' => $user->challenges()->with('category')
                ->wherePivot('status', ChallengeUser::STATUS_RUNNING)
                ->orderBy('end_date')
                ->get(),
            'recentBadges' => $user->badges()->orderByPivot('earned_at', 'desc')->take(4)->get(),
            'badgeCount' => $user->badges()->count(),
            'badgeTotal' => Badge::active()->count(),
            'pointLogs' => $user->pointLogs()->latest()->take(5)->get(),
        ]);
    }
}
