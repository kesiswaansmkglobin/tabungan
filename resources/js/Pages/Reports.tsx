import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

interface Student {
    id: number;
    nis: string;
    name: string;
    class: { id: number; name: string } | null;
    balance: number;
    transactions_count: number;
}

interface ClassOption {
    id: number;
    name: string;
}

export default function Reports({ classes, students, filters }: {
    classes: ClassOption[];
    students: Student[];
    filters: Record<string, string>;
}) {
    const { flash } = usePage().props as any;
    const [classId, setClassId] = useState(filters.class_id || '');

    function applyFilter() {
        router.get(route('reports'), { class_id: classId }, { preserveState: true });
    }

    function exportExcel() {
        const params = new URLSearchParams();
        if (classId) params.append('class_id', classId);
        window.open(route('reports.excel') + '?' + params.toString(), '_blank');
    }

    function exportPdf() {
        const params = new URLSearchParams();
        if (classId) params.append('class_id', classId);
        window.open(route('reports.pdf') + '?' + params.toString(), '_blank');
    }

    const formatRp = (amount: number) =>
        new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);

    const totalSaldo = students.reduce((sum, s) => sum + s.balance, 0);

    return (
        <AuthenticatedLayout>
            <Head title="Laporan" />

            <div className="space-y-6">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Rekap Saldo Siswa</h1>

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

                <div className="card flex flex-wrap items-end gap-3">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Filter Kelas</label>
                        <select
                            value={classId}
                            onChange={(e) => setClassId(e.target.value)}
                            className="mt-1 block w-48 rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                        >
                            <option value="">Semua Kelas</option>
                            {classes.map((c) => (
                                <option key={c.id} value={c.id}>{c.name}</option>
                            ))}
                        </select>
                    </div>
                    <button onClick={applyFilter} className="btn-primary">Tampilkan</button>
                    <button onClick={() => { setClassId(''); router.get(route('reports')); }} className="btn-secondary">Reset</button>
                    <div className="ml-auto flex flex-wrap gap-2">
                        <button onClick={exportExcel} className="btn-secondary">
                            <svg className="mr-2 inline h-4 w-4" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Download Excel
                        </button>
                        <button onClick={exportPdf} className="btn-secondary">
                            <svg className="mr-2 inline h-4 w-4" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Download PDF
                        </button>
                    </div>
                </div>

                <div className="card overflow-x-auto p-0">
                    <table className="w-full text-left text-sm">
                        <thead className="border-b bg-gray-50 dark:border-gray-700 dark:bg-navy-800">
                            <tr>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">No</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">NIS</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Nama Siswa</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Kelas</th>
                                <th className="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300">Saldo Akhir</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y dark:divide-gray-700">
                            {students.map((s, i) => (
                                <tr key={s.id} className="hover:bg-gray-50 dark:hover:bg-navy-700/50">
                                    <td className="px-4 py-3 text-gray-500 dark:text-gray-400">{i + 1}</td>
                                    <td className="px-4 py-3 text-gray-900 dark:text-white">{s.nis}</td>
                                    <td className="px-4 py-3 text-gray-900 dark:text-white">{s.name}</td>
                                    <td className="px-4 py-3 text-gray-600 dark:text-gray-400">{s.class?.name || '-'}</td>
                                    <td className="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                                        {formatRp(s.balance)}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    {students.length === 0 && (
                        <div className="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            Belum ada data siswa.
                        </div>
                    )}
                </div>

                {students.length > 0 && (
                    <div className="card text-right text-sm font-semibold text-gray-900 dark:text-white">
                        Total Saldo Keseluruhan: {formatRp(totalSaldo)}
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
