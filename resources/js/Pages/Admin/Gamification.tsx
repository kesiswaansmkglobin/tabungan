import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Pagination from '@/Components/Pagination';
import { Head, router, usePage } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

interface Tier {
    id: number;
    name: string;
    min_balance: number;
    icon: string | null;
    color: string | null;
    order_index: number;
}

interface Quest {
    id: number;
    title: string;
    description: string | null;
    xp_reward: number;
    type: string;
    criteria: Record<string, any> | null;
    active: boolean;
}

export default function Gamification({ tiers, quests }: { tiers: { data: Tier[]; links: any[]; current_page: number; last_page: number; from: number; to: number; total: number }; quests: { data: Quest[]; links: any[]; current_page: number; last_page: number; from: number; to: number; total: number } }) {
    const { errors, flash } = usePage().props as any;

    const [showTierForm, setShowTierForm] = useState(false);
    const [editingTier, setEditingTier] = useState<Tier | null>(null);
    const [tierForm, setTierForm] = useState({ name: '', min_balance: '', icon: '', color: '', order_index: '' });

    const [showQuestForm, setShowQuestForm] = useState(false);
    const [editingQuest, setEditingQuest] = useState<Quest | null>(null);
    const [questForm, setQuestForm] = useState({ title: '', description: '', xp_reward: '', type: '', criteria: '', active: true });

    function openCreateTier() {
        setEditingTier(null);
        setTierForm({ name: '', min_balance: '', icon: '', color: '', order_index: '' });
        setShowTierForm(true);
    }

    function openEditTier(t: Tier) {
        setEditingTier(t);
        setTierForm({
            name: t.name,
            min_balance: t.min_balance.toString(),
            icon: t.icon || '',
            color: t.color || '',
            order_index: t.order_index.toString(),
        });
        setShowTierForm(true);
    }

    function handleTierSubmit(e: FormEvent) {
        e.preventDefault();
        const data = {
            name: tierForm.name,
            min_balance: parseInt(tierForm.min_balance),
            icon: tierForm.icon || null,
            color: tierForm.color || null,
            order_index: parseInt(tierForm.order_index),
        };
        if (editingTier) {
            router.patch(route('admin.tiers.update', editingTier.id), data);
        } else {
            router.post(route('admin.tiers.store'), data);
        }
    }

    function handleTierDelete(t: Tier) {
        if (confirm(`Hapus tier "${t.name}"?`)) {
            router.delete(route('admin.tiers.destroy', t.id));
        }
    }

    function generateCriteria(type: string, desc: string): string {
        if (!type || !desc) return '';
        const nums = desc.match(/\d[\d.]*/g);
        const num = nums ? parseInt(nums[0].replace(/\./g, '')) : 0;
        const isStreak = /hari\s*berturut|consecutive/i.test(desc);

        switch (type) {
            case 'deposit':
            case 'withdrawal':
            case 'transaction':
            case 'deposit_count':
            case 'streak':
                return isStreak
                    ? JSON.stringify({ days: num || 3 })
                    : JSON.stringify({ count: num || 1 });
            case 'savings_milestone':
            case 'balance':
            case 'milestone':
                return JSON.stringify({ amount: num || 50000 });
            case 'login':
                return JSON.stringify({ count: num || 1 });
            default:
                return '';
        }
    }

    function handleAutoCriteria() {
        const generated = generateCriteria(questForm.type, questForm.description);
        if (generated) {
            setQuestForm({ ...questForm, criteria: generated });
        }
    }

    function openCreateQuest() {
        setEditingQuest(null);
        setQuestForm({ title: '', description: '', xp_reward: '', type: '', criteria: '', active: true });
        setShowQuestForm(true);
    }

    function openEditQuest(q: Quest) {
        setEditingQuest(q);
        setQuestForm({
            title: q.title,
            description: q.description || '',
            xp_reward: q.xp_reward.toString(),
            type: q.type,
            criteria: q.criteria ? JSON.stringify(q.criteria) : '',
            active: q.active,
        });
        setShowQuestForm(true);
    }

    function handleQuestSubmit(e: FormEvent) {
        e.preventDefault();
        const data: Record<string, any> = {
            title: questForm.title,
            description: questForm.description || null,
            xp_reward: parseInt(questForm.xp_reward),
            type: questForm.type,
            active: questForm.active,
        };
        if (questForm.criteria) {
            try {
                data.criteria = JSON.parse(questForm.criteria);
            } catch {
                data.criteria = questForm.criteria;
            }
        }
        if (editingQuest) {
            router.patch(route('admin.quests.update', editingQuest.id), data);
        } else {
            router.post(route('admin.quests.store'), data);
        }
    }

    function handleQuestDelete(q: Quest) {
        if (confirm(`Hapus quest "${q.title}"?`)) {
            router.delete(route('admin.quests.destroy', q.id));
        }
    }

    return (
        <AuthenticatedLayout>
            <Head title="Gamifikasi" />

            <div className="space-y-8">
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

                {/* Tier Section */}
                <section className="space-y-4">
                    <div className="flex items-center justify-between">
                        <h2 className="text-xl font-bold text-gray-900 dark:text-white">Tier / Tingkatan</h2>
                        <button onClick={openCreateTier} className="btn-primary">Tambah Tier</button>
                    </div>

                    {showTierForm && (
                        <form onSubmit={handleTierSubmit} className="card max-w-lg space-y-4">
                            <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                {editingTier ? 'Edit Tier' : 'Tambah Tier'}
                            </h3>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Tier</label>
                                <input
                                    type="text"
                                    value={tierForm.name}
                                    onChange={(e) => setTierForm({ ...tierForm, name: e.target.value })}
                                    className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                    required
                                />
                                {errors?.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Min. Saldo</label>
                                    <input
                                        type="number"
                                        min="0"
                                        value={tierForm.min_balance}
                                        onChange={(e) => setTierForm({ ...tierForm, min_balance: e.target.value })}
                                        className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                        required
                                    />
                                    {errors?.min_balance && <p className="mt-1 text-sm text-red-600">{errors.min_balance}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Urutan</label>
                                    <input
                                        type="number"
                                        min="0"
                                        value={tierForm.order_index}
                                        onChange={(e) => setTierForm({ ...tierForm, order_index: e.target.value })}
                                        className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                        required
                                    />
                                    {errors?.order_index && <p className="mt-1 text-sm text-red-600">{errors.order_index}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Icon (opsional)</label>
                                    <input
                                        type="text"
                                        value={tierForm.icon}
                                        onChange={(e) => setTierForm({ ...tierForm, icon: e.target.value })}
                                        placeholder="emoji atau class icon"
                                        className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Warna (opsional)</label>
                                    <input
                                        type="text"
                                        value={tierForm.color}
                                        onChange={(e) => setTierForm({ ...tierForm, color: e.target.value })}
                                        placeholder="#hex atau nama warna"
                                        className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                    />
                                </div>
                            </div>

                            <div className="flex gap-2">
                                <button type="submit" className="btn-primary">
                                    {editingTier ? 'Simpan' : 'Tambah'}
                                </button>
                                <button type="button" onClick={() => setShowTierForm(false)} className="btn-secondary">
                                    Batal
                                </button>
                            </div>
                        </form>
                    )}

                    <div className="card overflow-x-auto p-0">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b bg-gray-50 dark:border-gray-700 dark:bg-navy-800">
                                <tr>
                                    <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Urutan</th>
                                    <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Nama Tier</th>
                                    <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Min. Saldo</th>
                                    <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Icon</th>
                                    <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Warna</th>
                                    <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y dark:divide-gray-700">
                                {tiers.data.length === 0 ? (
                                    <tr><td colSpan={6} className="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada tier.</td></tr>
                                ) : tiers.data.map((t) => (
                                    <tr key={t.id} className="hover:bg-gray-50 dark:hover:bg-navy-700/50">
                                        <td className="px-4 py-3 text-gray-900 dark:text-white">{t.order_index}</td>
                                        <td className="px-4 py-3 font-medium text-gray-900 dark:text-white">{t.name}</td>
                                        <td className="px-4 py-3 text-gray-600 dark:text-gray-400">
                                            {new Intl.NumberFormat('id-ID').format(t.min_balance)}
                                        </td>
                                        <td className="px-4 py-3 text-gray-600 dark:text-gray-400">{t.icon || '-'}</td>
                                        <td className="px-4 py-3">
                                            {t.color ? (
                                                <span className="inline-flex items-center gap-1">
                                                    <span className="inline-block h-3 w-3 rounded-full" style={{ backgroundColor: t.color }} />
                                                    {t.color}
                                                </span>
                                            ) : '-'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex gap-2">
                                                <button onClick={() => openEditTier(t)} className="text-sm text-gold-600 hover:text-gold-700 dark:text-gold-400">
                                                    Edit
                                                </button>
                                                <button onClick={() => handleTierDelete(t)} className="text-sm text-red-600 hover:text-red-700 dark:text-red-400">
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        <Pagination {...tiers} />
                    </div>
                </section>

                {/* Quest Section */}
                <section className="space-y-4">
                    <div className="flex items-center justify-between">
                        <h2 className="text-xl font-bold text-gray-900 dark:text-white">Quest / Tantangan</h2>
                        <button onClick={openCreateQuest} className="btn-primary">Tambah Quest</button>
                    </div>

                    {showQuestForm && (
                        <form onSubmit={handleQuestSubmit} className="card max-w-lg space-y-4">
                            <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                {editingQuest ? 'Edit Quest' : 'Tambah Quest'}
                            </h3>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Judul</label>
                                <input
                                    type="text"
                                    value={questForm.title}
                                    onChange={(e) => setQuestForm({ ...questForm, title: e.target.value })}
                                    className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                    required
                                />
                                {errors?.title && <p className="mt-1 text-sm text-red-600">{errors.title}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label>
                                <textarea
                                    value={questForm.description}
                                    onChange={(e) => setQuestForm({ ...questForm, description: e.target.value })}
                                    rows={2}
                                    className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">XP Reward</label>
                                    <input
                                        type="number"
                                        min="0"
                                        value={questForm.xp_reward}
                                        onChange={(e) => setQuestForm({ ...questForm, xp_reward: e.target.value })}
                                        className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                        required
                                    />
                                    {errors?.xp_reward && <p className="mt-1 text-sm text-red-600">{errors.xp_reward}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe</label>
                                    <select
                                        value={questForm.type}
                                        onChange={(e) => setQuestForm({ ...questForm, type: e.target.value })}
                                        className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                        required
                                    >
                                        <option value="">Pilih Tipe</option>
                                        <option value="deposit">Setoran</option>
                                        <option value="withdrawal">Penarikan</option>
                                        <option value="savings_milestone">Saldo / Pencapaian</option>
                                        <option value="streak">Konsisten (beruntun)</option>
                                        <option value="login">Login</option>
                                    </select>
                                    {errors?.type && <p className="mt-1 text-sm text-red-600">{errors.type}</p>}
                                </div>
                            </div>

                            <div>
                                <div className="flex items-center justify-between">
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Kriteria (JSON, opsional)</label>
                                    <button
                                        type="button"
                                        onClick={handleAutoCriteria}
                                        className="text-xs text-navy-600 hover:text-navy-700 dark:text-navy-300"
                                    >
                                        Buat dari deskripsi
                                    </button>
                                </div>
                                <textarea
                                    value={questForm.criteria}
                                    onChange={(e) => setQuestForm({ ...questForm, criteria: e.target.value })}
                                    rows={2}
                                    placeholder='{"count": 5}'
                                    className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white font-mono"
                                />
                                {errors?.criteria && <p className="mt-1 text-sm text-red-600">{errors.criteria}</p>}
                            </div>

                            <div className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="active"
                                    checked={questForm.active}
                                    onChange={(e) => setQuestForm({ ...questForm, active: e.target.checked })}
                                    className="rounded border-gray-300 text-gold-500 focus:ring-gold-500 dark:border-gray-600"
                                />
                                <label htmlFor="active" className="text-sm font-medium text-gray-700 dark:text-gray-300">Aktif</label>
                            </div>

                            <div className="flex gap-2">
                                <button type="submit" className="btn-primary">
                                    {editingQuest ? 'Simpan' : 'Tambah'}
                                </button>
                                <button type="button" onClick={() => setShowQuestForm(false)} className="btn-secondary">
                                    Batal
                                </button>
                            </div>
                        </form>
                    )}

                    <div className="card overflow-x-auto p-0">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b bg-gray-50 dark:border-gray-700 dark:bg-navy-800">
                                <tr>
                                    <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Judul</th>
                                    <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Tipe</th>
                                    <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">XP</th>
                                    <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Status</th>
                                    <th className="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y dark:divide-gray-700">
                                {quests.data.length === 0 ? (
                                    <tr><td colSpan={5} className="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada misi.</td></tr>
                                ) : quests.data.map((q) => (
                                    <tr key={q.id} className="hover:bg-gray-50 dark:hover:bg-navy-700/50">
                                        <td className="px-4 py-3">
                                            <div className="text-gray-900 dark:text-white">{q.title}</div>
                                            {q.description && (
                                                <div className="text-xs text-gray-500 dark:text-gray-400">{q.description}</div>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 text-gray-600 dark:text-gray-400">{q.type}</td>
                                        <td className="px-4 py-3 font-medium text-gray-900 dark:text-white">{q.xp_reward}</td>
                                        <td className="px-4 py-3">
                                            <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${
                                                q.active
                                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                                                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
                                            }`}>
                                                {q.active ? 'Aktif' : 'Nonaktif'}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex gap-2">
                                                <button onClick={() => openEditQuest(q)} className="text-sm text-gold-600 hover:text-gold-700 dark:text-gold-400">
                                                    Edit
                                                </button>
                                                <button onClick={() => handleQuestDelete(q)} className="text-sm text-red-600 hover:text-red-700 dark:text-red-400">
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        <Pagination {...quests} />
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
