import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

import FormInput from '../../../Components/FormInput';
import FormTextarea from '../../../Components/FormTextarea';
import Modal from '../../../Components/Modal';
import SubmitButton from '../../../Components/SubmitButton';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function Edit({ role, updateUrl, indexUrl }) {
    const { adminNavigation } = usePage().props;
    const [confirmingDeletion, setConfirmingDeletion] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const form = useForm({ name: role.data.name, description: role.data.description ?? '' });
    function submit(event) { event.preventDefault(); form.put(updateUrl, { preserveScroll: true }); }
    function destroyRole() {
        if (role.data.usersCount > 0) return;
        setDeleting(true);
        router.delete(role.data.destroyUrl, { onFinish: () => { setDeleting(false); setConfirmingDeletion(false); } });
    }
    return <AdminLayout adminNavigation={adminNavigation}>
        <Head title="Editar rol" />
        <section data-testid="role-edit">
            <div className="mb-8"><a href={indexUrl} className="text-sm font-bold text-cyan-700">Volver al listado</a><div className="mt-4 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><h1 className="text-3xl font-extrabold text-slate-900">Editar rol: <span className="capitalize">{role.data.name}</span></h1><p className="mt-2 text-slate-500">Modifica el nombre y la descripción de este rol del sistema.</p></div><button type="button" onClick={() => setConfirmingDeletion(true)} className="rounded-2xl bg-red-600 px-5 py-3 text-sm font-bold text-white">Eliminar rol</button></div></div>
            <div className="mx-auto max-w-2xl rounded-3xl border border-slate-100 bg-white p-6 shadow-sm sm:p-8"><form onSubmit={submit} noValidate className="space-y-5">
                <FormInput id="name" name="name" label="Nombre del rol" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} error={form.errors.name} required />
                <FormTextarea id="description" name="description" label="Descripción (opcional)" rows={4} value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} placeholder="Breve descripción de los alcances funcionales de este rol..." error={form.errors.description} />
                <div className="flex flex-col-reverse gap-3 border-t pt-6 sm:flex-row sm:justify-end"><a href={indexUrl} className="rounded-2xl border px-5 py-3 text-center text-sm font-semibold text-slate-600">Cancelar</a><SubmitButton processing={form.processing} className="sm:w-auto sm:px-8">Guardar cambios</SubmitButton></div>
            </form></div>
        </section>
        <Modal open={confirmingDeletion} title="Confirmar eliminación" onClose={() => setConfirmingDeletion(false)} actions={<><button type="button" onClick={() => setConfirmingDeletion(false)} className="rounded-xl border px-4 py-2 text-sm font-semibold">Cancelar</button><button type="button" onClick={destroyRole} disabled={role.data.usersCount > 0 || deleting} className="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50">{deleting ? 'Eliminando...' : role.data.usersCount > 0 ? 'Acción bloqueada' : 'Confirmar eliminación'}</button></>}>
            <p className="text-slate-600">¿Eliminar el rol <strong className="capitalize">{role.data.name}</strong>?</p>{role.data.usersCount > 0 ? <div role="alert" className="mt-4 rounded-2xl bg-amber-50 p-4 text-sm text-amber-800">Este rol tiene <strong>{role.data.usersCount} usuarios</strong> asignados. No puede eliminarse mientras esté en uso.</div> : <p className="mt-3 text-sm text-slate-500">Esta acción removerá el privilegio del sistema.</p>}
        </Modal>
    </AdminLayout>;
}
