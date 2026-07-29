import { Head, router, usePage } from '@inertiajs/react';
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
            <div className="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><h1 className="text-3xl font-extrabold text-slate-900">Roles y privilegios</h1><p className="mt-2 text-slate-500">Administra los roles disponibles en el sistema y define sus alcances.</p></div><a href={createUrl} className="rounded-2xl bg-cyan-600 px-5 py-3 text-center text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:bg-cyan-700">Nuevo rol</a></div>
            <Table headers={['Nombre del rol', 'Descripción detallada', 'Usuarios activos', 'Fecha de registro', 'Acciones']}>
                {roles.data.length ? roles.data.map((role) => <tr key={role.id}><td className="px-5 py-4"><span className="rounded-full bg-cyan-50 px-3 py-2 text-sm font-bold capitalize text-cyan-700">{role.name}</span></td><td className="max-w-sm px-5 py-4 text-sm text-slate-600">{role.description || 'Sin descripción detallada'}</td><td className="px-5 py-4 font-bold text-slate-800">{role.usersCount}</td><td className="px-5 py-4 text-sm text-slate-500">{role.createdAt}</td><td className="px-5 py-4"><div className="flex gap-2"><a href={role.editUrl} className="rounded-xl bg-cyan-50 px-3 py-2 text-sm font-semibold text-cyan-700">Editar</a><button type="button" onClick={() => setSelectedRole(role)} className="rounded-xl bg-red-50 px-3 py-2 text-sm font-semibold text-red-700">Eliminar</button></div></td></tr>) : <tr><td colSpan="5" className="px-5 py-12 text-center text-slate-500">No se encontraron roles.</td></tr>}
            </Table>
            <Pagination links={roles.meta.links} />
        </section>
        <Modal open={Boolean(selectedRole)} title="Confirmar eliminación" onClose={() => setSelectedRole(null)} actions={selectedRole && <><button type="button" onClick={() => setSelectedRole(null)} className="rounded-xl border px-4 py-2 text-sm font-semibold">Cancelar</button><button type="button" onClick={destroyRole} disabled={selectedRole.usersCount > 0 || deleting} className="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50">{deleting ? 'Eliminando...' : selectedRole.usersCount > 0 ? 'Acción bloqueada' : 'Confirmar eliminación'}</button></>}>{selectedRole && <div><p className="text-slate-600">¿Eliminar el rol <strong className="capitalize">{selectedRole.name}</strong>?</p>{selectedRole.usersCount > 0 ? <div role="alert" className="mt-4 rounded-2xl bg-amber-50 p-4 text-sm text-amber-800">Este rol tiene <strong>{selectedRole.usersCount} usuarios</strong> asignados. No puede eliminarse mientras esté en uso.</div> : <p className="mt-3 text-sm text-slate-500">Esta acción removerá el privilegio del sistema.</p>}</div>}</Modal>
    </AdminLayout>;
}
