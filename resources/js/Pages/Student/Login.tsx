import ApplicationLogo from '@/Components/ApplicationLogo';
import { Head, router, usePage } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

export default function StudentLogin({ prefillNis }: { prefillNis?: string }) {
    const { errors, flash, school } = usePage().props as any;
    const [nis, setNis] = useState(prefillNis || '');
    const [password, setPassword] = useState('');
    const [processing, setProcessing] = useState(false);

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        setProcessing(true);
        router.post(route('student.login.authenticate'), { nis, password }, {
            onFinish: () => setProcessing(false),
        });
    }

    return (
        <div className="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900">
            <div className="card mx-auto w-full max-w-md px-8 py-8">
                <div className="mb-8 text-center">
                    <div className="mx-auto mb-6 flex items-center justify-center">
                        <ApplicationLogo src={school?.logo_url} className="h-20 w-20" />
                    </div>
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Login Siswa</h1>
                    <p className="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Masukkan NIS dan password untuk login
                    </p>
                </div>

                {flash?.error && (
                    <div className="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">
                        {flash.error}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">NIS</label>
                        <input
                            type="text"
                            value={nis}
                            onChange={(e) => setNis(e.target.value)}
                            placeholder="Masukkan NIS..."
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                            required
                            autoFocus
                        />
                        {errors?.nis && <p className="mt-2 text-sm text-red-600">{errors.nis}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                        <input
                            type="password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            placeholder="Masukkan password (kosongkan jika scan QR)..."
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                        />
                        {errors?.password && <p className="mt-2 text-sm text-red-600">{errors.password}</p>}
                    </div>

                    <button type="submit" className="btn-primary w-full justify-center py-3 text-base" disabled={processing}>
                        {processing ? 'Memproses...' : 'Masuk'}
                    </button>
                </form>

                <div className="mt-8 text-center">
                    <a href={route('login')} className="text-sm text-gold-600 hover:text-gold-700 dark:text-gold-400">
                        Login untuk Petugas / Admin / Walas
                    </a>
                </div>
            </div>
        </div>
    );
}
