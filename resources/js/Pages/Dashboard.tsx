import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

interface MonthlyTrendItem {
    month: string;
    label: string;
    income: number;
    expense: number;
}

interface Transaction {
    id: number;
    type: string;
    amount: number;
    transaction_date: string;
    student: { id: number; nis: string; name: string } | null;
    created_by_user: { id: number; name: string } | null;
}

interface DashboardProps {
    totalStudents: number;
    totalTransactions: number;
    totalBalance: number;
    todayTransactions: number;
    monthlyTrend: MonthlyTrendItem[];
    recentTransactions: Transaction[];
}

export default function Dashboard({
    totalStudents = 0,
    totalTransactions = 0,
    totalBalance = 0,
    todayTransactions = 0,
    monthlyTrend = [],
    recentTransactions = [],
}: DashboardProps) {
    const formatRp = (amount: number) =>
        new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);

    const BAR_HEIGHT = 180;
    const allIncome = monthlyTrend.map(d => d.income);
    const allExpense = monthlyTrend.map(d => d.expense);
    const maxVal = Math.max(...allIncome, ...allExpense, 1);
    const allZero = maxVal === 1 && allIncome.every(v => v === 0) && allExpense.every(v => v === 0);

    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />

            <div className="space-y-6">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>

                {/* Quick Actions */}
                <div className="flex flex-wrap gap-3">
                    <button onClick={() => router.visit(route('transactions.index'))} className="btn-primary text-sm">
                        + Transaksi Baru
                    </button>
                    <button onClick={() => router.visit(route('reports'))} className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-navy-700 dark:text-gray-300 dark:hover:bg-navy-600">
                        Lihat Laporan
                    </button>
                    <button onClick={() => router.visit(route('admin.students'))} className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-navy-700 dark:text-gray-300 dark:hover:bg-navy-600">
                        Kelola Siswa
                    </button>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="stat-card">
                        <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300">
                            <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                            </svg>
                        </div>
                        <div>
                            <p className="text-sm text-gray-500 dark:text-gray-400">Total Siswa</p>
                            <p className="text-2xl font-bold text-gray-900 dark:text-white">{totalStudents}</p>
                        </div>
                    </div>

                    <div className="stat-card">
                        <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900 dark:text-emerald-300">
                            <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div>
                            <p className="text-sm text-gray-500 dark:text-gray-400">Total Saldo</p>
                            <p className="text-2xl font-bold text-gray-900 dark:text-white">{formatRp(totalBalance)}</p>
                        </div>
                    </div>

                    <div className="stat-card">
                        <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900 dark:text-amber-300">
                            <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                        </div>
                        <div>
                            <p className="text-sm text-gray-500 dark:text-gray-400">Total Transaksi</p>
                            <p className="text-2xl font-bold text-gray-900 dark:text-white">{totalTransactions}</p>
                        </div>
                    </div>

                    <div className="stat-card">
                        <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-300">
                            <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div>
                            <p className="text-sm text-gray-500 dark:text-gray-400">Transaksi Hari Ini</p>
                            <p className="text-2xl font-bold text-gray-900 dark:text-white">{todayTransactions}</p>
                        </div>
                    </div>
                </div>

                {/* Monthly Income & Expense Chart */}
                <div className="card">
                    <h2 className="mb-1 text-lg font-semibold text-gray-900 dark:text-white">
                        Grafik Pemasukan & Pengeluaran Per Bulan
                    </h2>
                    <p className="mb-4 text-xs text-gray-400 dark:text-gray-500">
                        Tahun Ajaran {monthlyTrend[0]?.month?.slice(0, 4) ?? '—'}/{monthlyTrend[11]?.month?.slice(0, 4) ?? '—'}
                    </p>

                    <div className="flex items-center gap-4 mb-4 text-xs text-gray-500">
                        <span className="flex items-center gap-1">
                            <span className="inline-block h-3 w-3 rounded bg-emerald-500" />
                            Pemasukan
                        </span>
                        <span className="flex items-center gap-1">
                            <span className="inline-block h-3 w-3 rounded bg-red-500" />
                            Pengeluaran
                        </span>
                    </div>

                    {monthlyTrend.length > 0 && !allZero ? (
                        <div className="overflow-x-auto">
                            <div className="flex h-[200px] items-end gap-3 min-w-[400px] px-2">
                                {monthlyTrend.map((d, i) => {
                                    const incomePx = Math.max(Math.round((d.income / maxVal) * (BAR_HEIGHT - 20)), d.income > 0 ? 4 : 0);
                                    const expensePx = Math.max(Math.round((d.expense / maxVal) * (BAR_HEIGHT - 20)), d.expense > 0 ? 4 : 0);
                                    return (
                                        <div key={i} className="group relative flex flex-col items-center flex-1" style={{ height: `${BAR_HEIGHT}px` }}>
                                            <div className="flex w-full h-full items-end justify-center gap-[3px]">
                                                <div className="relative flex w-[45%] justify-center">
                                                    <div
                                                        className="w-full rounded-t bg-emerald-500 transition-all hover:bg-emerald-400"
                                                        style={{ height: `${incomePx}px` }}
                                                    >
                                                        <div className="invisible absolute bottom-full left-1/2 z-10 mb-1 -translate-x-1/2 whitespace-nowrap rounded bg-gray-800 px-2 py-1 text-xs text-white group-hover:visible">
                                                            Pemasukan: {formatRp(d.income)}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div className="relative flex w-[45%] justify-center">
                                                    <div
                                                        className="w-full rounded-t bg-red-500 transition-all hover:bg-red-400"
                                                        style={{ height: `${expensePx}px` }}
                                                    >
                                                        <div className="invisible absolute bottom-full left-1/2 z-10 mb-1 -translate-x-1/2 whitespace-nowrap rounded bg-gray-800 px-2 py-1 text-xs text-white group-hover:visible">
                                                            Pengeluaran: {formatRp(d.expense)}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <span className="mt-1 text-[10px] text-gray-500 dark:text-gray-400">{d.label}</span>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    ) : (
                        <p className="py-8 text-center text-sm text-gray-400 dark:text-gray-500">
                            Belum ada data transaksi periode ini.
                        </p>
                    )}
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Recent Transactions */}
                    <div className="card">
                        <div className="mb-3 flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-gray-900 dark:text-white">Transaksi Terbaru</h2>
                            <button onClick={() => router.visit(route('transactions.index'))} className="text-sm text-gold-600 hover:text-gold-700 dark:text-gold-400">
                                Lihat Semua
                            </button>
                        </div>
                        {recentTransactions.length === 0 ? (
                            <p className="py-6 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada transaksi.</p>
                        ) : (
                            <div className="divide-y dark:divide-gray-700">
                                {recentTransactions.map((t) => (
                                    <div key={t.id} className="flex items-center justify-between py-2.5">
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate text-sm font-medium text-gray-900 dark:text-white">{t.student?.name}</p>
                                            <p className="text-xs text-gray-500 dark:text-gray-400">
                                                {new Date(t.transaction_date).toLocaleDateString('id-ID')} &middot; {t.created_by_user?.name}
                                            </p>
                                        </div>
                                        <span className={`ml-3 shrink-0 text-sm font-semibold ${t.type === 'setor' ? 'text-emerald-600' : 'text-red-600'}`}>
                                            {t.type === 'setor' ? '+' : '-'}{formatRp(t.amount)}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Quick Info */}
                    <div className="card">
                        <h2 className="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                            Selamat Datang
                        </h2>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            Aplikasi manajemen tabungan siswa SMK Globin. Gunakan menu samping untuk navigasi.
                        </p>
                        <div className="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div className="rounded-lg bg-gray-50 p-3 dark:bg-navy-700">
                                <p className="text-gray-500 dark:text-gray-400">Rata-rata Saldo</p>
                                <p className="text-lg font-bold text-gray-900 dark:text-white">
                                    {totalStudents > 0 ? formatRp(Math.round(totalBalance / totalStudents)) : 'Rp0'}
                                </p>
                            </div>
                            <div className="rounded-lg bg-gray-50 p-3 dark:bg-navy-700">
                                <p className="text-gray-500 dark:text-gray-400">Transaksi / Hari</p>
                                <p className="text-lg font-bold text-gray-900 dark:text-white">{todayTransactions}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
