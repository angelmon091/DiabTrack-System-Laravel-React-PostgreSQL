import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

import Modal from '../../../Components/Modal';
import Pagination from '../../../Components/Pagination';
import Table from '../../../Components/Table';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function Index({ users, filters, createUrl, indexUrl }) {
    const { adminNavigation } = usePage().props;
    const [search, setSearch] = useState(filters.search || '');
    const [selectedUser, setSelectedUser] = useState(null);
    const [deleting, setDeleting] = useState(false);
    function submitSearch(event) { event.preventDefault(); router.get(indexUrl, search ? { search } : {}, { preserveState: true, replace: true }); }
    function destroyUser() {
        if (!selectedUser || selectedUser.isCurrent) return;
        setDeleting(true);
        router.delete(selectedUser.destroyUrl, { preserveScroll: true, onFinish: () => { setDeleting(false); setSelectedUser(null); } });
    }
    return <AdminLayout adminNavigation={adminNavigation}>
        <Head title="Control de usuarios" />
        <section data-testid="users-index">
            <div className="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><h1 className="text-3xl font-extrabold text-slate-900">Control de usuarios</h1><p className="mt-2 text-slate-500">Gestiona los accesos, roles y permisos de los integrantes del sistema.</p></div><Link href={createUrl} className="rounded-2xl bg-cyan-600 px-5 py-3 text-center text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:bg-cyan-700">Nuevo usuario</Link></div>
            <form onSubmit={submitSearch} className="mb-8 flex flex-col gap-3 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row"><label className="sr-only" htmlFor="user-search">Buscar usuarios</label><input id="user-search" value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Buscar por nombre o correo electrónico..." className="min-w-0 flex-1 rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-cyan-500" /><button className="rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-bold text-white">Buscar</button>{filters.search && <Link href={indexUrl} className="rounded-xl border border-slate-300 px-5 py-2.5 text-center text-sm font-semibold text-slate-600">Limpiar</Link>}</form>
            <Table headers={['Usuario', 'Correo electrónico', 'Tipo de cuenta', 'Roles asignados', 'Acciones']}>
                {users.data.length ? users.data.map((user) => <tr key={user.id}><td className="px-5 py-4"><div className="flex items-center gap-3"><span className="flex h-10 w-10 items-center justify-center rounded-full bg-cyan-50 font-bold text-cyan-700">{user.initial}</span><div><strong className="block text-slate-900">{user.name}</strong>{user.isCurrent && <span className="text-xs font-bold uppercase text-slate-500">Sesión actual</span>}</div></div></td><td className="px-5 py-4 text-sm text-slate-600">{user.email}</td><td className="px-5 py-4"><span className={`rounded-full px-3 py-1 text-xs font-bold ${user.isAdmin ? 'bg-red-50 text-red-700' : 'bg-cyan-50 text-cyan-700'}`}>{user.isAdmin ? 'Administrador' : 'Estándar'}</span></td><td className="px-5 py-4"><div className="flex flex-wrap gap-1">{user.roles.length ? user.roles.map((role) => <span key={role.id} className="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{role.name}</span>) : <span className="text-sm italic text-slate-400">Sin roles</span>}</div></td><td className="px-5 py-4"><div className="flex gap-2"><Link href={user.editUrl} className="rounded-xl bg-cyan-50 px-3 py-2 text-sm font-semibold text-cyan-700">Editar</Link>{!user.isCurrent && <button type="button" onClick={() => setSelectedUser(user)} className="rounded-xl bg-red-50 px-3 py-2 text-sm font-semibold text-red-700">Eliminar</button>}</div></td></tr>) : <tr><td colSpan="5" className="px-5 py-12 text-center text-slate-500">No se encontraron usuarios.{filters.search && <div className="mt-4"><Link href={indexUrl} className="font-semibold text-cyan-700">Limpiar búsqueda</Link></div>}</td></tr>}
            </Table>
            <Pagination links={users.meta.links} />
        </section>
        <Modal open={Boolean(selectedUser)} title="Confirmar eliminación" onClose={() => setSelectedUser(null)} actions={<><button type="button" onClick={() => setSelectedUser(null)} className="rounded-xl border px-4 py-2 text-sm font-semibold">Cancelar</button><button type="button" onClick={destroyUser} disabled={deleting} className="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">{deleting ? 'Eliminando...' : 'Eliminar definitivamente'}</button></>}><p className="text-slate-600">¿Eliminar a <strong>{selectedUser?.name}</strong>? Esta acción es irreversible y eliminará sus datos asociados.</p></Modal>
    </AdminLayout>;
}
