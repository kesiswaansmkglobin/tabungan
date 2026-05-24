import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Pagination from '@/Components/Pagination';
import { Head, router, usePage } from '@inertiajs/react';
import { FormEvent, useRef, useState } from 'react';

interface Student {
    id: number;
    nis: string;
    name: string;
    phone: string | null;
    class_id: number;
    balance: number;
    class: { id: number; name: string } | null;
}

interface ClassOption {
    id: number;
    name: string;
}

export default function Students({ students, classes, filters, canManage = true }: { students: { data: Student[]; links: any[]; current_page: number; last_page: number; from: number; to: number; total: number }; classes: ClassOption[]; filters?: { search?: string; sort?: string; dir?: string }; canManage?: boolean }) {
    const { errors, flash } = usePage().props as any;
    const [showForm, setShowForm] = useState(false);
    const [editing, setEditing] = useState<Student | null>(null);
    const [form, setForm] = useState({ nis: '', name: '', phone: '', class_id: '' });
    const [qrStudent, setQrStudent] = useState<Student | null>(null);
    const [showImport, setShowImport] = useState(false);
    const fileRef = useRef<HTMLInputElement>(null);
    const [searchTimeout, setSearchTimeout] = useState<any>(null);
    const [processing, setProcessing] = useState(false);

    const activeSort = filters?.sort || 'created_at';
    const activeDir = filters?.dir || 'desc';

    function openCreate() {
        setEditing(null);
        setForm({ nis: '', name: '', phone: '', class_id: '' });
        setShowForm(true);
    }

    function openEdit(s: Student) {
        setEditing(s);
        setForm({ nis: s.nis, name: s.name, phone: s.phone ?? '', class_id: s.class_id.toString() });
        setShowForm(true);
    }

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        setProcessing(true);
        const data = { nis: form.nis, name: form.name, phone: form.phone || null, class_id: parseInt(form.class_id) };
        if (editing) {
            router.patch(route('admin.students.update', editing.id), data, {
                onFinish: () => setProcessing(false),
            });
        } else {
            router.post(route('admin.students.store'), data, {
                onFinish: () => setProcessing(false),
            });
        }
    }

    function handleDelete(s: Student) {
        if (confirm(`Hapus siswa "${s.name}"?`)) {
            setProcessing(true);
            router.delete(route('admin.students.destroy', s.id), {
                onFinish: () => setProcessing(false),
            });
        }
    }

    function handleImport(e: FormEvent) {
        e.preventDefault();
        const file = fileRef.current?.files?.[0];
        if (!file) return;
        setProcessing(true);
        const data = new FormData();
        data.append('file', file);
        router.post(route('admin.students.import'), data, {
            forceFormData: true,
            onSuccess: () => {
                setShowImport(false);
                if (fileRef.current) fileRef.current.value = '';
            },
            onFinish: () => setProcessing(false),
        });
    }

    function handleSearch(value: string) {
        if (searchTimeout) clearTimeout(searchTimeout);
        setSearchTimeout(setTimeout(() => {
            router.get(route('admin.students'), { search: value || undefined, sort: activeSort, dir: activeDir }, { preserveState: true, replace: true });
        }, 300));
    }

    function handleSort(field: string) {
        const newDir = activeSort === field && activeDir === 'asc' ? 'desc' : 'asc';
        const params: any = { sort: field, dir: newDir };
        if (filters?.search) params.search = filters.search;
        router.get(route('admin.students'), params, { preserveState: true, replace: true });
    }

    function sortIcon(field: string) {
        if (activeSort !== field) return '↕';
        return activeDir === 'asc' ? '↑' : '↓';
    }

    const formatRp = (amount: number) =>
        new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);

    return (
        <AuthenticatedLayout>
            <Head title="Siswa" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">{canManage ? 'Manajemen Siswa' : 'Data Siswa'}</h1>
                    {canManage && (
                        <div className="flex gap-2">
                            <button onClick={() => setShowImport(true)} className="btn-secondary">Impor Excel</button>
                            <button onClick={openCreate} className="btn-primary">Tambah Siswa</button>
                        </div>
                    )}
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
                            {editing ? 'Edit Siswa' : 'Tambah Siswa'}
                        </h3>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">NIS</label>
                            <input
                                type="text"
                                value={form.nis}
                                onChange={(e) => setForm({ ...form, nis: e.target.value })}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                required
                            />
                            {errors?.nis && <p className="mt-1 text-sm text-red-600">{errors.nis}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap</label>
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
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">No. HP <span className="text-gray-400 font-normal">(opsional, untuk WhatsApp)</span></label>
                            <input
                                type="text"
                                value={form.phone}
                                onChange={(e) => setForm({ ...form, phone: e.target.value })}
                                placeholder="08xxxxxxxxxx"
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                            />
                            {errors?.phone && <p className="mt-1 text-sm text-red-600">{errors.phone}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Kelas</label>
                            <select
                                value={form.class_id}
                                onChange={(e) => setForm({ ...form, class_id: e.target.value })}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                required
                            >
                                <option value="">-- Pilih Kelas --</option>
                                {classes.map((c) => (
                                    <option key={c.id} value={c.id}>{c.name}</option>
                                ))}
                            </select>
                            {errors?.class_id && <p className="mt-1 text-sm text-red-600">{errors.class_id}</p>}
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

                {/* Search */}
                <div className="flex items-center gap-3">
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
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">QR</th>
                                {canManage && <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Aksi</th>}
                            </tr>
                        </thead>
                        <tbody className="divide-y dark:divide-gray-700">
                            {students.data.map((s) => (
                                <tr key={s.id} className="hover:bg-gray-50 dark:hover:bg-navy-700/50">
                                    <td className="px-4 py-3 font-mono text-gray-900 dark:text-white">{s.nis}</td>
                                    <td className="px-4 py-3 text-gray-900 dark:text-white">{s.name}</td>
                                    <td className="px-4 py-3 text-gray-600 dark:text-gray-400">{s.class?.name || '-'}</td>
                                    <td className="px-4 py-3 font-semibold text-gray-900 dark:text-white">{formatRp(s.balance)}</td>
                                    <td className="px-4 py-3">
                                        <button
                                            onClick={() => setQrStudent(s)}
                                            className="text-sm text-navy-600 hover:text-navy-700 dark:text-navy-300"
                                        >
                                            Lihat QR
                                        </button>
                                    </td>
                                    {canManage && (
                                        <td className="px-4 py-3">
                                            <div className="flex gap-2">
                                                <button onClick={() => openEdit(s)} className="text-sm text-gold-600 hover:text-gold-700 dark:text-gold-400">
                                                    Edit
                                                </button>
                                                <button onClick={() => handleDelete(s)} className="text-sm text-red-600 hover:text-red-700 dark:text-red-400">
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    )}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    <Pagination {...students} />
                </div>

                {students.data.length === 0 && (
                    <div className="card text-center text-gray-500 dark:text-gray-400">
                        Belum ada data siswa. Tambahkan siswa baru.
                    </div>
                )}
            </div>

            {/* Import Modal */}
            {showImport && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50" onClick={() => setShowImport(false)}>
                    <div className="mx-4 w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-navy-700" onClick={(e) => e.stopPropagation()}>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white">Impor Siswa dari Excel</h3>
                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Upload file Excel dengan format NIS, Nama, Kelas.
                        </p>

                        <form onSubmit={handleImport} className="mt-4 space-y-4">
                            <input
                                ref={fileRef}
                                type="file"
                                accept=".xlsx,.xls,.csv"
                                className="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-navy-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-navy-700 hover:file:bg-navy-100 dark:file:bg-navy-700 dark:file:text-navy-200"
                                required
                            />

                            <div className="flex gap-2">
                                <button type="submit" className="btn-primary" disabled={processing}>{processing ? 'Mengimpor...' : 'Impor'}</button>
                                <button type="button" onClick={() => setShowImport(false)} className="btn-secondary">Batal</button>
                            </div>
                        </form>

                        <div className="mt-4 border-t border-gray-100 pt-4 text-center dark:border-gray-600">
                            <button
                                onClick={() => window.location.href = route('admin.students.template')}
                                className="text-sm text-navy-600 hover:text-navy-700 dark:text-navy-300"
                            >
                                Download Template Excel
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* QR Modal */}
            {qrStudent && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50" onClick={() => setQrStudent(null)}>
                    <div className="mx-4 w-full max-w-sm rounded-xl bg-white p-6 shadow-xl dark:bg-navy-700" onClick={(e) => e.stopPropagation()}>
                        <div className="mb-4 text-center">
                            <h3 className="text-lg font-semibold text-gray-900 dark:text-white">QR Code Siswa</h3>
                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                {qrStudent.name} ({qrStudent.nis})
                            </p>
                        </div>

                        <div className="flex justify-center rounded-lg bg-white p-4">
                            <img
                                src={route('admin.students.qrcode', qrStudent.id)}
                                alt={`QR ${qrStudent.nis}`}
                                className="h-64 w-64"
                                loading="lazy"
                            />
                        </div>

                        <p className="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">
                            Scan QR untuk login ke portal siswa
                        </p>

                        <div className="mt-4 flex gap-2">
                            <a
                                href={route('admin.students.qrcode.download', qrStudent.id)}
                                className="btn-primary flex-1 justify-center text-center"
                                download
                            >
                                Download PNG
                            </a>
                            <a
                                href={route('admin.students.qrcode', qrStudent.id)}
                                className="btn-secondary flex-1 justify-center text-center"
                                download
                            >
                                Download SVG
                            </a>
                        </div>

                        <button
                            onClick={() => setQrStudent(null)}
                            className="btn-secondary mt-2 w-full justify-center"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
