import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Pagination from '@/Components/Pagination';
import { Head, router, usePage } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

interface AppUser {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    primary_role: string | null;
    role_names: string[];
}

export default function Users({ users }: { users: { data: AppUser[]; links: any[]; current_page: number; last_page: number; from: number; to: number; total: number } }) {
    const { errors, flash } = usePage().props as any;
    const [showForm, setShowForm] = useState(false);
    const [editing, setEditing] = useState<AppUser | null>(null);
    const [form, setForm] = useState({ name: '', email: '', password: '', password_confirmation: '', role: 'staff' });
    const [processing, setProcessing] = useState(false);

    function openCreate() {
        setEditing(null);
        setForm({ name: '', email: '', password: '', password_confirmation: '', role: 'staff' });
        setShowForm(true);
    }

    function openEdit(u: AppUser) {
        setEditing(u);
        setForm({ name: u.name, email: u.email, password: '', password_confirmation: '', role: u.primary_role || 'staff' });
        setShowForm(true);
    }

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        setProcessing(true);
        const data: any = { name: form.name, email: form.email, role: form.role };
        if (form.password) {
            data.password = form.password;
        }
        if (editing) {
            router.patch(route('admin.users.update', editing.id), data, {
                onFinish: () => setProcessing(false),
            });
        } else {
            router.post(route('admin.users.store'), data, {
                onFinish: () => setProcessing(false),
            });
        }
    }

    function handleDelete(u: AppUser) {
        if (confirm(`Hapus pengguna "${u.name}"?`)) {
            setProcessing(true);
            router.delete(route('admin.users.destroy', u.id), {
                onFinish: () => setProcessing(false),
            });
        }
    }

    const roleBadge = (role: string) => {
        const colors: Record<string, string> = {
            admin: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
            staff: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
            wali_kelas: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        };
        return (
            <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${colors[role] || 'bg-gray-100 text-gray-700'}`}>
                {role === 'wali_kelas' ? 'Wali Kelas' : role.charAt(0).toUpperCase() + role.slice(1)}
            </span>
        );
    };

    return (
        <AuthenticatedLayout>
            <Head title="Pengguna" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Pengguna</h1>
                    <button onClick={openCreate} className="btn-primary">Tambah Pengguna</button>
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
                            {editing ? 'Edit Pengguna' : 'Tambah Pengguna'}
                        </h3>

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
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input
                                type="email"
                                value={form.email}
                                onChange={(e) => setForm({ ...form, email: e.target.value })}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                required
                            />
                            {errors?.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Password {editing && '(kosongkan jika tidak diubah)'}
                            </label>
                            <input
                                type="password"
                                value={form.password}
                                onChange={(e) => setForm({ ...form, password: e.target.value })}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                {...(editing ? {} : { required: true })}
                            />
                            {errors?.password && <p className="mt-1 text-sm text-red-600">{errors.password}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                            <select
                                value={form.role}
                                onChange={(e) => setForm({ ...form, role: e.target.value })}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                required
                            >
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                                <option value="wali_kelas">Wali Kelas</option>
                            </select>
                            {errors?.role && <p className="mt-1 text-sm text-red-600">{errors.role}</p>}
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
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Nama</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Email</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Role</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y dark:divide-gray-700">
                            {users.data.length === 0 ? (
                                <tr><td colSpan={4} className="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada pengguna.</td></tr>
                            ) : users.data.map((u) => (
                                <tr key={u.id} className="hover:bg-gray-50 dark:hover:bg-navy-700/50">
                                    <td className="px-4 py-3 text-gray-900 dark:text-white">{u.name}</td>
                                    <td className="px-4 py-3 text-gray-600 dark:text-gray-400">{u.email}</td>
                                    <td className="px-4 py-3">{roleBadge(u.primary_role || '')}</td>
                                    <td className="px-4 py-3">
                                        <div className="flex gap-2">
                                            <button onClick={() => openEdit(u)} className="text-sm text-gold-600 hover:text-gold-700 dark:text-gold-400">
                                                Edit
                                            </button>
                                            <button onClick={() => handleDelete(u)} className="text-sm text-red-600 hover:text-red-700 dark:text-red-400">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    <Pagination {...users} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
