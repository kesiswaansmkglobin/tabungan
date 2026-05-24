import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Pagination from '@/Components/Pagination';
import { Head, router, usePage } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

interface LogEntry {
    id: number;
    log_name: string;
    description: string;
    event: string | null;
    causer: { id: number; name: string } | null;
    properties: Record<string, any> | null;
    created_at: string;
}

export default function AuditLog({ logs, logNames, events, filters }: {
    logs: { data: LogEntry[]; links: any[]; current_page: number; last_page: number; from: number; to: number; total: number };
    logNames: string[];
    events: string[];
    filters: Record<string, string>;
}) {
    const { flash } = usePage().props as any;
    const [filter, setFilter] = useState({
        log_name: filters.log_name || '',
        event: filters.event || '',
        description: filters.description || '',
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
    });

    function applyFilters(e: FormEvent) {
        e.preventDefault();
        router.get(route('admin.audit'), filter, { preserveState: true });
    }

    function resetFilters() {
        setFilter({ log_name: '', event: '', description: '', date_from: '', date_to: '' });
        router.get(route('admin.audit'));
    }

    function formatDate(dateStr: string) {
        return new Date(dateStr).toLocaleString('id-ID');
    }

    return (
        <AuthenticatedLayout>
            <Head title="Audit Log" />

            <div className="space-y-6">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Audit Log</h1>

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
                        <label className="block text-xs font-medium text-gray-600 dark:text-gray-400">Log Name</label>
                        <select
                            value={filter.log_name}
                            onChange={(e) => setFilter({ ...filter, log_name: e.target.value })}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                        >
                            <option value="">Semua</option>
                            {logNames.map((name) => (
                                <option key={name} value={name}>{name}</option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="block text-xs font-medium text-gray-600 dark:text-gray-400">Event</label>
                        <select
                            value={filter.event}
                            onChange={(e) => setFilter({ ...filter, event: e.target.value })}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                        >
                            <option value="">Semua</option>
                            {events.map((ev) => (
                                <option key={ev} value={ev}>{ev}</option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="block text-xs font-medium text-gray-600 dark:text-gray-400">Cari Deskripsi</label>
                        <input
                            type="text"
                            value={filter.description}
                            onChange={(e) => setFilter({ ...filter, description: e.target.value })}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                        />
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
                        <div className="mt-1 flex gap-1">
                            <input
                                type="date"
                                value={filter.date_to}
                                onChange={(e) => setFilter({ ...filter, date_to: e.target.value })}
                                className="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                            />
                        </div>
                    </div>

                    <div className="col-span-full flex gap-2">
                        <button type="submit" className="btn-primary">Filter</button>
                        <button type="button" onClick={resetFilters} className="btn-secondary">Reset</button>
                    </div>
                </form>

                <div className="card overflow-x-auto p-0">
                    <table className="w-full text-left text-sm">
                        <thead className="border-b bg-gray-50 dark:border-gray-700 dark:bg-navy-800">
                            <tr>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Waktu</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Log</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Event</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Deskripsi</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Pelaku</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y dark:divide-gray-700">
                            {logs.data.map((log) => (
                                <tr key={log.id} className="hover:bg-gray-50 dark:hover:bg-navy-700/50">
                                    <td className="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-400">
                                        {formatDate(log.created_at)}
                                    </td>
                                    <td className="px-4 py-3">
                                        <span className="inline-flex rounded-full bg-navy-100 px-2 py-0.5 text-xs font-medium text-navy-700 dark:bg-navy-700 dark:text-navy-200">
                                            {log.log_name}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3">
                                        {log.event ? (
                                            <span className="inline-flex rounded-full bg-gold-100 px-2 py-0.5 text-xs font-medium text-gold-700 dark:bg-gold-900/30 dark:text-gold-300">
                                                {log.event}
                                            </span>
                                        ) : '-'}
                                    </td>
                                    <td className="px-4 py-3 text-gray-900 dark:text-white">{log.description}</td>
                                    <td className="px-4 py-3 text-gray-600 dark:text-gray-400">
                                        {log.causer?.name || 'System'}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    <Pagination {...logs} />
                </div>

                {logs.data.length === 0 && (
                    <div className="card text-center text-gray-500 dark:text-gray-400">
                        Belum ada log aktivitas.
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
