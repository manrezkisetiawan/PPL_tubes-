<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PBI-13 Manajemen Lencana: galeri lencana milik pengguna.
 */
class BadgeController extends Controller
{
    public function index(Request $request, GamificationService $gamification): View
    {
        $user = $request->user();
        $gamification->updateStreak($user);
        $newBadges = $gamification->evaluateBadges($user);

        $earned = $user->badges()->get()->keyBy('id');
        // Lencana yang sudah dinonaktifkan tetap tampil bagi pemiliknya.
        $typeOrder = array_flip(array_keys(Badge::TYPES));
        $badges = Badge::active()->get()->merge($earned)
            ->sortBy(fn (Badge $badge) => [$typeOrder[$badge->type] ?? PHP_INT_MAX, (float) $badge->required_value])
            ->values();

        return view('badges.index', [
            'badges' => $badges,
            'earned' => $earned,
            'newBadges' => $newBadges,
            'metrics' => $gamification->metrics($user),
        ]);
    }
}
