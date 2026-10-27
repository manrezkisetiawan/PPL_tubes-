<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChallengeRequest;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\ChallengeUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PBI-14 Manajemen Tantangan (sisi administrator).
 */
class ChallengeController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->query('periode');

        $challenges = Challenge::with('category')
            ->withCount(['participants as active_participants_count' => fn ($q) => $q->where('challenge_user.status', '!=', ChallengeUser::STATUS_CANCELLED)])
            ->period($period)
            ->orderByDesc('start_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.challenges.index', compact('challenges', 'period'));
    }

    public function create(): View
    {
        return view('admin.challenges.create', [
            'challenge' => new Challenge([
                'start_date' => today(),
                'end_date' => today()->addDays(6),
                'reward_points' => 100,
            ]),
            'categories' => $this->categories(),
        ]);
    }

    public function store(ChallengeRequest $request): RedirectResponse
    {
        $challenge = Challenge::create($request->validated());

        return redirect()->route('admin.challenges.show', $challenge)
            ->with('success', "Tantangan \"{$challenge->title}\" berhasil dibuat.");
    }

    public function show(Challenge $challenge): View
    {
        $challenge->load('category');
        $participants = $challenge->participants()
            ->orderByPivot('progress', 'desc')
            ->orderByPivot('joined_at')
            ->get();
        $statusCounts = $participants->countBy(fn ($user) => $user->pivot->status);

        return view('admin.challenges.show', compact('challenge', 'participants', 'statusCounts'));
    }

    public function edit(Challenge $challenge): View
    {
        return view('admin.challenges.edit', [
            'challenge' => $challenge,
            'categories' => $this->categories($challenge->category_id),
        ]);
    }

    public function update(ChallengeRequest $request, Challenge $challenge): RedirectResponse
    {
        $challenge->update($request->validated());

        return redirect()->route('admin.challenges.show', $challenge)
            ->with('success', "Tantangan \"{$challenge->title}\" berhasil diperbarui.");
    }

    public function destroy(Challenge $challenge): RedirectResponse
    {
        if (! $challenge->isDeletable()) {
            return back()->with('error', "Tantangan \"{$challenge->title}\" sudah diikuti peserta sehingga tidak dapat dihapus.");
        }

        $challenge->delete();

        return redirect()->route('admin.challenges.index')
            ->with('success', "Tantangan \"{$challenge->title}\" berhasil dihapus.");
    }

    private function categories(?int $includeId = null)
    {
        return Category::query()
            ->where(fn ($q) => $q->where('is_active', true)->when($includeId, fn ($q) => $q->orWhere('id', $includeId)))
            ->orderBy('name')
            ->get();
    }
}
