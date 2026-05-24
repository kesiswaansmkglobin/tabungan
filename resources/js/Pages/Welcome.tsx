import { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome({ auth }: PageProps) {
    const { school } = usePage().props as any;

    return (
        <>
            <Head title="Beranda" />

            <div className="flex min-h-screen flex-col bg-white">
                {/* Header */}
                <header className="sticky top-0 z-50 border-b border-gray-100 bg-white/80 backdrop-blur-md">
                    <div className="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
                        <div className="flex items-center gap-3">
                            {school?.logo_url ? (
                                <img src={school.logo_url} alt="Logo" className="h-8 w-8 rounded object-contain" />
                            ) : (
                                <div className="flex h-8 w-8 items-center justify-center rounded bg-navy-500 text-xs font-bold text-white">
                                    {school?.name?.charAt(0) || 'S'}
                                </div>
                            )}
                            <span className="text-sm font-semibold text-gray-900">
                                {school?.name || 'Tabungan Siswa'}
                            </span>
                        </div>
                        <nav className="flex items-center gap-4">
                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="rounded-lg bg-navy-500 px-5 py-2 text-sm font-medium text-white transition hover:bg-navy-600"
                                >
                                    Dashboard
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('student.login')}
                                        className="rounded-lg bg-navy-500 px-5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-navy-600"
                                    >
                                        Masuk
                                    </Link>
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                {/* Hero */}
                <section className="relative overflow-hidden">
                    <div className="absolute inset-0 bg-gradient-to-br from-navy-500 via-navy-600 to-navy-800" />
                    <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(212,165,32,0.15),transparent_50%)]" />
                    <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(255,255,255,0.05),transparent_50%)]" />

                    <div className="relative mx-auto max-w-6xl px-6 py-24 sm:py-32">
                        <div className="mx-auto max-w-3xl text-center">
                            {school?.logo_url && (
                                <div className="mb-8 flex justify-center">
                                    <img src={school.logo_url} alt="Logo" className="h-20 w-20 rounded-2xl object-contain ring-4 ring-white/10" />
                                </div>
                            )}
                            <h1 className="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                                Kelola Tabungan Siswa
                                <span className="mt-2 block text-gold-400">Secara Digital</span>
                            </h1>
                            <p className="mt-6 text-lg leading-relaxed text-navy-100 sm:text-xl">
                                Catat setoran dan penarikan dengan mudah, pantau saldo secara real-time,
                                dan hasilkan laporan PDF/Excel dalam satu klik.
                            </p>
                            <div className="mt-10 flex flex-wrap items-center justify-center gap-4">
                                <Link
                                    href={route('student.login')}
                                    className="inline-flex items-center gap-2 rounded-xl bg-gold-500 px-8 py-3.5 text-base font-semibold text-navy-900 shadow-lg shadow-gold-500/25 transition hover:bg-gold-400 hover:shadow-gold-500/40"
                                >
                                    Masuk Aplikasi
                                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" strokeWidth="2.5" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </Link>
                                <Link
                                    href={route('login')}
                                    className="inline-flex items-center gap-2 rounded-xl border border-navy-400 bg-white/5 px-8 py-3.5 text-base font-medium text-white backdrop-blur-sm transition hover:bg-white/10"
                                >
                                    Login Petugas
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div className="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-white to-transparent" />
                </section>

                {/* Features */}
                <section className="border-b border-gray-100 bg-white py-20">
                    <div className="mx-auto max-w-6xl px-6">
                        <div className="mx-auto max-w-2xl text-center">
                            <h2 className="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
                                Fitur Unggulan
                            </h2>
                            <p className="mt-3 text-base text-gray-500">
                                Semua yang Anda butuhkan untuk mengelola tabungan siswa
                            </p>
                        </div>

                        <div className="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                            <div className="group rounded-xl border border-gray-100 bg-white p-6 transition hover:border-navy-200 hover:shadow-sm">
                                <div className="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-navy-50 text-navy-600 group-hover:bg-navy-500 group-hover:text-white transition">
                                    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.5m-1.5 0a.75.75 0 01-.75-.75v-7.5a.75.75 0 01.75-.75h2.25a.75.75 0 01.75.75v7.5a.75.75 0 01-.75.75h-2.25z" />
                                    </svg>
                                </div>
                                <h3 className="text-base font-semibold text-gray-900">Manajemen Transaksi</h3>
                                <p className="mt-2 text-sm leading-relaxed text-gray-500">
                                    Catat setoran dan penarikan dalam hitungan detik. Saldo siswa terupdate otomatis setiap kali transaksi terjadi.
                                </p>
                            </div>

                            <div className="group rounded-xl border border-gray-100 bg-white p-6 transition hover:border-navy-200 hover:shadow-sm">
                                <div className="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-navy-50 text-navy-600 group-hover:bg-navy-500 group-hover:text-white transition">
                                    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                    </svg>
                                </div>
                                <h3 className="text-base font-semibold text-gray-900">Laporan & Analitik</h3>
                                <p className="mt-2 text-sm leading-relaxed text-gray-500">
                                    Ekspor laporan PDF dan Excel dengan filter kelas, tanggal, dan jenis. Cetak buku tabungan siswa secara individu.
                                </p>
                            </div>

                            <div className="group rounded-xl border border-gray-100 bg-white p-6 transition hover:border-navy-200 hover:shadow-sm">
                                <div className="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-navy-50 text-navy-600 group-hover:bg-navy-500 group-hover:text-white transition">
                                    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0016.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 01-2.77.896m0 0a6.06 6.06 0 01-3 .896m0 0a6.06 6.06 0 01-3-.896m0 0a6.023 6.023 0 01-2.77-.896" />
                                    </svg>
                                </div>
                                <h3 className="text-base font-semibold text-gray-900">Gamifikasi</h3>
                                <p className="mt-2 text-sm leading-relaxed text-gray-500">
                                    Siswa mendapatkan XP dan naik tier setiap kali menabung. Mendorong kebiasaan menabung sejak dini.
                                </p>
                            </div>

                            <div className="group rounded-xl border border-gray-100 bg-white p-6 transition hover:border-navy-200 hover:shadow-sm">
                                <div className="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-navy-50 text-navy-600 group-hover:bg-navy-500 group-hover:text-white transition">
                                    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                </div>
                                <h3 className="text-base font-semibold text-gray-900">Portal Siswa</h3>
                                <p className="mt-2 text-sm leading-relaxed text-gray-500">
                                    Siswa dapat login menggunakan NIS atau QR code untuk melihat saldo dan riwayat transaksi mereka sendiri.
                                </p>
                            </div>

                            <div className="group rounded-xl border border-gray-100 bg-white p-6 transition hover:border-navy-200 hover:shadow-sm">
                                <div className="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-navy-50 text-navy-600 group-hover:bg-navy-500 group-hover:text-white transition">
                                    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                </div>
                                <h3 className="text-base font-semibold text-gray-900">Impor Data Excel</h3>
                                <p className="mt-2 text-sm leading-relaxed text-gray-500">
                                    Impor data siswa secara massal dari file Excel menggunakan template yang sudah disediakan.
                                </p>
                            </div>

                            <div className="group rounded-xl border border-gray-100 bg-white p-6 transition hover:border-navy-200 hover:shadow-sm">
                                <div className="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-navy-50 text-navy-600 group-hover:bg-navy-500 group-hover:text-white transition">
                                    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                </div>
                                <h3 className="text-base font-semibold text-gray-900">Aman & Terpercaya</h3>
                                <p className="mt-2 text-sm leading-relaxed text-gray-500">
                                    Semua transaksi dicatat dengan audit log. Data tersimpan aman dan dapat dilacak kapan saja.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Stats */}
                <section className="bg-navy-500 py-16">
                    <div className="mx-auto max-w-6xl px-6">
                        <div className="grid gap-8 text-center sm:grid-cols-3">
                            <div>
                                <p className="text-3xl font-bold text-white">Real-time</p>
                                <p className="mt-1 text-sm text-navy-200">Pembaruan saldo otomatis</p>
                            </div>
                            <div>
                                <p className="text-3xl font-bold text-white">Multi-peran</p>
                                <p className="mt-1 text-sm text-navy-200">Admin, Staff, Wali Kelas</p>
                            </div>
                            <div>
                                <p className="text-3xl font-bold text-white">Laporan</p>
                                <p className="mt-1 text-sm text-navy-200">PDF & Excel siap unduh</p>
                            </div>
                        </div>
                    </div>
                </section>

                {/* CTA */}
                <section className="bg-white py-20">
                    <div className="mx-auto max-w-6xl px-6 text-center">
                        <h2 className="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
                            Siap Mengelola Tabungan Siswa?
                        </h2>
                        <p className="mt-3 text-base text-gray-500">
                            Mulai sekarang dan nikmati kemudahan pencatatan tabungan secara digital.
                        </p>
                        <div className="mt-8 flex flex-wrap items-center justify-center gap-4">
                            <Link
                                href={route('student.login')}
                                className="inline-flex items-center gap-2 rounded-xl bg-navy-500 px-8 py-3.5 text-base font-semibold text-white shadow-sm transition hover:bg-navy-600"
                            >
                                Masuk Aplikasi
                            </Link>
                            <Link
                                href={route('login')}
                                className="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-8 py-3.5 text-base font-medium text-gray-700 transition hover:bg-gray-50"
                            >
                                Login Petugas
                            </Link>
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="border-t border-gray-100 bg-gray-50 px-6 py-8">
                    <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 sm:flex-row">
                        <div className="flex items-center gap-2 text-sm text-gray-400">
                            {school?.logo_url && (
                                <img src={school.logo_url} alt="Logo" className="h-5 w-5 rounded object-contain" />
                            )}
                            &copy; {new Date().getFullYear()} {school?.name || 'SMK Globin'}
                        </div>
                        <p className="text-xs text-gray-400">
                            Sistem Tabungan Siswa
                        </p>
                    </div>
                </footer>
            </div>
        </>
    );
}
