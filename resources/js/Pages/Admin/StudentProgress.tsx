import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Pagination from '@/Components/Pagination';
import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { PageProps, Flash } from '@/types';

interface Student {
    id: number;
    nis: string;
    name: string;
    balance: number;
    class: { id: number; name: string } | null;
    progress: {
        xp: number;
        tier: { id: number; name: string; icon: string | null; color: string | null } | null;
        last_login_at: string | null;
    } | null;
    quests_completed: number;
}

interface ClassOption {
    id: number;
    name: string;
}

interface TierOption {
    id: number;
    name: string;
    icon: string | null;
    color: string | null;
}

const formatRp = (amount: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

export default function StudentProgress({ students, classes, tiers, filters }: {
    students: { data: Student[]; links: any[]; current_page: number; last_page: number; from: number; to: number; total: number };
    classes: ClassOption[];
    tiers: TierOption[];
    filters?: { search?: string; class_id?: string; tier_id?: string };
}) {
    const { flash } = usePage<PageProps<{ flash: Flash }>>().props;
    const [searchTimeout, setSearchTimeout] = useState<any>(null);

    function handleSearch(value: string) {
        if (searchTimeout) clearTimeout(searchTimeout);
        setSearchTimeout(setTimeout(() => {
            const params: any = { search: value || undefined };
            if (filters?.class_id) params.class_id = filters.class_id;
            if (filters?.tier_id) params.tier_id = filters.tier_id;
            router.get(route('admin.student-progress'), params, { preserveState: true, replace: true });
        }, 300));
    }

    function handleFilter(field: string, value: string) {
        const params: any = {};
        if (filters?.search) params.search = filters.search;
        if (field === 'class_id' && value) params.class_id = value;
        if (field === 'tier_id' && value) params.tier_id = value;
        router.get(route('admin.student-progress'), params, { preserveState: true, replace: true });
    }

    function clearFilters() {
        router.get(route('admin.student-progress'), {}, { preserveState: true, replace: true });
    }

    const hasFilters = filters?.search || filters?.class_id || filters?.tier_id;

    return (
        <AuthenticatedLayout>
            <Head title="Progress Siswa" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Progress Gamifikasi Siswa</h1>
                </div>

                {flash?.success && (
                    <div className="rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                        {flash.success}
                    </div>
                )}

                <div className="flex flex-wrap items-center gap-3">
                    <div className="relative flex-1 max-w-xs">
                        <svg className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input
                            type="text"
                            defaultValue={filters?.search || ''}
                            onChange={(e) => handleSearch(e.target.value)}
                            placeholder="Cari NIS atau nama..."
                            className="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-3 text-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                        />
                    </div>

                    <select
                        value={filters?.class_id || ''}
                        onChange={(e) => handleFilter('class_id', e.target.value)}
                        className="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                    >
                        <option value="">Semua Kelas</option>
                        {classes.map((c) => (
                            <option key={c.id} value={c.id}>{c.name}</option>
                        ))}
                    </select>

                    <select
                        value={filters?.tier_id || ''}
                        onChange={(e) => handleFilter('tier_id', e.target.value)}
                        className="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                    >
                        <option value="">Semua Tier</option>
                        {tiers.map((t) => (
                            <option key={t.id} value={t.id}>{t.icon} {t.name}</option>
                        ))}
                    </select>

                    {hasFilters && (
                        <button onClick={clearFilters} className="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            Reset Filter
                        </button>
                    )}

                    <span className="text-sm text-gray-500">{students.total} siswa</span>
                </div>

                <div className="card overflow-x-auto p-0">
                    <table className="w-full text-left text-sm">
                        <thead className="border-b bg-gray-50 dark:border-gray-700 dark:bg-navy-800">
                            <tr>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">NIS</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Nama</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Kelas</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Saldo</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Tier</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">XP</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Quest Selesai</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Terakhir Login</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y dark:divide-gray-700">
                            {students.data.length === 0 ? (
                                <tr><td colSpan={8} className="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada data siswa.</td></tr>
                            ) : students.data.map((s) => (
                                <tr key={s.id} className="hover:bg-gray-50 dark:hover:bg-navy-700/50">
                                    <td className="px-4 py-3 font-mono text-gray-900 dark:text-white">{s.nis}</td>
                                    <td className="px-4 py-3 text-gray-900 dark:text-white">{s.name}</td>
                                    <td className="px-4 py-3 text-gray-600 dark:text-gray-400">{s.class?.name || '-'}</td>
                                    <td className="px-4 py-3 font-semibold text-gray-900 dark:text-white">{formatRp(s.balance)}</td>
                                    <td className="px-4 py-3">
                                        {s.progress?.tier ? (
                                            <span className="inline-flex items-center gap-1 text-gray-900 dark:text-white">
                                                {s.progress.tier.icon && <span>{s.progress.tier.icon}</span>}
                                                <span>{s.progress.tier.name}</span>
                                            </span>
                                        ) : (
                                            <span className="text-gray-400">-</span>
                                        )}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex items-center gap-2">
                                            <span className="font-medium text-gray-900 dark:text-white">{s.progress?.xp ?? 0}</span>
                                            <div className="h-2 w-16 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-600">
                                                <div
                                                    className="h-full rounded-full bg-gold-500 transition-all"
                                                    style={{ width: `${Math.min(100, (s.progress?.xp ?? 0) / 10)}%` }}
                                                />
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-4 py-3">
                                        {s.quests_completed > 0 ? (
                                            <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                                {s.quests_completed} misi
                                            </span>
                                        ) : (
                                            <span className="text-gray-400">0</span>
                                        )}
                                    </td>
                                    <td className="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                        {formatDate(s.progress?.last_login_at ?? null)}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    <Pagination {...students} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
