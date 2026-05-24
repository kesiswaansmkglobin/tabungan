import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

interface School {
    id: number;
    name: string;
    headmaster_name: string | null;
    treasurer_name: string | null;
    logo_path: string | null;
    signature_path: string | null;
    treasurer_signature_path: string | null;
    logo_url?: string | null;
    signature_url?: string | null;
    treasurer_signature_url?: string | null;
}

export default function School({ school }: { school: School }) {
    const { errors, flash } = usePage().props as any;
    const [form, setForm] = useState({
        name: school.name || '',
        headmaster_name: school.headmaster_name || '',
        treasurer_name: school.treasurer_name || '',
    });
    const [logo, setLogo] = useState<File | null>(null);
    const [signature, setSignature] = useState<File | null>(null);
    const [treasurerSignature, setTreasurerSignature] = useState<File | null>(null);
    const [processing, setProcessing] = useState(false);

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        setProcessing(true);
        const data = new FormData();
        data.append('name', form.name);
        if (form.headmaster_name) data.append('headmaster_name', form.headmaster_name);
        if (form.treasurer_name) data.append('treasurer_name', form.treasurer_name);
        if (logo) data.append('logo', logo);
        if (signature) data.append('signature', signature);
        if (treasurerSignature) data.append('treasurer_signature', treasurerSignature);

        router.post(route('admin.school.update'), data, {
            forceFormData: true,
            onFinish: () => setProcessing(false),
        });
    }

    function deleteImage(type: string) {
        if (!confirm(`Hapus gambar ini?`)) return;
        setProcessing(true);
        router.delete(route('admin.school.image.delete', type), {
            onFinish: () => setProcessing(false),
        });
    }

    return (
        <AuthenticatedLayout>
            <Head title="Data Sekolah" />

            <div className="space-y-6">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Data Sekolah</h1>

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

                <form onSubmit={handleSubmit} className="card max-w-2xl space-y-6">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Sekolah</label>
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
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Kepala Sekolah</label>
                        <input
                            type="text"
                            value={form.headmaster_name}
                            onChange={(e) => setForm({ ...form, headmaster_name: e.target.value })}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Bendahara</label>
                        <input
                            type="text"
                            value={form.treasurer_name}
                            onChange={(e) => setForm({ ...form, treasurer_name: e.target.value })}
                            className="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gold-500 focus:ring-gold-500 dark:border-gray-600 dark:bg-navy-800 dark:text-white"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Logo Sekolah</label>
                        {school.logo_url && (
                            <div className="relative mb-2 inline-block">
                                <img src={school.logo_url} alt="Logo" className="h-16 w-16 object-contain" loading="lazy" />
                                <button
                                    type="button"
                                    onClick={() => deleteImage('logo')}
                                    className="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white hover:bg-red-600"
                                >
                                    ×
                                </button>
                            </div>
                        )}
                        <input
                            type="file"
                            accept="image/jpeg,image/png"
                            onChange={(e) => setLogo(e.target.files?.[0] || null)}
                            className="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-navy-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-navy-700 hover:file:bg-navy-100 dark:file:bg-navy-700 dark:file:text-navy-200"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanda Tangan Kepala Sekolah</label>
                        {school.signature_url && (
                            <div className="relative mb-2 inline-block">
                                <img src={school.signature_url} alt="Signature" className="h-16 w-16 object-contain" loading="lazy" />
                                <button
                                    type="button"
                                    onClick={() => deleteImage('signature')}
                                    className="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white hover:bg-red-600"
                                >
                                    ×
                                </button>
                            </div>
                        )}
                        <input
                            type="file"
                            accept="image/jpeg,image/png"
                            onChange={(e) => setSignature(e.target.files?.[0] || null)}
                            className="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-navy-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-navy-700 hover:file:bg-navy-100 dark:file:bg-navy-700 dark:file:text-navy-200"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanda Tangan Bendahara</label>
                        {school.treasurer_signature_url && (
                            <div className="relative mb-2 inline-block">
                                <img src={school.treasurer_signature_url} alt="Treasurer Signature" className="h-16 w-16 object-contain" loading="lazy" />
                                <button
                                    type="button"
                                    onClick={() => deleteImage('treasurer_signature')}
                                    className="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white hover:bg-red-600"
                                >
                                    ×
                                </button>
                            </div>
                        )}
                        <input
                            type="file"
                            accept="image/jpeg,image/png"
                            onChange={(e) => setTreasurerSignature(e.target.files?.[0] || null)}
                            className="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-navy-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-navy-700 hover:file:bg-navy-100 dark:file:bg-navy-700 dark:file:text-navy-200"
                        />
                    </div>

                    <div className="flex justify-end">
                        <button type="submit" className="btn-primary" disabled={processing}>
                            {processing ? 'Menyimpan...' : 'Simpan'}
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
