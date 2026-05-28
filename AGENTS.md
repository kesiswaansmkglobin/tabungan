# AGENTS.md — Tabungan Siswa SMK Globin (Laravel 11)

## Stack
- Laravel 11 + PHP ^8.2 + **MySQL** + **Inertia + React + TypeScript** + Tailwind CSS + Vite
- Breeze (auth scaffolding), Sanctum (API tokens), Ziggy (client-side routes)
- Queue: database driver (jobs table). Cache/Session: database driver.
- **Key PHP packages:** barryvdh/laravel-dompdf, maatwebsite/excel, simplesoftwareio/simple-qrcode, spatie/laravel-permission, spatie/laravel-activitylog, intervention/image-laravel, laravel/reverb

## Quick start (development)
```bash
composer install
npm install
php artisan key:generate          # already done if .env has APP_KEY
php artisan migrate               # must be MySQL, with db "tabungan" created
php artisan storage:link
npm run build                     # production assets (tsc + vite × 2)
npm run dev                       # vite dev (no ssr)
php artisan serve                 # dev server at localhost:8000
```

## Production deployment
```bash
# 1. Clone & install dependencies
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 2. Configure .env
cp .env.example .env
# Edit .env: set APP_KEY, APP_URL, DB_*, MAIL_*, etc.
php artisan key:generate

# 3. Database & storage
php artisan migrate --force
php artisan storage:link --force

# 4. Optimize Laravel
php artisan optimize
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Queue worker & Reverb (must always run — via supervisor)
#   - Copy supervisor-tabungan.conf to /etc/supervisor/conf.d/
#   - supervisorctl reread && supervisorctl update && supervisorctl start all
#   - Manages both queue worker (2 processes) & Reverb WebSocket server

# 6. Scheduler (cron)
#   - Add crontab.txt entry: * * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1

# 7. Web server
#   - Point document root to public/
#   - Ensure HTTPS (set SESSION_SECURE_COOKIE=true in .env)

# Or use the deploy script:
bash deploy.sh
```

## Production checklist
- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_URL` set to real domain (HTTPS)
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] DB credentials secured (not root/no-password)
- [ ] Queue worker & Reverb running (supervisor — both processes)
- [ ] Cron job for scheduler
- [ ] `public/hot` removed (Vite dev file)
- [ ] Storage permissions: `www-data` or equivalent
- [ ] SSL certificate installed on web server

## Commands
| Task | Command |
|---|---|
| Run all tests | `php artisan test` |
| Run a specific test | `php artisan test --filter=TransactionTest` |
| Format code | `./vendor/bin/pint` |
| Dev server | `php artisan serve` |
| Vite dev | `npm run dev` (keep running alongside serve) |
| Queue worker | `php artisan queue:listen --tries=1` |
| Reverb server | `php artisan reverb:start` (prod: supervisor) |
| View logs | `php artisan pail` |
| Build assets | `npm run build` |
| Full dev env | `composer dev` (serve + queue:listen + pail + vite concurrently) |
| Create admin user | `php artisan tinker` then `User::create(...)->assignRole('admin')` |

## Seeders
- `php artisan db:seed` or `migrate:fresh --seed` to run all
- **SchoolDataSeeder**: 1 school (SMK Globin)
- **UserSeeder**: admin/staff/2 wali_kelas users with `password` default
- **GamificationSeeder**: 4 tiers (Bronze/Silver/Gold/Platinum) + 4 quests
- **DemoSeeder**: 6 classes (X-A through XII-B) + 18 students + 19 sample transactions

## App structure
```
routes/
  web.php         — web + admin role-based routes
  auth.php        — Breeze auth routes (login, register, etc.)
  console.php     — Artisan commands
app/
  Models/         — User, UserRole, SchoolData, ClassRoom(classes), Student,
                    Transaction, Tier, Quest, StudentProgress,
                    StudentQuestCompletion, StudentQrToken, OfflineSyncKey
  Traits/HasRoles — role check helpers (hasRole, assignRole, etc.)
  Http/
    Controllers/        — ProfileController + per-role dirs
    Middleware/
      RoleMiddleware    — alias `role:admin,staff,wali_kelas`
      LastActivity      — updates last_activity_at on each request
      HandleInertiaRequests — shares auth.user + roles
    Requests/           — Form Requests (empty)
  Policies/             — TransactionPolicy, StudentPolicy, ClassPolicy, UserPolicy
  Services/             — (empty, ready for TransactionService, etc.)
  Observers/            — (empty, ready for TransactionObserver, etc.)
  Providers/            — AppServiceProvider with gates registered
resources/js/
  Layouts/AuthenticatedLayout — sidebar layout (navy + gold theme)
  Pages/                — Dashboard, Profile, Admin/*, Transactions/*
  Components/           — Breeze + custom components
```

## Role system
- **3 roles:** `admin`, `staff`, `wali_kelas` stored in separate `user_roles` table
- Middleware: `role:admin` or `role:admin,staff` on route groups
- Gates registered in `AppServiceProvider`: `manage-gamification`, `view-audit-logs`, `export-report`, `send-whatsapp`
- Policies for Transaction, Student, Class, User models

## Auth
- Web guard (`users` table) for Admin/Staff/WaliKelas — default Breeze login
- Student guard (`students` table) — for student portal (passwordless via QR)
- Throttle: 5 failed attempts via `RateLimiter`
- Idle timeout middleware: `LastActivity`

## Design system
- Navy (`#1e3a5f`) / Gold (`#d4a520`) color tokens in `tailwind.config.js`
- CSS utility classes: `.card`, `.stat-card`, `.btn-primary`, `.sidebar-link*`
- Dark mode via `class` strategy (`.dark` on `<html>`)
- Layout: fixed sidebar (64 width) + top header + content area

## Database
- **MySQL** via `DB_CONNECTION=mysql` in `.env`
- Database name: `tabungan`
- 18 tables total: users + user_roles + school_data + classes + students +
  transactions + tiers + quests + student_progress + student_quest_completions +
  student_qr_tokens + offline_sync_keys + (spatie:) roles/permissions/activity_log + jobs + cache + sessions
- Composite indexes on `transactions(student_id, transaction_date)` and `transactions(transaction_date, type)`

## WhatsApp
- **Driver:** `fonnte` via `config/whatsapp.php` (bisa diganti `log` untuk development)
- **Konfigurasi di `.env`:** `WHATSAPP_DRIVER=fonnte`, `WHATSAPP_API_KEY=...`, `WHATSAPP_API_URL=https://api.fonnte.com/send`
- **Trigger:** Otomatis saat admin/staff membuat transaksi (setor/tarik) via `TransactionService`
- **Penerima:** Nomor `phone` siswa, otomatis dikonversi `08xx` → `628xx`
- **Fallback:** Jika `phone` null, notifikasi dilewati (bukan pakai NIS lagi)
- **Gagal:** Jika API gagal, error tercatat di `storage/logs/laravel.log`
- **SSL:** System `withoutVerifying()` untuk kompatibilitas Windows (CA bundle)

## Real-time (WebSocket)
- **Stack:** Laravel Reverb (self-hosted) + Pusher protocol
- **Konfigurasi:** `config/broadcasting.php` + `config/reverb.php`
- **.env vars:** `BROADCAST_CONNECTION=reverb`, `REVERB_*` auto-generated by `reverb:install`
- **Client:** `resources/js/echo.js` configured with Reverb, imported client-only in `app.tsx`
- **Channel auth:** `routes/channels.php` — `student.{id}` private channel using `student` guard
- **Event:** `App\Events\StudentTransactionUpdated` broadcasts `transaction.updated` on `student.{id}`
- **Trigger:** Dispatched in `TransactionService` on create/update/delete
- **Frontend listener:** `Student/Dashboard.tsx` — `Echo.private('student.'+id).listen('.transaction.updated', ...)` calls `router.reload()`
- **Menjalankan:** `php artisan reverb:start` (dev) or supervisor (prod) — must run alongside queue worker

## Critical conventions
- Financial mutations MUST use `DB::transaction()` + `lockForUpdate()` on student row (PRD §5.5)
- Role stored in `user_roles` table (not on `users`), enforced by middleware + Policy
- Wali Kelas reports: total row only on last page of PDF
- All transaction create/update/delete logged to `activity_log` table
- `.env` is committed (`.gitignore` allows — local dev key included)
