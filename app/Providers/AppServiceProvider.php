<?php

namespace App\Providers;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use App\Observers\StudentObserver;
use App\Observers\TransactionObserver;
use App\Policies\ClassPolicy;
use App\Policies\StudentPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);

        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(ClassRoom::class, ClassPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Gate::define('manage-gamification', fn (User $user) => $user->hasRole('admin'));
        Gate::define('view-audit-logs', fn (User $user) => $user->hasRole('admin'));
        Gate::define('export-report', fn (User $user) => $user->hasAnyRole(['admin', 'staff', 'wali_kelas']));
        Gate::define('send-whatsapp', fn (User $user) => $user->hasAnyRole(['admin', 'staff']));

        Transaction::observe(TransactionObserver::class);
        Student::observe(StudentObserver::class);
    }
}
