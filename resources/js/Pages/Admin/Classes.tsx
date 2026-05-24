import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Pagination from '@/Components/Pagination';
import { Head, router, usePage } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

interface ClassRoom {
    id: number;
    name: string;
    wali_kelas_id: number | null;
    wali_kelas: { id: number; name: string } | null;
    students_count?: number;
}

interface WaliKelas {
    id: number;
    name: string;
}

export default function Classes({ classes, waliKelas }: { classes: { data: ClassRoom[]; links: any[]; current_page: number; last_page: number; from: number; to: number; total: number }; waliKelas: WaliKelas[] }) {
    const { errors, flash } = usePage().props as any;
    const [showForm, setShowForm] = useState(false);
    const [editing, setEditing] = useState<ClassRoom | null>(null);
    const [form, setForm] = useState({ name: '', wali_kelas_id: '' });
    const [processing, setProcessing] = useState(false);

    function openCreate() {
        setEditing(null);
        setForm({ name: '', wali_kelas_id: '' });
        setShowForm(true);
    }

    function openEdit(c: ClassRoom) {
        setEditing(c);
        setForm({ name: c.name, wali_kelas_id: c.wali_kelas_id?.toString() || '' });
        setShowForm(true);
    }

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        setProcessing(true);
        const data = { name: form.name, wali_kelas_id: form.wali_kelas_id || null };
        if (editing) {
            router.patch(route('admin.classes.update', editing.id), data, {
                onFinish: () => setProcessing(false),
            });
        } else {
            router.post(route('admin.classes.store'), data, {
                onFinish: () => setProcessing(false),
            });
        }
    }

    function handleDelete(c: ClassRoom) {
        if (confirm(`Hapus kelas "${c.name}"?`)) {
            setProcessing(true);
            router.delete(route('admin.classes.destroy', c.id), {
                onFinish: () => setProcessing(false),
            });
        }
    }

    return (
        <AuthenticatedLayout>
            <Head title="Kelas" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Kelas</h1>
                    <button onClick={openCreate} className="btn-primary">Tambah Kelas</button>
                </div>

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

                {showForm && (
                    <form onSubmit={handleSubmit} className="card max-w-lg space-y-4">
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                            {editing ? 'Edit Kelas' : 'Tambah Kelas'}
                        </h3>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Kelas</label>
                            <input
                                type="text"
                                value={form.name}
                                onChange={(e) => setForm({ ...form, name: e.target.value })}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                required
                            />
                            {errors?.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Wali Kelas</label>
                            <select
                                value={form.wali_kelas_id}
                                onChange={(e) => setForm({ ...form, wali_kelas_id: e.target.value })}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                            >
                                <option value="">-- Pilih Wali Kelas --</option>
                                {waliKelas.map((wk) => (
                                    <option key={wk.id} value={wk.id}>{wk.name}</option>
                                ))}
                            </select>
                        </div>

                        <div className="flex gap-2">
                            <button type="submit" className="btn-primary" disabled={processing}>
                                {processing ? 'Menyimpan...' : (editing ? 'Simpan' : 'Tambah')}
                            </button>
                            <button type="button" onClick={() => setShowForm(false)} className="btn-secondary">
                                Batal
                            </button>
                        </div>
                    </form>
                )}

                <div className="card overflow-x-auto p-0">
                    <table className="w-full text-left text-sm">
                        <thead className="border-b bg-gray-50 dark:border-gray-700 dark:bg-navy-800">
                            <tr>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Nama Kelas</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Wali Kelas</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Jumlah Siswa</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y dark:divide-gray-700">
                            {classes.data.length === 0 ? (
                                <tr><td colSpan={4} className="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada kelas.</td></tr>
                            ) : classes.data.map((c) => (
                                <tr key={c.id} className="hover:bg-gray-50 dark:hover:bg-navy-700/50">
                                    <td className="px-4 py-3 text-gray-900 dark:text-white">{c.name}</td>
                                    <td className="px-4 py-3 text-gray-600 dark:text-gray-400">
                                        {c.wali_kelas?.name || '-'}
                                    </td>
                                    <td className="px-4 py-3 text-gray-600 dark:text-gray-400">{c.students_count || 0}</td>
                                    <td className="px-4 py-3">
                                        <div className="flex gap-2">
                                            <button onClick={() => openEdit(c)} className="text-sm text-gold-600 hover:text-gold-700 dark:text-gold-400">
                                                Edit
                                            </button>
                                            <button onClick={() => handleDelete(c)} className="text-sm text-red-600 hover:text-red-700 dark:text-red-400">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    <Pagination {...classes} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
