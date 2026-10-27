<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BadgeRequest;
use App\Models\Badge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * PBI-13 Manajemen Lencana (sisi administrator).
 */
class BadgeController extends Controller
{
    public function index(): View
    {
        $badges = Badge::withCount('users')
            ->orderBy('type')
            ->orderBy('required_value')
            ->paginate(15);

        return view('admin.badges.index', compact('badges'));
    }

    public function create(): View
    {
        return view('admin.badges.create', ['badge' => new Badge(['is_active' => true])]);
    }

    public function store(BadgeRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['icon', 'remove_icon']);
        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('badges', 'public');
        }

        $badge = Badge::create($data);

        return redirect()->route('admin.badges.index')
            ->with('success', "Lencana \"{$badge->name}\" berhasil ditambahkan.");
    }

    public function edit(Badge $badge): View
    {
        $badge->loadCount('users');

        return view('admin.badges.edit', compact('badge'));
    }

    public function update(BadgeRequest $request, Badge $badge): RedirectResponse
    {
        $data = $request->safe()->except(['icon', 'remove_icon']);

        if ($request->hasFile('icon') || $request->boolean('remove_icon')) {
            $this->deleteIcon($badge);
            $data['icon'] = $request->hasFile('icon')
                ? $request->file('icon')->store('badges', 'public')
                : null;
        }

        $badge->update($data);

        return redirect()->route('admin.badges.index')
            ->with('success', "Lencana \"{$badge->name}\" berhasil diperbarui.");
    }

    public function destroy(Badge $badge): RedirectResponse
    {
        $holders = $badge->users()->count();
        if ($holders > 0) {
            return back()->with('error', "Lencana \"{$badge->name}\" sudah diraih {$holders} pengguna sehingga tidak dapat dihapus. Nonaktifkan lencana melalui menu Ubah.");
        }

        $this->deleteIcon($badge);
        $badge->delete();

        return redirect()->route('admin.badges.index')
            ->with('success', "Lencana \"{$badge->name}\" berhasil dihapus.");
    }

    private function deleteIcon(Badge $badge): void
    {
        if ($badge->icon) {
            Storage::disk('public')->delete($badge->icon);
        }
    }
}
