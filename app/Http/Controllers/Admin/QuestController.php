<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestRequest;
use App\Http\Requests\UpdateQuestRequest;
use App\Models\Quest;
use App\Models\Tier;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class QuestController extends Controller
{
    public function index(): Response
    {
        $quests = Quest::latest()->paginate(25);
        $tiers = Tier::orderBy('order_index')->orderBy('min_balance')->paginate(25);

        return Inertia::render('Admin/Gamification', [
            'quests' => $quests,
            'tiers' => $tiers,
        ]);
    }

    public function store(StoreQuestRequest $request): RedirectResponse
    {
        Quest::create($request->validated());

        return back()->with('success', 'Quest berhasil ditambahkan.');
    }

    public function update(UpdateQuestRequest $request, Quest $quest): RedirectResponse
    {
        $quest->update($request->validated());

        return back()->with('success', 'Quest berhasil diperbarui.');
    }

    public function destroy(Quest $quest): RedirectResponse
    {
        if ($quest->completions()->exists()) {
            return back()->with('error', 'Quest tidak bisa dihapus karena sudah ada penyelesaian.');
        }

        $quest->delete();

        return back()->with('success', 'Quest berhasil dihapus.');
    }
}
