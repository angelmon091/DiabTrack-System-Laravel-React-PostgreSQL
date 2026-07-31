import { Head, Link, useForm, usePage } from '@inertiajs/react';
import ArrowLeft from 'lucide-react/dist/esm/icons/arrow-left.mjs';

import FormInput from '../../../Components/FormInput';
import FormTextarea from '../../../Components/FormTextarea';
import SubmitButton from '../../../Components/SubmitButton';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function Create({ storeUrl, indexUrl }) {
    const { adminNavigation } = usePage().props;
    const form = useForm({ name: '', description: '' });
    function submit(event) { event.preventDefault(); form.post(storeUrl, { preserveScroll: true }); }
    return <AdminLayout adminNavigation={adminNavigation}>
        <Head title="Crear rol" />
        <section data-testid="role-create">
            <div className="mb-8"><Link href={indexUrl} className="inline-flex items-center gap-1 text-sm font-bold text-cyan-700"><ArrowLeft className="h-4 w-4" />Volver al listado</Link><h1 className="mt-4 text-3xl font-extrabold text-slate-900">Registrar Nuevo Rol</h1><p className="mt-2 text-slate-500">Define un nuevo nivel de acceso para agrupar permisos en el sistema DiabTrack.</p></div>
            <div className="mx-auto max-w-2xl rounded-3xl border border-slate-100 bg-white p-6 shadow-sm sm:p-8"><form onSubmit={submit} noValidate className="space-y-5">
                <FormInput id="name" name="name" label="Nombre del rol" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} placeholder="Ej. Nutricionista, Médico, VIP..." error={form.errors.name} required />
                <FormTextarea id="description" name="description" label="Descripción (opcional)" rows={4} value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} placeholder="Breve descripción de los privilegios y alcances de este rol..." error={form.errors.description} />
                <div className="flex flex-col-reverse gap-3 border-t pt-6 sm:flex-row sm:justify-end"><a href={indexUrl} className="rounded-2xl border px-5 py-3 text-center text-sm font-semibold text-slate-600">Cancelar</a><SubmitButton processing={form.processing} className="sm:w-auto sm:px-8">Crear rol</SubmitButton></div>
            </form></div>
        </section>
    </AdminLayout>;
}
