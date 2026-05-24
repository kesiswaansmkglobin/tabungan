import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Pagination from '@/Components/Pagination';
import { Head, router, usePage } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

interface Transaction {
    id: number;
    student_id: number;
    type: string;
    amount: number;
    balance_after: number;
    transaction_date: string;
    note: string | null;
    student: { id: number; nis: string; name: string; class?: { id: number; name: string } };
    created_by_user: { id: number; name: string };
}

interface StudentOption {
    id: number;
    nis: string;
    name: string;
}

interface ClassOption {
    id: number;
    name: string;
}

export default function History({ transactions, students, classes, filters }: {
    transactions: { data: Transaction[]; links: any[]; current_page: number; last_page: number; from: number; to: number; total: number };
    students: StudentOption[];
    classes: ClassOption[];
    filters: Record<string, string>;
}) {
    const { flash } = usePage().props as any;
    const [filter, setFilter] = useState({
        type: filters.type || '',
        student_id: filters.student_id || '',
        class_id: filters.class_id || '',
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
    });

    function applyFilters(e: FormEvent) {
        e.preventDefault();
        router.get(route('history'), filter, { preserveState: true });
    }

    function resetFilters() {
        setFilter({ type: '', student_id: '', class_id: '', date_from: '', date_to: '' });
        router.get(route('history'));
    }

    const formatRp = (amount: number) =>
        new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);

    return (
        <AuthenticatedLayout>
            <Head title="Riwayat" />

            <div className="space-y-6">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Riwayat Transaksi</h1>

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

                <form onSubmit={applyFilters} className="card grid grid-cols-1 gap-3 sm:grid-cols-5">
                    <div>
                        <label className="block text-xs font-medium text-gray-600 dark:text-gray-400">Jenis</label>
                        <select
                            value={filter.type}
                            onChange={(e) => setFilter({ ...filter, type: e.target.value })}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                        >
                            <option value="">Semua</option>
                            <option value="setor">Setoran</option>
                            <option value="tarik">Penarikan</option>
                        </select>
                    </div>

                    <div>
                        <label className="block text-xs font-medium text-gray-600 dark:text-gray-400">Kelas</label>
                        <select
                            value={filter.class_id}
                            onChange={(e) => setFilter({ ...filter, class_id: e.target.value })}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                        >
                            <option value="">Semua Kelas</option>
                            {classes.map((c) => (
                                <option key={c.id} value={c.id}>{c.name}</option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="block text-xs font-medium text-gray-600 dark:text-gray-400">Siswa</label>
                        <select
                            value={filter.student_id}
                            onChange={(e) => setFilter({ ...filter, student_id: e.target.value })}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                        >
                            <option value="">Semua Siswa</option>
                            {students.map((s) => (
                                <option key={s.id} value={s.id}>{s.name} ({s.nis})</option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="block text-xs font-medium text-gray-600 dark:text-gray-400">Dari Tanggal</label>
                        <input
                            type="date"
                            value={filter.date_from}
                            onChange={(e) => setFilter({ ...filter, date_from: e.target.value })}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                        />
                    </div>

                    <div>
                        <label className="block text-xs font-medium text-gray-600 dark:text-gray-400">Sampai Tanggal</label>
                        <input
                            type="date"
                            value={filter.date_to}
                            onChange={(e) => setFilter({ ...filter, date_to: e.target.value })}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                        />
                    </div>

                    <div className="col-span-full flex gap-2">
                        <button type="submit" className="btn-primary">Filter</button>
                        <button type="button" onClick={resetFilters} className="btn-secondary">Reset</button>
                        <div className="ml-auto flex gap-2">
                            <button type="button" onClick={() => {
                                const params = new URLSearchParams();
                                Object.entries(filter).forEach(([k, v]) => { if (v) params.append(k, v); });
                                window.open(route('history.excel') + '?' + params.toString(), '_blank');
                            }} className="btn-secondary">
                                <svg className="mr-1 inline h-4 w-4" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                Excel
                            </button>
                            <button type="button" onClick={() => {
                                const params = new URLSearchParams();
                                Object.entries(filter).forEach(([k, v]) => { if (v) params.append(k, v); });
                                window.open(route('history.pdf') + '?' + params.toString(), '_blank');
                            }} className="btn-secondary">
                                <svg className="mr-1 inline h-4 w-4" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                PDF
                            </button>
                        </div>
                    </div>
                </form>

                <div className="card overflow-x-auto p-0">
                    <table className="w-full text-left text-sm">
                        <thead className="border-b bg-gray-50 dark:border-gray-700 dark:bg-navy-800">
                            <tr>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Tanggal</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Siswa</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Kelas</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Jenis</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Jumlah</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Saldo Akhir</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Petugas</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y dark:divide-gray-700">
                            {transactions.data.map((t) => (
                                <tr key={t.id} className="hover:bg-gray-50 dark:hover:bg-navy-700/50">
                                    <td className="whitespace-nowrap px-4 py-3 text-gray-900 dark:text-white">
                                        {new Date(t.transaction_date).toLocaleDateString('id-ID')}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="text-gray-900 dark:text-white">{t.student?.name}</div>
                                        <div className="text-xs text-gray-500">{t.student?.nis}</div>
                                    </td>
                                    <td className="px-4 py-3 text-gray-600 dark:text-gray-400">
                                        {t.student?.class?.name || '-'}
                                    </td>
                                    <td className="px-4 py-3">
                                        <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${
                                            t.type === 'setor'
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                                                : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                                        }`}>
                                            {t.type === 'setor' ? 'Setoran' : 'Penarikan'}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                        {formatRp(t.amount)}
                                    </td>
                                    <td className="px-4 py-3 text-gray-600 dark:text-gray-400">
                                        {formatRp(t.balance_after)}
                                    </td>
                                    <td className="px-4 py-3 text-gray-500 dark:text-gray-400">
                                        {t.created_by_user?.name}
                                    </td>
                                    <td className="max-w-xs truncate px-4 py-3 text-gray-500 dark:text-gray-400">
                                        {t.note || '-'}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    <Pagination {...transactions} />
                </div>

                {transactions.data.length === 0 && (
                    <div className="card text-center text-gray-500 dark:text-gray-400">
                        Belum ada transaksi.
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
