<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTierRequest;
use App\Http\Requests\UpdateTierRequest;
use App\Models\Quest;
use App\Models\Tier;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TierController extends Controller
{
    public function index(): Response
    {
        $tiers = Tier::orderBy('order_index')->orderBy('min_balance')->paginate(25);
        $quests = Quest::latest()->paginate(25);

        return Inertia::render('Admin/Gamification', [
            'tiers' => $tiers,
            'quests' => $quests,
        ]);
    }

    public function store(StoreTierRequest $request): RedirectResponse
    {
        Tier::create($request->validated());

        return back()->with('success', 'Tier berhasil ditambahkan.');
    }

    public function update(UpdateTierRequest $request, Tier $tier): RedirectResponse
    {
        $tier->update($request->validated());

        return back()->with('success', 'Tier berhasil diperbarui.');
    }

    public function destroy(Tier $tier): RedirectResponse
    {
        if ($tier->studentProgresses()->exists()) {
            return back()->with('error', 'Tier tidak bisa dihapus karena masih digunakan oleh siswa.');
        }

        $tier->delete();

        return back()->with('success', 'Tier berhasil dihapus.');
    }
}
