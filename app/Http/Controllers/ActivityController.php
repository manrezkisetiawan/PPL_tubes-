<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityRequest;
use App\Models\Activity;
use App\Models\Category;
use App\Models\EmissionFactor;
use App\Services\ChallengeProgressService;
use App\Services\EmissionCalculator;
use App\Services\GamificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pencatatan aktivitas versi minimal sebagai sumber data progres tantangan
 * dan lencana. Versi lengkap (ubah, filter riwayat, template) adalah PBI-04.
 */
class ActivityController extends Controller
{
    public function __construct(
        private GamificationService $gamification,
        private ChallengeProgressService $progress,
    ) {}

    public function index(Request $request): View
    {
        $categories = Category::active()
            ->with(['emissionFactors' => fn ($q) => $q->active()->orderBy('name')])
            ->orderBy('name')
            ->get();

        $activities = $request->user()->activities()
            ->with('emissionFactor.category')
            ->orderByDesc('activity_date')
            ->orderByDesc('id')
            ->paginate(15);

        return view('activities.index', compact('categories', 'activities'));
    }

    public function store(StoreActivityRequest $request, EmissionCalculator $calculator): RedirectResponse
    {
        $user = $request->user();
        $factor = EmissionFactor::findOrFail($request->integer('emission_factor_id'));
        $emission = $calculator->calculate($factor, (float) $request->input('amount'));

        $user->activities()->create($request->safe()->only(['emission_factor_id', 'amount', 'activity_date', 'note']) + [
            'emission' => $emission,
        ]);

        $this->gamification->updateStreak($user);
        $completed = $this->progress->evaluateRunningFor($user);
        $badges = $this->gamification->evaluateBadges($user);

        return redirect()->route('activities.index')
            ->with('success', 'Aktivitas tersimpan: '.format_kg($emission).'.')
            ->with('rewards', [
                'challenges' => $completed->pluck('title')->all(),
                'badges' => $badges->pluck('name')->all(),
            ]);
    }

    public function destroy(Request $request, Activity $activity): RedirectResponse
    {
        abort_unless($activity->user_id === $request->user()->id, 403);

        $activity->delete();
        $this->gamification->updateStreak($request->user());
        $this->progress->evaluateRunningFor($request->user());

        return back()->with('success', 'Catatan aktivitas dihapus.');
    }
}
