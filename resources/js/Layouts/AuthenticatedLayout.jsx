import { Link, useForm, usePage } from '@inertiajs/react';
import BarChart3 from 'lucide-react/dist/esm/icons/bar-chart-3.mjs';
import CircleUserRound from 'lucide-react/dist/esm/icons/circle-user-round.mjs';
import Home from 'lucide-react/dist/esm/icons/home.mjs';
import LogOut from 'lucide-react/dist/esm/icons/log-out.mjs';
import Plus from 'lucide-react/dist/esm/icons/plus.mjs';

import Alert from '../Components/Alert';
import GlobalSearch from '../Components/GlobalSearch';
import NotificationMenu from '../Components/NotificationMenu';

const flashMessages = {
    'profile-updated': 'Perfil actualizado con éxito.',
    'email-change-requested': 'Solicitud enviada. Revisa tu nuevo correo.',
    'email-updated': 'Correo electrónico actualizado correctamente.',
    'password-updated': 'Contraseña actualizada.',
};

function Brand() {
    return <span className="whitespace-nowrap text-[26px] font-black tracking-[-1.4px] text-slate-900 sm:text-[28px]">
        D<span className="text-cyan-500">ia</span>bTrack
    </span>;
}

function UserAvatar({ user }) {
    if (user.avatar) {
        const source = user.avatar.startsWith('http') ? user.avatar : `/storage/${user.avatar}`;
        return <img src={source} alt="Usuario" className="h-full w-full object-cover" />;
    }

    return <span className="flex h-full w-full items-center justify-center bg-gradient-to-br from-blue-500 to-blue-700 text-white">
        <CircleUserRound size={25} strokeWidth={2.4} />
    </span>;
}

function PatientNavigation({ currentPath, navigation, mobile = false }) {
    const items = [
        { href: navigation.dashboardUrl, label: 'Inicio', icon: Home, active: currentPath === navigation.dashboardUrl },
        { href: navigation.summaryUrl, label: 'Resumen', icon: BarChart3, active: currentPath.startsWith(navigation.summaryUrl) },
        { href: navigation.vitalsCreateUrl, label: 'Nuevo', icon: Plus, active: currentPath.startsWith('/tracking/') && !currentPath.startsWith(navigation.summaryUrl) },
    ];

    if (mobile) {
        return <nav className="fixed inset-x-0 bottom-0 z-50 flex h-[66px] items-start justify-around rounded-t-[24px] border-t border-slate-200 bg-white px-2 pt-2 shadow-[0_-10px_25px_rgba(15,23,42,0.06)] md:hidden" aria-label="Navegación móvil">
            {items.map(({ href, label, icon: Icon, active }) => <Link key={label} href={href} className={`relative flex flex-1 flex-col items-center gap-0.5 text-[11px] font-semibold transition ${active ? 'text-cyan-500' : 'text-slate-500'}`}>
                <Icon size={22} strokeWidth={active ? 2.6 : 2} className={active ? '-translate-y-0.5' : ''} />
                <span>{label}</span>
            </Link>)}
        </nav>;
    }

    return <nav className="hidden h-[70px] items-stretch md:flex" aria-label="Navegación principal">
        {items.map(({ href, label, icon: Icon, active }) => <Link key={label} href={href} className={`relative flex min-w-[78px] flex-col items-center justify-center gap-0.5 px-3 text-[11px] font-medium transition ${active ? 'text-cyan-500' : 'text-slate-500 hover:text-cyan-500'}`}>
            <Icon size={21} strokeWidth={active ? 2.6 : 2} />
            <span>{label}</span>
            {active && <span className="absolute inset-x-4 bottom-0 h-[3px] rounded-t-full bg-cyan-500" />}
        </Link>)}
    </nav>;
}

export default function AuthenticatedLayout({ children }) {
    const { auth, flash, navigation, notifications = [] } = usePage().props;
    const currentPath = usePage().url.split('?')[0];
    const logoutForm = useForm({});
    const isPatient = auth.permissions.puedeBuscar;

    const submitLogout = (event) => {
        event.preventDefault();
        logoutForm.post(navigation.logoutUrl);
    };

    return <div className="flex min-h-screen min-w-0 flex-col overflow-x-clip bg-slate-50 text-slate-800">
        <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/95 shadow-[0_2px_8px_rgba(15,23,42,0.08)] backdrop-blur">
            <div className="flex h-[62px] min-w-0 items-center gap-3 px-5 md:grid md:h-[72px] md:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] md:gap-4 md:px-8 xl:px-12">
                <div className="flex min-w-0 items-center">
                    <Link href={navigation.dashboardUrl} className="shrink-0 no-underline" aria-label="Ir al inicio"><Brand /></Link>
                    {isPatient && navigation.searchUrl && <GlobalSearch searchUrl={navigation.searchUrl} />}
                </div>
                <div className="hidden md:block">
                    {isPatient && <PatientNavigation currentPath={currentPath} navigation={navigation} />}
                </div>

                <div className="ml-auto flex min-w-0 shrink-0 items-center gap-3 md:ml-0 md:justify-self-end">
                    <NotificationMenu notifications={notifications} navigation={navigation} />
                    <div className="flex h-[46px] min-w-0 items-center rounded-full border border-slate-200 bg-white py-1 pl-3 pr-2 shadow-sm">
                        <div className="mr-3 hidden max-w-[220px] text-right xl:block">
                            <strong className="block truncate text-xs font-bold leading-4 text-slate-900">{auth.user.name}</strong>
                            <span className="block truncate text-[10px] leading-4 text-slate-500">{auth.user.email}</span>
                        </div>
                        <Link href={navigation.profileUrl} className="block h-9 w-9 shrink-0 overflow-hidden rounded-full shadow-sm" aria-label="Abrir perfil">
                            <UserAvatar user={auth.user} />
                        </Link>
                        <form onSubmit={submitLogout} className="ml-2 border-l border-slate-200 pl-2">
                            <button type="submit" disabled={logoutForm.processing} className="flex h-8 w-7 items-center justify-center rounded-full text-red-500 transition hover:bg-red-50 disabled:opacity-50" title="Cerrar sesión" aria-label="Cerrar sesión">
                                <LogOut size={18} strokeWidth={2.2} />
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {(flash?.success || flash?.status || flash?.error) && <div className="w-full space-y-3 px-4 pt-4 sm:px-6 md:px-8 xl:px-12">
            <Alert>{flash?.success || flashMessages[flash?.status] || flash?.status}</Alert>
            <Alert tone="error">{flash?.error}</Alert>
        </div>}

        <main className="min-h-[calc(100vh-62px)] w-full min-w-0 flex-1 px-4 py-8 pb-24 sm:px-6 md:min-h-[calc(100vh-72px)] md:px-8 md:pb-8 xl:px-12">
            {children}
        </main>

        <footer className="border-t border-slate-200 bg-white py-10">
            <div className="mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-5 px-8 text-center md:flex-row md:text-left">
                <div><Brand /><div className="mt-3 flex gap-6 text-xs text-slate-500"><a href="#">Políticas</a><a href="#">Términos</a><a href="#">Ayuda</a></div></div>
                <p className="text-xs text-slate-500">© {new Date().getFullYear()} DiabTrack App. Cuidando tu salud.</p>
            </div>
        </footer>

        {isPatient && <PatientNavigation currentPath={currentPath} navigation={navigation} mobile />}
    </div>;
}
