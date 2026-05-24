<?php

namespace App\Http\Middleware;

use App\Models\SchoolData;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $guard = 'web';

        if ($request->user('student')) {
            $user = $request->user('student');
            $guard = 'student';
        } elseif ($user) {
            $user->load('roles');
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'guard' => $guard,
            ],
            'school' => fn () => Cache::remember('school_data', 3600, function () {
                try {
                    return SchoolData::first();
                } catch (QueryException) {
                    return null;
                }
            }),
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
