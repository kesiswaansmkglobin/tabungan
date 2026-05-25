<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboard,
    ) {}

    public function __invoke()
    {
        $user = auth()->user();
        $isWaliKelas = $user->hasRole('wali_kelas');
        $allowedClassIds = $isWaliKelas ? $user->classes()->pluck('id') : null;

        $cacheKey = 'dashboard_stats_'.($isWaliKelas ? 'wk_'.$user->id : 'admin');
        $dashboardStats = Cache::remember($cacheKey, 60, fn () => $this->dashboard->stats($allowedClassIds)
        );

        return Inertia::render('Dashboard', $dashboardStats);
    }
}
