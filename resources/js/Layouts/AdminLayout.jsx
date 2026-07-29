import { useForm, usePage } from '@inertiajs/react';

import Alert from '../Components/Alert';
import BrandMark from '../Components/BrandMark';

export default function AdminLayout({ adminNavigation, children }) {
    const { auth, flash, navigation } = usePage().props;
    const logoutForm = useForm({});
    const items = [
        ['Dashboard', adminNavigation.dashboardUrl],
        ['Usuarios', adminNavigation.usersUrl],
        ['Roles y permisos', adminNavigation.rolesUrl],
        ['Aprobar médicos', adminNavigation.doctorsUrl],
        ['Uso de APIs', adminNavigation.apiUsageUrl],
    ];
    return <div className="min-h-screen bg-slate-100 text-slate-800">
        <header className="sticky top-0 z-20 border-b bg-white shadow-sm"><div className="flex items-center gap-4 px-5 py-3 lg:px-8"><a href={adminNavigation.dashboardUrl}><BrandMark className="text-xl" /></a><div className="ml-auto text-right"><strong className="block text-sm">{auth.user.name}</strong><span className="text-xs text-slate-500">Administrador</span></div><a href={navigation.profileUrl} className="rounded-xl px-3 py-2 text-sm font-semibold text-cyan-700 hover:bg-cyan-50">Perfil</a><form onSubmit={(event) => { event.preventDefault(); logoutForm.post(navigation.logoutUrl); }}><button disabled={logoutForm.processing} className="rounded-xl px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">Salir</button></form></div></header>
        <div className="mx-auto grid max-w-7xl gap-6 p-4 md:grid-cols-[220px_1fr] md:p-6 lg:p-8">
            <aside className="rounded-3xl bg-slate-900 p-4 text-white shadow-xl"><p className="mb-4 px-3 text-xs font-bold uppercase tracking-widest text-slate-400">Menú principal</p><nav className="flex gap-2 overflow-auto md:flex-col">{items.map(([label, url]) => <a key={label} href={url} className={`whitespace-nowrap rounded-xl px-3 py-2.5 text-sm font-semibold transition ${url === adminNavigation.dashboardUrl ? 'bg-cyan-500 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'}`}>{label}</a>)}</nav><p className="mt-8 hidden text-center text-xs text-slate-500 md:block">Protegido por DiabTrack Security</p></aside>
            <main><div className="mb-5 space-y-3"><Alert>{flash?.success || flash?.status}</Alert><Alert tone="error">{flash?.error}</Alert></div>{children}</main>
        </div>
    </div>;
}
