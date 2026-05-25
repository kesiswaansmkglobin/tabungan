import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Pagination from '@/Components/Pagination';
import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

interface Student {
    id: number;
    nis: string;
    name: string;
    phone: string | null;
    balance: number;
    class: { id: number; name: string } | null;
}

export default function Students({ students, filters }: {
    students: { data: Student[]; links: any[]; current_page: number; last_page: number; from: number; to: number; total: number };
    filters?: { search?: string; sort?: string; dir?: string };
}) {
    const { flash } = usePage().props as any;
    const [searchTimeout, setSearchTimeout] = useState<any>(null);

    const activeSort = filters?.sort || 'name';
    const activeDir = filters?.dir || 'asc';

    function handleSearch(value: string) {
        if (searchTimeout) clearTimeout(searchTimeout);
        setSearchTimeout(setTimeout(() => {
            router.get(route('wali-kelas.students'), { search: value || undefined, sort: activeSort, dir: activeDir }, { preserveState: true, replace: true });
        }, 300));
    }

    function handleSort(field: string) {
        const newDir = activeSort === field && activeDir === 'asc' ? 'desc' : 'asc';
        const params: any = { sort: field, dir: newDir };
        if (filters?.search) params.search = filters.search;
        router.get(route('wali-kelas.students'), params, { preserveState: true, replace: true });
    }

    function sortIcon(field: string) {
        if (activeSort !== field) return '↕';
        return activeDir === 'asc' ? '↑' : '↓';
    }

    const formatRp = (amount: number) =>
        new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);

    return (
        <AuthenticatedLayout>
            <Head title="Siswa Saya" />

            <div className="space-y-6">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Daftar Siswa Perwalian</h1>

                {flash?.success && (
                    <div className="rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                        {flash.success}
                    </div>
                )}

                {flash?.error && (
                    <div className="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">
                        {flash.error}
                    </div>
                )}

                <div className="flex items-center gap-3">
                    <div className="relative max-w-xs flex-1">
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
                    <span className="text-sm text-gray-500">{students.total} siswa</span>
                </div>

                <div className="card overflow-x-auto p-0">
                    <table className="w-full text-left text-sm">
                        <thead className="border-b bg-gray-50 dark:border-gray-700 dark:bg-navy-800">
                            <tr>
                                <th className="cursor-pointer px-4 py-3 font-medium text-gray-700 hover:text-gold-600 dark:text-gray-300 dark:hover:text-gold-400" onClick={() => handleSort('nis')}>
                                    NIS <span className="text-xs">{sortIcon('nis')}</span>
                                </th>
                                <th className="cursor-pointer px-4 py-3 font-medium text-gray-700 hover:text-gold-600 dark:text-gray-300 dark:hover:text-gold-400" onClick={() => handleSort('name')}>
                                    Nama <span className="text-xs">{sortIcon('name')}</span>
                                </th>
                                <th className="cursor-pointer px-4 py-3 font-medium text-gray-700 hover:text-gold-600 dark:text-gray-300 dark:hover:text-gold-400" onClick={() => handleSort('class_id')}>
                                    Kelas <span className="text-xs">{sortIcon('class_id')}</span>
                                </th>
                                <th className="cursor-pointer px-4 py-3 font-medium text-gray-700 hover:text-gold-600 dark:text-gray-300 dark:hover:text-gold-400" onClick={() => handleSort('balance')}>
                                    Saldo <span className="text-xs">{sortIcon('balance')}</span>
                                </th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">No. HP</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y dark:divide-gray-700">
                            {students.data.map((s) => (
                                <tr key={s.id} className="hover:bg-gray-50 dark:hover:bg-navy-700/50">
                                    <td className="px-4 py-3 font-mono text-gray-900 dark:text-white">{s.nis}</td>
                                    <td className="px-4 py-3 text-gray-900 dark:text-white">{s.name}</td>
                                    <td className="px-4 py-3 text-gray-600 dark:text-gray-400">{s.class?.name || '-'}</td>
                                    <td className="px-4 py-3 font-semibold text-gray-900 dark:text-white">{formatRp(s.balance)}</td>
                                    <td className="px-4 py-3 text-gray-600 dark:text-gray-400">{s.phone || '-'}</td>
                                    <td className="px-4 py-3">
                                        <a
                                            href={route('reports.buku-tabungan', s.id)}
                                            className="text-sm text-navy-600 hover:text-navy-700 dark:text-navy-300"
                                            target="_blank"
                                        >
                                            Cetak Buku Tabungan
                                        </a>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    <Pagination {...students} />
                </div>

                {students.data.length === 0 && (
                    <div className="card text-center text-gray-500 dark:text-gray-400">
                        Belum ada siswa di kelas perwalian Anda.
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
