import { Head, Link, router, usePage } from '@inertiajs/react';
import Pen from 'lucide-react/dist/esm/icons/pen.mjs';
import Plus from 'lucide-react/dist/esm/icons/plus.mjs';
import ShieldAlert from 'lucide-react/dist/esm/icons/shield-alert.mjs';
import Trash2 from 'lucide-react/dist/esm/icons/trash-2.mjs';
import TriangleAlert from 'lucide-react/dist/esm/icons/triangle-alert.mjs';
import Users from 'lucide-react/dist/esm/icons/users.mjs';
import { useState } from 'react';

import Modal from '../../../Components/Modal';
import Pagination from '../../../Components/Pagination';
import Table from '../../../Components/Table';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function Index({ roles, createUrl }) {
    const { adminNavigation } = usePage().props;
    const [selectedRole, setSelectedRole] = useState(null);
    const [deleting, setDeleting] = useState(false);
    function destroyRole() {
        if (!selectedRole || selectedRole.usersCount > 0) return;
        setDeleting(true);
        router.delete(selectedRole.destroyUrl, { preserveScroll: true, onFinish: () => { setDeleting(false); setSelectedRole(null); } });
    }
    return <AdminLayout adminNavigation={adminNavigation}>
        <Head title="Control de roles" />
        <section data-testid="roles-index">
            <div className="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><h1 className="text-3xl font-extrabold text-slate-900">Roles y Privilegios</h1><p className="mt-2 text-slate-500">Administra los roles disponibles en el sistema y define sus alcances.</p></div><Link href={createUrl} className="inline-flex items-center justify-center gap-2 rounded-2xl bg-cyan-600 px-5 py-3 text-center text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:bg-cyan-700"><Plus className="h-4 w-4" />Nuevo Rol</Link></div>
            <Table headers={['Nombre del rol', 'Descripción detallada', 'Usuarios activos', 'Fecha de registro', 'Acciones']}>
                {roles.data.length ? roles.data.map((role) => <tr key={role.id}><td className="px-5 py-4"><span className="rounded-full bg-cyan-50 px-3 py-2 text-sm font-bold capitalize text-cyan-700">{role.name}</span></td><td className="max-w-sm px-5 py-4 text-sm text-slate-600">{role.description || 'Sin descripción detallada'}</td><td className="px-5 py-4"><span className="inline-flex items-center gap-2 font-bold text-slate-800"><span className="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-500"><Users className="h-4 w-4" /></span>{role.usersCount}</span></td><td className="px-5 py-4 text-sm text-slate-500">{role.createdAt}</td><td className="px-5 py-4"><div className="flex gap-2"><Link href={role.editUrl} aria-label={`Editar rol ${role.name}`} title="Editar Rol" className="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700"><Pen className="h-4 w-4" /></Link><button type="button" aria-label={`Eliminar rol ${role.name}`} title="Eliminar Rol" onClick={() => setSelectedRole(role)} className="flex h-9 w-9 items-center justify-center rounded-xl bg-red-50 text-red-700"><Trash2 className="h-4 w-4" /></button></div></td></tr>) : <tr><td colSpan="5" className="px-5 py-12 text-center text-slate-500">No se encontraron roles.</td></tr>}
            </Table>
            <Pagination links={roles.meta.links} />
        </section>
        <Modal open={Boolean(selectedRole)} title="Confirmar Eliminación" onClose={() => setSelectedRole(null)} actions={selectedRole && <><button type="button" onClick={() => setSelectedRole(null)} className="rounded-xl border px-4 py-2 text-sm font-semibold">Cancelar</button><button type="button" onClick={destroyRole} disabled={selectedRole.usersCount > 0 || deleting} className="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50">{deleting ? 'Eliminando...' : selectedRole.usersCount > 0 ? 'Acción Bloqueada' : 'Confirmar Eliminación'}</button></>}>{selectedRole && <div className="text-center"><ShieldAlert className="mx-auto mb-4 h-16 w-16 text-red-200" /><h3 className="font-bold">¿Eliminar el rol <span className="capitalize">{selectedRole.name}</span>?</h3>{selectedRole.usersCount > 0 ? <div role="alert" className="mt-4 flex gap-3 rounded-2xl bg-amber-50 p-4 text-left text-sm text-amber-800"><TriangleAlert className="h-6 w-6 shrink-0" /><span><strong className="block">Rol en uso activo</strong>Este rol tiene <strong>{selectedRole.usersCount} usuarios</strong> asignados. No puede eliminarse mientras esté en uso.</span></div> : <p className="mt-3 text-sm text-slate-500">Esta acción removerá el privilegio del sistema. Asegúrate de que no sea necesario para futuras configuraciones.</p>}</div>}</Modal>
    </AdminLayout>;
}
