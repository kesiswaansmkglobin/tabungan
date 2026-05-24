import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Pagination from '@/Components/Pagination';
import { Head, router, usePage } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

interface Student {
    id: number;
    nis: string;
    name: string;
    class_id: number;
    balance: number;
    class: { id: number; name: string } | null;
}

interface Transaction {
    id: number;
    student_id: number;
    type: string;
    amount: number;
    balance_after: number;
    transaction_date: string;
    note: string | null;
    created_by: number;
    student: { id: number; nis: string; name: string };
    created_by_user: { id: number; name: string };
}

interface ClassOption {
    id: number;
    name: string;
}

export default function TransactionsIndex({
    transactions,
    students,
    classes,
}: {
    transactions: { data: Transaction[]; links: any[]; current_page: number; last_page: number; from: number; to: number; total: number };
    students: Student[];
    classes: ClassOption[];
}) {
    const { errors, flash } = usePage().props as any;
    const [showForm, setShowForm] = useState(false);
    const [editingTransaction, setEditingTransaction] = useState<Transaction | null>(null);
    const [selectedStudent, setSelectedStudent] = useState<Student | null>(null);
    const [selectedClassId, setSelectedClassId] = useState('');
    const [form, setForm] = useState({
        student_id: '',
        type: 'setor' as string,
        amount: '',
        transaction_date: new Date().toISOString().split('T')[0],
        note: '',
    });

    const filteredStudents = students.filter(
        (s) => !selectedClassId || s.class_id === parseInt(selectedClassId)
    );

    function selectStudent(student: Student) {
        setSelectedStudent(student);
        setForm({ ...form, student_id: student.id.toString() });
    }

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        if (editingTransaction) {
            router.patch(route('transactions.update', editingTransaction.id), form);
        } else {
            router.post(route('transactions.store'), form);
        }
    }

    function handleDelete(t: Transaction) {
        if (confirm(`Hapus transaksi ini?`)) {
            router.delete(route('transactions.destroy', t.id));
        }
    }

    function openEdit(t: Transaction) {
        setEditingTransaction(t);
        const s = students.find(st => st.id === t.student_id);
        if (s) {
            setSelectedStudent(s);
            setSelectedClassId(s.class_id.toString());
        }
        setForm({
            student_id: t.student_id.toString(),
            type: t.type,
            amount: t.amount.toString(),
            transaction_date: t.transaction_date,
            note: t.note || '',
        });
        setShowForm(true);
    }

    function closeForm() {
        setShowForm(false);
        setEditingTransaction(null);
        setSelectedStudent(null);
        setSelectedClassId('');
        setForm({
            student_id: '',
            type: 'setor',
            amount: '',
            transaction_date: new Date().toISOString().split('T')[0],
            note: '',
        });
    }

    const formatRp = (amount: number) =>
        new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);

    return (
        <AuthenticatedLayout>
            <Head title="Transaksi" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Transaksi</h1>
                    <button onClick={() => editingTransaction ? closeForm() : setShowForm(!showForm)} className="btn-primary">
                        {showForm ? 'Tutup' : 'Transaksi Baru'}
                    </button>
                </div>

                {flash?.success && (
                    <div className="rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                        {flash.success}
                    </div>
                )}

                {errors?.error && (
                    <div className="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">
                        {errors.error}
                    </div>
                )}

                {showForm && (
                    <form onSubmit={handleSubmit} className="card max-w-lg space-y-4">
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                            {editingTransaction ? 'Edit Transaksi' : 'Transaksi Baru'}
                        </h3>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Kelas</label>
                            <select
                                value={selectedClassId}
                                onChange={(e) => {
                                    setSelectedClassId(e.target.value);
                                    setSelectedStudent(null);
                                    setForm({ ...form, student_id: '' });
                                }}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                            >
                                <option value="">-- Pilih Kelas --</option>
                                {classes.map((c) => (
                                    <option key={c.id} value={c.id}>{c.name}</option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Siswa</label>
                            <select
                                value={form.student_id}
                                onChange={(e) => {
                                    const s = students.find((st) => st.id === parseInt(e.target.value));
                                    if (s) selectStudent(s);
                                }}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                required
                                disabled={!selectedClassId}
                            >
                                <option value="">-- Pilih Siswa --</option>
                                {filteredStudents.map((s) => (
                                    <option key={s.id} value={s.id}>{s.name} ({s.nis})</option>
                                ))}
                            </select>
                            {errors?.student_id && <p className="mt-1 text-sm text-red-600">{errors.student_id}</p>}
                        </div>

                        {selectedStudent && (
                            <div className="rounded-lg bg-navy-50 p-3 dark:bg-navy-700">
                                <p className="text-sm font-semibold text-navy-700 dark:text-navy-200">
                                    {selectedStudent.name} ({selectedStudent.nis})
                                </p>
                                <p className="text-xs text-gray-500">
                                    Kelas: {selectedStudent.class?.name} | Saldo: {formatRp(selectedStudent.balance)}
                                </p>
                            </div>
                        )}

                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis</label>
                                <select
                                    value={form.type}
                                    onChange={(e) => setForm({ ...form, type: e.target.value })}
                                    className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                >
                                    <option value="setor">Setoran</option>
                                    <option value="tarik">Penarikan</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah</label>
                                <input
                                    type="number"
                                    min="1"
                                    value={form.amount}
                                    onChange={(e) => setForm({ ...form, amount: e.target.value })}
                                    className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                    required
                                />
                                {errors?.amount && <p className="mt-1 text-sm text-red-600">{errors.amount}</p>}
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal</label>
                            <input
                                type="date"
                                value={form.transaction_date}
                                onChange={(e) => setForm({ ...form, transaction_date: e.target.value })}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                required
                            />
                            {errors?.transaction_date && <p className="mt-1 text-sm text-red-600">{errors.transaction_date}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan</label>
                            <textarea
                                value={form.note}
                                onChange={(e) => setForm({ ...form, note: e.target.value })}
                                rows={2}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                            />
                        </div>

                        <div className="flex gap-2">
                            <button type="submit" className="btn-primary w-full justify-center">
                                {editingTransaction ? 'Simpan Perubahan' : 'Simpan Transaksi'}
                            </button>
                            <button type="button" onClick={closeForm} className="btn-secondary">
                                Batal
                            </button>
                        </div>
                    </form>
                )}

                <div className="card overflow-x-auto p-0">
                    <table className="w-full text-left text-sm">
                        <thead className="border-b bg-gray-50 dark:border-gray-700 dark:bg-navy-800">
                            <tr>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Tanggal</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Siswa</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Jenis</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Jumlah</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Saldo Akhir</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Petugas</th>
                                <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y dark:divide-gray-700">
                            {transactions.data.length === 0 ? (
                                <tr><td colSpan={7} className="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada transaksi.</td></tr>
                            ) : transactions.data.map((t) => (
                                <tr key={t.id} className="hover:bg-gray-50 dark:hover:bg-navy-700/50">
                                    <td className="px-4 py-3 text-gray-900 dark:text-white">
                                        {new Date(t.transaction_date).toLocaleDateString('id-ID')}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="text-gray-900 dark:text-white">{t.student?.name}</div>
                                        <div className="text-xs text-gray-500">{t.student?.nis}</div>
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
                                    <td className="px-4 py-3">
                                        <div className="flex gap-2">
                                            <button onClick={() => openEdit(t)} className="text-sm text-gold-600 hover:text-gold-700 dark:text-gold-400">
                                                Edit
                                            </button>
                                            <button onClick={() => handleDelete(t)} className="text-sm text-red-600 hover:text-red-700 dark:text-red-400">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    <Pagination {...transactions} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
