import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import ArrowLeft from 'lucide-react/dist/esm/icons/arrow-left.mjs';
import Trash2 from 'lucide-react/dist/esm/icons/trash-2.mjs';
import TriangleAlert from 'lucide-react/dist/esm/icons/triangle-alert.mjs';
import { useState } from 'react';

import UserForm from '../../../Components/Admin/UserForm';
import Modal from '../../../Components/Modal';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function Edit({ user, roles, updateUrl, indexUrl }) {
    const { adminNavigation } = usePage().props;
    const currentUser = user.data;
    const form = useForm({ name: currentUser.name, email: currentUser.email, password: '', password_confirmation: '', is_admin: currentUser.isAdmin, roles: currentUser.roles.map((role) => role.id) });
    const [confirmingDelete, setConfirmingDelete] = useState(false);
    const [deleting, setDeleting] = useState(false);
    function submit(event) { event.preventDefault(); form.transform((data) => { const payload = { ...data }; if (!payload.is_admin) delete payload.is_admin; return payload; }); form.put(updateUrl, { preserveScroll: true }); }
    function destroyUser() { if (currentUser.isCurrent) return; setDeleting(true); router.delete(currentUser.destroyUrl, { onFinish: () => setDeleting(false) }); }
    return <AdminLayout adminNavigation={adminNavigation}>
        <Head title="Editar usuario" />
        <section data-testid="user-edit"><div className="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><Link href={indexUrl} className="inline-flex items-center gap-1 text-sm font-bold text-cyan-700"><ArrowLeft size={17} />Volver al listado</Link><h1 className="mt-4 text-3xl font-extrabold text-slate-900">Editar Usuario: {currentUser.name}</h1><p className="mt-2 text-slate-500">Actualiza la información personal y los permisos de acceso al sistema.</p></div>{!currentUser.isCurrent && <button type="button" onClick={() => setConfirmingDelete(true)} className="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white shadow-sm"><Trash2 size={17} />Eliminar Usuario</button>}</div><form onSubmit={submit} noValidate><UserForm form={form} roles={roles} indexUrl={indexUrl} submitLabel="Guardar Cambios" selfEditing={currentUser.isCurrent} /></form></section>
        <Modal open={confirmingDelete} title="Confirmar Eliminación" onClose={() => setConfirmingDelete(false)} actions={<><button type="button" onClick={() => setConfirmingDelete(false)} className="rounded-xl border px-4 py-2 text-sm font-semibold">Cancelar</button><button type="button" onClick={destroyUser} disabled={deleting} className="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">{deleting ? 'Eliminando...' : 'Eliminar Definitivamente'}</button></>}><div className="text-center"><TriangleAlert className="mx-auto mb-4 text-red-600 opacity-30" size={64} /><h2 className="mb-3 text-lg font-bold text-slate-900">¿Eliminar a {currentUser.name}?</h2><p className="text-slate-600">Esta acción es irreversible y se perderán todos los datos y registros asociados a este usuario permanentemente.</p></div></Modal>
    </AdminLayout>;
}
