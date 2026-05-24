import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { FormEvent, useRef, useState } from 'react';

export default function Settings() {
    const { errors, flash } = usePage().props as any;
    const [backupPassword, setBackupPassword] = useState('');
    const [resetPassword, setResetPassword] = useState('');
    const [restorePassword, setRestorePassword] = useState('');
    const [backupError, setBackupError] = useState('');
    const [confirmText, setConfirmText] = useState('');
    const [showResetConfirm, setShowResetConfirm] = useState(false);
    const [showRestoreConfirm, setShowRestoreConfirm] = useState(false);
    const [isBackingUp, setIsBackingUp] = useState(false);
    const [isResetting, setIsResetting] = useState(false);
    const [isRestoring, setIsRestoring] = useState(false);
    const restoreFileRef = useRef<HTMLInputElement>(null);
    const [restoreFile, setRestoreFile] = useState<File | null>(null);
    function handleBackup(e: FormEvent) {
        e.preventDefault();
        setBackupError('');
        const csrfToken = (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content ?? '';
        setIsBackingUp(true);
        fetch(route('admin.settings.backup'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ password: backupPassword }),
        })
        .then(async res => {
            if (!res.ok) {
                const err = await res.json().catch(() => ({ error: 'Gagal backup' }));
                throw new Error(err.error || 'Gagal backup');
            }
            return res.blob();
        })
        .then(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'backup-'+new Date().toISOString().slice(0,19).replace(/[T:]/g,'-')+'.sql';
            a.click();
            URL.revokeObjectURL(url);
            setBackupPassword('');
        })
        .catch(err => {
            setBackupError(err.message);
        })
        .finally(() => setIsBackingUp(false));
    }

    function handleRestore(e: FormEvent) {
        e.preventDefault();
        if (!restoreFile) return;
        setIsRestoring(true);
        const data = new FormData();
        data.append('file', restoreFile);
        data.append('password', restorePassword);
        router.post(route('admin.settings.restore'), data, {
            forceFormData: true,
            onFinish: () => {
                setIsRestoring(false);
                setRestorePassword('');
                setShowRestoreConfirm(false);
                setRestoreFile(null);
            },
        });
    }

    function handleReset(e: FormEvent) {
        e.preventDefault();
        setIsResetting(true);
        router.post(route('admin.settings.reset'), {
            password: resetPassword,
            confirmation: confirmText,
        }, {
            onFinish: () => {
                setIsResetting(false);
                setResetPassword('');
                setConfirmText('');
                setShowResetConfirm(false);
            },
        });
    }

    return (
        <AuthenticatedLayout>
            <Head title="Pengaturan" />

            <div className="space-y-6">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan</h1>

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

                {/* Backup Section */}
                <div className="card space-y-4">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300">
                            <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                        </div>
                        <div>
                            <h2 className="text-lg font-semibold text-gray-900 dark:text-white">Backup Database</h2>
                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                Download seluruh database sebagai file SQL.
                            </p>
                        </div>
                    </div>

                    <form onSubmit={handleBackup} className="flex flex-wrap items-end gap-3">
                        <div className="flex-1">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Konfirmasi Password
                            </label>
                            <input
                                type="password"
                                value={backupPassword}
                                onChange={(e) => setBackupPassword(e.target.value)}
                                className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                placeholder="Masukkan password admin"
                                required
                            />
                        </div>
                        <button
                            type="submit"
                            disabled={isBackingUp}
                            className="btn-primary"
                        >
                            {isBackingUp ? 'Membackup...' : 'Backup Sekarang'}
                        </button>
                    </form>
                    {backupError && <p className="text-sm text-red-600">{backupError}</p>}
                    {errors?.password && <p className="text-sm text-red-600">{errors.password}</p>}
                </div>

                {/* Restore Section */}
                <div className="card space-y-4">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300">
                            <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12L12 7.5m0 0L16.5 12M12 7.5V21" />
                            </svg>
                        </div>
                        <div>
                            <h2 className="text-lg font-semibold text-gray-900 dark:text-white">Restore Database</h2>
                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                Pulihkan database dari file backup SQL. Data saat ini akan ditimpa.
                            </p>
                        </div>
                    </div>

                    {!showRestoreConfirm ? (
                        <button
                            onClick={() => setShowRestoreConfirm(true)}
                            className="btn-secondary"
                        >
                            Restore Database
                        </button>
                    ) : (
                        <form onSubmit={handleRestore} className="space-y-3">
                            <div className="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700 dark:border-amber-900/50 dark:bg-amber-900/20 dark:text-amber-300">
                                <strong>Perhatian:</strong> Restore akan menimpa seluruh data yang ada. Pastikan Anda sudah melakukan backup terlebih dahulu.
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    File Backup (.sql)
                                </label>
                                <input
                                    ref={restoreFileRef}
                                    type="file"
                                    accept=".sql,.txt"
                                    onChange={(e) => setRestoreFile(e.target.files?.[0] || null)}
                                    className="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-navy-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-navy-700 hover:file:bg-navy-100 dark:file:bg-navy-700 dark:file:text-navy-200"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Konfirmasi Password Admin
                                </label>
                                <input
                                    type="password"
                                    value={restorePassword}
                                    onChange={(e) => setRestorePassword(e.target.value)}
                                    className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                    placeholder="Masukkan password admin"
                                    required
                                />
                            </div>

                            <div className="flex flex-wrap gap-2">
                                <button
                                    type="submit"
                                    disabled={isRestoring || !restoreFile}
                                    className="btn-primary"
                                >
                                    {isRestoring ? 'Merestore...' : 'Restore Sekarang'}
                                </button>
                                <button
                                    type="button"
                                    onClick={() => {
                                        setShowRestoreConfirm(false);
                                        setRestoreFile(null);
                                    }}
                                    className="btn-secondary"
                                >
                                    Batal
                                </button>
                            </div>
                        </form>
                    )}
                </div>

                {/* Reset Database Section */}
                <div className="card space-y-4">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-300">
                            <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </div>
                        <div>
                            <h2 className="text-lg font-semibold text-gray-900 dark:text-white">Hapus Data</h2>
                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                Hapus semua data siswa, kelas, dan transaksi. Data master (pengguna, sekolah, tier, quest) tetap aman.
                            </p>
                        </div>
                    </div>

                    {!showResetConfirm ? (
                        <button
                            onClick={() => setShowResetConfirm(true)}
                            className="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                        >
                            Hapus Semua Data
                        </button>
                    ) : (
                        <form onSubmit={handleReset} className="space-y-3">
                            <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300">
                                <strong>Peringatan!</strong> Tindakan ini tidak bisa dibatalkan. Semua data siswa, kelas, dan transaksi akan dihapus permanen. Disarankan backup terlebih dahulu.
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Ketik <strong>HAPUS</strong> untuk konfirmasi
                                </label>
                                <input
                                    type="text"
                                    value={confirmText}
                                    onChange={(e) => setConfirmText(e.target.value.toUpperCase().trim())}
                                    className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                    placeholder="HAPUS"
                                    autoComplete="off"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Konfirmasi Password Admin
                                </label>
                                <input
                                    type="password"
                                    value={resetPassword}
                                    onChange={(e) => setResetPassword(e.target.value)}
                                    className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                                    placeholder="Masukkan password admin"
                                    required
                                />
                            </div>

                            <div className="flex flex-wrap gap-2">
                                <button
                                    type="submit"
                                    disabled={isResetting || confirmText !== 'HAPUS'}
                                    className="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                                >
                                    {isResetting ? 'Menghapus...' : 'Ya, Hapus Semua Data'}
                                </button>
                                <button
                                    type="button"
                                    onClick={() => {
                                        setShowResetConfirm(false);
                                        setConfirmText('');
                                        setResetPassword('');
                                    }}
                                    className="btn-secondary"
                                >
                                    Batal
                                </button>
                            </div>
                        </form>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
