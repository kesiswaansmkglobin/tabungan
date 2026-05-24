import { Head, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

interface TierInfo {
    name: string;
    icon: string | null;
    color: string | null;
    min_balance: number;
}

interface Student {
    id: number;
    nis: string;
    name: string;
    class_id: number;
    balance: number;
    class: { id: number; name: string } | null;
    progress: { xp: number; tier: TierInfo | null } | null;
}

interface Transaction {
    id: number;
    type: string;
    amount: number;
    balance_after: number;
    transaction_date: string;
    note: string | null;
    created_by_user: { name: string };
}

interface QuestItem {
    id: number;
    title: string;
    description: string | null;
    xp_reward: number;
    type: string;
    completed: boolean;
}

export default function StudentDashboard({ student, transactions, stats, quests, nextTier, tierProgress, xpToNext, allTiers }: {
    student: Student;
    transactions: Transaction[];
    stats: { total_deposit: number; total_withdrawal: number; transaction_count: number };
    quests: QuestItem[];
    nextTier: TierInfo | null;
    tierProgress: number;
    xpToNext: number;
    allTiers: TierInfo[];
}) {
    const [showLogoutConfirm, setShowLogoutConfirm] = useState(false);
    const [showWelcome, setShowWelcome] = useState(false);

    useEffect(() => {
        const seen = sessionStorage.getItem('student_welcome_' + student.id);
        if (!seen) {
            setShowWelcome(true);
            sessionStorage.setItem('student_welcome_' + student.id, '1');
        }
    }, []);

    function handleLogout() {
        router.post(route('student.logout'));
    }

    const formatRp = (amount: number) =>
        new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);

    const tier = student.progress?.tier;
    const tierColor = tier?.color || '#d4a520';
    const tierIcon = tier?.icon || '🏆';
    const xp = student.progress?.xp || 0;

    const completedQuestCount = quests.filter(q => q.completed).length;
    const totalQuestCount = quests.length;

    function getQuestIcon(type: string): string {
        const icons: Record<string, string> = {
            deposit: '💰',
            withdrawal: '💳',
            savings_milestone: '🎯',
            login: '🔑',
            streak: '🔥',
            balance: '💵',
            milestone: '🏅',
            transaction: '📊',
        };
        return icons[type] || '⭐';
    }

    function getTierGlow(tierName: string): string {
        const glows: Record<string, string> = {
            Bronze: 'shadow-[0_0_30px_rgba(205,127,50,0.5)]',
            Silver: 'shadow-[0_0_30px_rgba(192,192,192,0.5)]',
            Gold: 'shadow-[0_0_30px_rgba(255,215,0,0.6)]',
            Platinum: 'shadow-[0_0_30px_rgba(229,228,226,0.5)]',
        };
        return glows[tierName] || 'shadow-[0_0_30px_rgba(212,165,32,0.5)]';
    }

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-900 via-navy-900 to-gray-900 text-white">
            <Head title="Dashboard Siswa" />

            {/* Welcome Modal */}
            {showWelcome && (
                <div className="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm" onClick={() => setShowWelcome(false)}>
                    <div className="mx-4 w-full max-w-sm animate-[fadeIn_0.5s_ease-out]">
                        <div className="rounded-2xl bg-gradient-to-br from-navy-600 to-gray-800 p-6 text-center shadow-2xl">
                            <div className="text-5xl animate-bounce">👋</div>
                            <h2 className="mt-4 text-xl font-bold text-white">
                                Halo, {student.name}!
                            </h2>
                            <p className="mt-2 text-sm text-gray-300">
                                {student.balance === 0
                                    ? 'Ayo mulai menabung! Setor tabunganmu sekarang juga.'
                                    : 'Terus semangat menabung! Raih mimpimu bersama Tabungan Siswa.'}
                            </p>

                            {totalQuestCount > 0 && (
                                <div className="mt-4 rounded-xl bg-white/10 p-3">
                                    <p className="text-xs text-gray-400">Perjalanan Misi</p>
                                    <div className="mt-1 flex items-center justify-center gap-1 text-sm">
                                        <span className="text-emerald-400 font-bold">{completedQuestCount}</span>
                                        <span className="text-gray-400">/</span>
                                        <span>{totalQuestCount}</span>
                                        <span className="text-gray-400 ml-1">misi selesai</span>
                                    </div>
                                    <div className="mt-1.5 h-2 overflow-hidden rounded-full bg-gray-700">
                                        <div
                                            className="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-500 transition-all duration-1000"
                                            style={{ width: `${totalQuestCount > 0 ? (completedQuestCount / totalQuestCount) * 100 : 0}%` }}
                                        />
                                    </div>
                                </div>
                            )}

                            {tier && (
                                <div className="mt-3 flex items-center justify-center gap-2 rounded-xl bg-gold-500/10 p-2 text-sm">
                                    <span>{tierIcon}</span>
                                    <span className="text-gold-300">Tier {tier.name}</span>
                                    <span className="text-gray-400">|</span>
                                    <span className="text-gray-300">{xp} XP</span>
                                </div>
                            )}

                            <button
                                onClick={() => setShowWelcome(false)}
                                className="btn-primary mt-4 w-full justify-center"
                            >
                                {student.balance === 0 ? 'Mulai Menabung!' : 'Lihat Dashboard'}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Header */}
            <header className="relative z-10 border-b border-white/10 bg-black/20 backdrop-blur-sm">
                <div className="mx-auto flex max-w-4xl items-center justify-between px-4 py-3">
                    <div className="flex items-center gap-2">
                        <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-gold-400 to-yellow-600 text-lg shadow-lg">
                            🪙
                        </div>
                        <div>
                            <h1 className="text-sm font-bold leading-tight">Tabungan Siswa</h1>
                            <p className="text-[10px] text-gray-400">SMK Globin</p>
                        </div>
                    </div>
                    <div className="relative">
                        <button
                            onClick={() => setShowLogoutConfirm(!showLogoutConfirm)}
                            className="flex items-center gap-2 rounded-xl bg-white/10 px-3 py-1.5 text-sm text-white backdrop-blur-sm transition hover:bg-white/20"
                        >
                            <div className="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-gold-400 to-yellow-600 text-[10px] font-bold text-navy-900">
                                {student.name.charAt(0)}
                            </div>
                            <span className="hidden sm:inline">{student.name}</span>
                            <svg className="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        {showLogoutConfirm && (
                            <>
                                <div className="fixed inset-0 z-[999]" onClick={() => setShowLogoutConfirm(false)} />
                                <div className="absolute right-0 z-[1000] mt-2 w-44 rounded-xl border border-white/10 bg-gray-800 shadow-xl backdrop-blur-xl">
                                    <div className="border-b border-white/10 px-4 py-2 text-xs text-gray-400">
                                        {student.nis}
                                    </div>
                                    <button
                                        onClick={handleLogout}
                                        className="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-red-400 transition hover:bg-white/5"
                                    >
                                        <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                        </svg>
                                        Logout
                                    </button>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </header>

            {/* Content */}
            <main className="mx-auto max-w-4xl space-y-5 px-4 py-5">
                {/* === Hero: Tier Badge + Balance === */}
                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-navy-600/80 via-navy-700/60 to-gray-800/80 p-6 text-center backdrop-blur-sm">
                    <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(212,165,32,0.15),transparent_70%)]" />
                    <div className="relative">
                        {/* Tier Icon */}
                        <div className={`mx-auto mb-3 flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-gray-700 to-gray-900 text-4xl ring-2 ring-[${tierColor}]/50 ${tier ? getTierGlow(tier.name) : ''}`}>
                            <span className="drop-shadow-lg">{tierIcon}</span>
                        </div>

                        <p className="text-sm font-medium uppercase tracking-wider text-gray-400">
                            {tier ? `Tier ${tier.name}` : 'Belum memiliki tier'}
                        </p>
                        <p className="mt-1 text-4xl font-bold tracking-tight text-white drop-shadow-lg">
                            {formatRp(student.balance)}
                        </p>
                        <p className="mt-0.5 text-xs text-gray-400">
                            {student.class?.name || '-'}
                        </p>

                        {/* XP / Tier Progress Bar */}
                        {nextTier ? (
                            <div className="mx-auto mt-4 max-w-xs">
                                <div className="flex items-center justify-between text-xs text-gray-400">
                                    <span>{tier ? tier.name : 'Mulai'} {tier?.icon}</span>
                                    <span>{nextTier.icon} {nextTier.name}</span>
                                </div>
                                <div className="mt-1 h-3 overflow-hidden rounded-full bg-gray-700">
                                    <div
                                        className="h-full rounded-full bg-gradient-to-r from-gold-400 to-yellow-500 transition-all duration-1000"
                                        style={{ width: `${tierProgress}%` }}
                                    />
                                </div>
                                <p className="mt-1 text-xs text-gray-500">
                                    {xpToNext > 0 ? `${formatRp(xpToNext)} lagi ke ${nextTier.name}` : 'Tier maksimum tercapai! 🎉'}
                                </p>
                            </div>
                        ) : (
                            <div className="mx-auto mt-4 max-w-xs">
                                <div className="h-3 overflow-hidden rounded-full bg-gray-700">
                                    <div className="h-full w-full rounded-full bg-gold-500" />
                                </div>
                                <p className="mt-1 text-xs text-gray-500">Tier maksimum tercapai! 🎉</p>
                            </div>
                        )}
                    </div>
                </div>

                {/* === Stats Grid === */}
                <div className="grid grid-cols-3 gap-3">
                    <div className="rounded-xl bg-gradient-to-br from-emerald-900/40 to-emerald-800/20 p-4 text-center backdrop-blur-sm">
                        <p className="text-2xl font-bold text-emerald-400">{formatRp(stats.total_deposit)}</p>
                        <p className="mt-0.5 text-xs text-gray-400">Total Setoran</p>
                    </div>
                    <div className="rounded-xl bg-gradient-to-br from-red-900/40 to-red-800/20 p-4 text-center backdrop-blur-sm">
                        <p className="text-2xl font-bold text-red-400">{formatRp(stats.total_withdrawal)}</p>
                        <p className="mt-0.5 text-xs text-gray-400">Total Penarikan</p>
                    </div>
                    <div className="rounded-xl bg-gradient-to-br from-blue-900/40 to-blue-800/20 p-4 text-center backdrop-blur-sm">
                        <p className="text-2xl font-bold text-blue-400">{stats.transaction_count}x</p>
                        <p className="mt-0.5 text-xs text-gray-400">Transaksi</p>
                    </div>
                </div>

                {/* === Active Quests / Missions === */}
                <div className="rounded-xl bg-white/5 p-5 backdrop-blur-sm">
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-base font-bold">
                            🎯 Misi Aktif
                            <span className="ml-2 text-xs font-normal text-gray-400">
                                ({completedQuestCount}/{totalQuestCount} selesai)
                            </span>
                        </h2>
                        <div className="flex items-center gap-1 text-xs text-gray-400">
                            <span className="inline-block h-2 w-2 rounded-full bg-gold-400" />
                            XP Reward
                        </div>
                    </div>

                    {quests.length === 0 && (
                        <p className="py-4 text-center text-sm text-gray-500">Belum ada misi tersedia.</p>
                    )}

                    <div className="grid gap-2.5 sm:grid-cols-2">
                        {quests.map((q) => (
                            <div
                                key={q.id}
                                className={`relative overflow-hidden rounded-xl border p-3.5 transition ${
                                    q.completed
                                        ? 'border-emerald-500/30 bg-emerald-900/20'
                                        : 'border-white/10 bg-white/5 hover:bg-white/10'
                                }`}
                            >
                                {q.completed && (
                                    <div className="absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-xs text-white shadow-lg">
                                        ✓
                                    </div>
                                )}

                                <div className="flex items-start gap-3">
                                    <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-lg ${
                                        q.completed
                                            ? 'bg-emerald-900/40'
                                            : 'bg-white/10'
                                    }`}>
                                        {getQuestIcon(q.type)}
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <h3 className={`text-sm font-semibold ${
                                            q.completed ? 'text-emerald-300' : 'text-white'
                                        }`}>
                                            {q.title}
                                        </h3>
                                        {q.description && (
                                            <p className="mt-0.5 text-xs text-gray-400">{q.description}</p>
                                        )}
                                        <div className="mt-1.5 flex items-center gap-2">
                                            <span className="inline-flex items-center gap-1 rounded-full bg-gold-500/20 px-2 py-0.5 text-[10px] font-medium text-gold-300">
                                                +{q.xp_reward} XP
                                            </span>
                                            {q.completed && (
                                                <span className="inline-flex items-center gap-1 rounded-full bg-emerald-500/20 px-2 py-0.5 text-[10px] text-emerald-300">
                                                    ✓ Selesai
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* === All Tiers (Achievement Showcase) === */}
                {allTiers.length > 0 && (
                    <div className="rounded-xl bg-white/5 p-5 backdrop-blur-sm">
                        <h2 className="mb-3 text-base font-bold">🏅 Tingkatan Tier</h2>
                        <div className="flex flex-wrap gap-2">
                            {allTiers.map((t, i) => {
                                const unlocked = student.balance >= t.min_balance;
                                return (
                                    <div
                                        key={i}
                                        className={`flex items-center gap-2 rounded-xl border px-3 py-2 text-sm transition ${
                                            unlocked
                                                ? 'border-gold-500/30 bg-gold-500/10'
                                                : 'border-white/10 bg-white/5 opacity-50'
                                        }`}
                                    >
                                        <span className="text-lg">{t.icon || '🏆'}</span>
                                        <div>
                                            <p className={`text-xs font-semibold ${unlocked ? 'text-gold-300' : 'text-gray-400'}`}>
                                                {t.name}
                                            </p>
                                            <p className="text-[10px] text-gray-500">{formatRp(t.min_balance)}</p>
                                        </div>
                                        {unlocked && (
                                            <span className="ml-1 text-xs text-emerald-400">✓</span>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                )}

                {/* === Recent Transactions === */}
                <div className="rounded-xl bg-white/5 p-5 backdrop-blur-sm">
                    <h2 className="mb-3 text-base font-bold">📋 Transaksi Terbaru</h2>

                    {transactions.length === 0 && (
                        <p className="py-4 text-center text-sm text-gray-500">Belum ada transaksi.</p>
                    )}

                    <div className="space-y-1">
                        {transactions.map((t) => (
                            <div
                                key={t.id}
                                className="flex items-center justify-between rounded-xl px-3 py-2.5 transition hover:bg-white/5"
                            >
                                <div className="flex items-center gap-3">
                                    <div className={`flex h-9 w-9 items-center justify-center rounded-xl text-sm ${
                                        t.type === 'setor'
                                            ? 'bg-emerald-900/40 text-emerald-400'
                                            : 'bg-red-900/40 text-red-400'
                                    }`}>
                                        {t.type === 'setor' ? '↑' : '↓'}
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-white">
                                            {t.type === 'setor' ? 'Setoran' : 'Penarikan'}
                                        </p>
                                        <p className="text-xs text-gray-500">
                                            {new Date(t.transaction_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}
                                            {t.note && ` • ${t.note}`}
                                        </p>
                                    </div>
                                </div>
                                <div className="text-right">
                                    <p className={`text-sm font-bold ${
                                        t.type === 'setor' ? 'text-emerald-400' : 'text-red-400'
                                    }`}>
                                        {t.type === 'setor' ? '+' : '-'}{formatRp(t.amount)}
                                    </p>
                                    <p className="text-[10px] text-gray-500">
                                        Saldo: {formatRp(t.balance_after)}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Footer */}
                <p className="pb-4 text-center text-[10px] text-gray-600">
                    Tabungan Siswa SMK Globin — Terus menabung, raih mimpimu! 🚀
                </p>
            </main>
        </div>
    );
}
