import { Head, Link, router, usePage } from '@inertiajs/react';
import Pen from 'lucide-react/dist/esm/icons/pen.mjs';
import Plus from 'lucide-react/dist/esm/icons/plus.mjs';
import Search from 'lucide-react/dist/esm/icons/search.mjs';
import Trash2 from 'lucide-react/dist/esm/icons/trash-2.mjs';
import TriangleAlert from 'lucide-react/dist/esm/icons/triangle-alert.mjs';
import UsersRound from 'lucide-react/dist/esm/icons/users-round.mjs';
import X from 'lucide-react/dist/esm/icons/x.mjs';
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
            <div className="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><h1 className="text-3xl font-extrabold text-slate-900">Control de Usuarios</h1><p className="mt-2 text-slate-500">Gestiona los accesos, roles y permisos de los integrantes del sistema.</p></div><Link href={createUrl} className="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:bg-cyan-700"><Plus size={18} />Nuevo Usuario</Link></div>
            <form onSubmit={submitSearch} className="mb-8 flex flex-col gap-3 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row"><label className="sr-only" htmlFor="user-search">Buscar usuarios</label><div className="relative min-w-0 flex-1"><Search className="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={18} /><input id="user-search" value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Buscar por nombre o correo electrónico..." className="w-full rounded-xl border-slate-300 py-2.5 pl-12 focus:border-cyan-500 focus:ring-cyan-500" /></div><button className="rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-bold text-white">Buscar</button>{filters.search && <Link href={indexUrl} aria-label="Limpiar búsqueda" className="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-slate-600"><X size={18} /></Link>}</form>
            <Table headers={['Usuario', 'Correo Electrónico', 'Tipo de Cuenta', 'Roles Asignados', 'Acciones']}>
                {users.data.length ? users.data.map((user) => <tr key={user.id}><td className="px-5 py-4"><div className="flex items-center gap-3"><span className="flex h-10 w-10 items-center justify-center rounded-full bg-cyan-50 font-bold text-cyan-700 shadow-sm">{user.initial}</span><div><strong className="block text-slate-900">{user.name}</strong>{user.isCurrent && <span className="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-500">Sesión actual</span>}</div></div></td><td className="px-5 py-4 text-sm text-slate-600">{user.email}</td><td className="px-5 py-4"><span className={`rounded-full px-3 py-1 text-xs font-bold ${user.isAdmin ? 'bg-red-50 text-red-700' : 'bg-cyan-50 text-cyan-700'}`}>{user.isAdmin ? 'Administrador' : 'Estándar'}</span></td><td className="px-5 py-4"><div className="flex flex-wrap gap-1">{user.roles.length ? user.roles.map((role) => <span key={role.id} className="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{role.name}</span>) : <span className="text-sm italic text-slate-400">Sin roles</span>}</div></td><td className="px-5 py-4"><div className="flex gap-2"><Link href={user.editUrl} title="Editar Perfil" aria-label={`Editar a ${user.name}`} className="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700 transition hover:bg-cyan-100"><Pen size={17} /></Link>{!user.isCurrent && <button type="button" title="Eliminar Usuario" aria-label={`Eliminar a ${user.name}`} onClick={() => setSelectedUser(user)} className="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-red-50 text-red-700 transition hover:bg-red-100"><Trash2 size={17} /></button>}</div></td></tr>) : <tr><td colSpan="5" className="px-5 py-12 text-center text-slate-500"><UsersRound className="mx-auto mb-4 opacity-25" size={64} /><h2 className="text-lg font-bold">No se encontraron usuarios</h2><p className="mt-1 text-sm">Intenta ajustar los criterios de búsqueda o limpia los filtros.</p>{filters.search && <div className="mt-4"><Link href={indexUrl} className="font-semibold text-cyan-700">Limpiar búsqueda</Link></div>}</td></tr>}
            </Table>
            <Pagination links={users.meta.links} />
        </section>
        <Modal open={Boolean(selectedUser)} title="Confirmar Eliminación" onClose={() => setSelectedUser(null)} actions={<><button type="button" onClick={() => setSelectedUser(null)} className="rounded-xl border px-4 py-2 text-sm font-semibold">Cancelar</button><button type="button" onClick={destroyUser} disabled={deleting} className="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">{deleting ? 'Eliminando...' : 'Eliminar Definitivamente'}</button></>}><div className="text-center"><TriangleAlert className="mx-auto mb-4 text-red-600 opacity-30" size={64} /><h2 className="mb-3 text-lg font-bold text-slate-900">¿Eliminar a {selectedUser?.name}?</h2><p className="text-slate-600">Esta acción es irreversible y se perderán todos los datos y registros asociados a este usuario permanentemente.</p></div></Modal>
    </AdminLayout>;
}
