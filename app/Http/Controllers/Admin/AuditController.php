<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Activity::with('causer:id,name');

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('description')) {
            $query->where('description', 'like', '%'.$request->description.'%');
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to.' 23:59:59');
        }

        $logs = $query->latest()->paginate(25);

        $logNames = cache()->remember('audit_log_names', 3600, fn () => Activity::select('log_name')->distinct()->pluck('log_name')
        );
        $events = cache()->remember('audit_events', 3600, fn () => Activity::select('event')->whereNotNull('event')->distinct()->pluck('event')
        );

        return Inertia::render('Admin/Audit', [
            'logs' => $logs,
            'logNames' => $logNames,
            'events' => $events,
            'filters' => $request->only(['log_name', 'event', 'description', 'date_from', 'date_to']),
        ]);
    }
}
