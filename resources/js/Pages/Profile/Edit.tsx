import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

export default function Edit({
    mustVerifyEmail,
    status,
}: PageProps<{ mustVerifyEmail: boolean; status?: string }>) {
    const { auth } = usePage().props as any;
    const isAdmin = auth.user?.primary_role === 'admin';

    return (
        <AuthenticatedLayout>
            <Head title="Profile" />

            <div className="space-y-6">
                <div className="card">
                    <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Profile Information</h2>
                    <UpdateProfileInformationForm
                        mustVerifyEmail={mustVerifyEmail}
                        status={status}
                        className="max-w-xl"
                    />
                </div>

                {isAdmin && (
                    <>
                        <div className="card">
                            <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Update Password</h2>
                            <UpdatePasswordForm className="max-w-xl" />
                        </div>

                        <div className="card">
                            <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Delete Account</h2>
                            <DeleteUserForm className="max-w-xl" />
                        </div>
                    </>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
