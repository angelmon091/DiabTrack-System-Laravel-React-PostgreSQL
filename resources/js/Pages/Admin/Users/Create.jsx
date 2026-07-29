import { Head, useForm, usePage } from '@inertiajs/react';

import UserForm from '../../../Components/Admin/UserForm';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function Create({ roles, storeUrl, indexUrl }) {
    const { adminNavigation } = usePage().props;
    const form = useForm({ name: '', email: '', password: '', password_confirmation: '', is_admin: false, roles: [] });
    function submit(event) { event.preventDefault(); form.transform((data) => { const payload = { ...data }; if (!payload.is_admin) delete payload.is_admin; return payload; }); form.post(storeUrl, { preserveScroll: true }); }
    return <AdminLayout adminNavigation={adminNavigation}>
        <Head title="Crear usuario" />
        <section data-testid="user-create"><div className="mb-8"><a href={indexUrl} className="text-sm font-bold text-cyan-700">Volver al listado</a><h1 className="mt-4 text-3xl font-extrabold text-slate-900">Registrar nuevo usuario</h1><p className="mt-2 text-slate-500">Completa la información básica y asigna los privilegios correspondientes.</p></div><form onSubmit={submit} noValidate><UserForm form={form} roles={roles} indexUrl={indexUrl} submitLabel="Guardar usuario" passwordRequired /></form></section>
    </AdminLayout>;
}
