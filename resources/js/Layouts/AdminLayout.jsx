import { Link, useForm, usePage } from '@inertiajs/react';
import CircleUserRound from 'lucide-react/dist/esm/icons/circle-user-round.mjs';
import Cpu from 'lucide-react/dist/esm/icons/cpu.mjs';
import Gauge from 'lucide-react/dist/esm/icons/gauge.mjs';
import LogOut from 'lucide-react/dist/esm/icons/log-out.mjs';
import ShieldCheck from 'lucide-react/dist/esm/icons/shield-check.mjs';
import Stethoscope from 'lucide-react/dist/esm/icons/stethoscope.mjs';
import UsersRound from 'lucide-react/dist/esm/icons/users-round.mjs';

import Alert from '../Components/Alert';

function Brand() {
    return <span className="whitespace-nowrap text-[26px] font-black tracking-[-1.4px] text-slate-900 sm:text-[28px]">
        D<span className="text-cyan-500">ia</span>bTrack
    </span>;
}

function AdminAvatar({ user }) {
    if (user.avatar) {
        const source = user.avatar.startsWith('http') ? user.avatar : `/storage/${user.avatar}`;
        return <img src={source} alt="Usuario" className="h-full w-full object-cover" />;
    }

    return <span className="flex h-full w-full items-center justify-center bg-gradient-to-br from-blue-500 to-blue-700 text-white">
        <CircleUserRound size={25} strokeWidth={2.4} />
    </span>;
}

export default function AdminLayout({ adminNavigation, children }) {
    const { auth, flash, navigation } = usePage().props;
    const currentPath = usePage().url.split('?')[0];
    const logoutForm = useForm({});
    const items = [
        { label: 'Dashboard', mobileLabel: 'Inicio', url: adminNavigation.dashboardUrl, icon: Gauge, active: currentPath === adminNavigation.dashboardUrl },
        { label: 'Usuarios', mobileLabel: 'Usuarios', url: adminNavigation.usersUrl, icon: UsersRound, active: currentPath.startsWith(adminNavigation.usersUrl) },
        { label: 'Roles y permisos', mobileLabel: 'Roles', url: adminNavigation.rolesUrl, icon: ShieldCheck, active: currentPath.startsWith(adminNavigation.rolesUrl) },
        { label: 'Aprobar médicos', mobileLabel: 'Médicos', url: adminNavigation.doctorsUrl, icon: Stethoscope, active: currentPath.startsWith(adminNavigation.doctorsUrl) },
        { label: 'Uso de APIs', mobileLabel: 'APIs', url: adminNavigation.apiUsageUrl, icon: Cpu, active: currentPath.startsWith(adminNavigation.apiUsageUrl) },
    ];

    const submitLogout = (event) => {
        event.preventDefault();
        logoutForm.post(navigation.logoutUrl);
    };

    return <div className="flex min-h-screen min-w-0 flex-col overflow-x-clip bg-gradient-to-br from-slate-100 to-slate-200 text-slate-800">
        <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/95 shadow-[0_2px_8px_rgba(15,23,42,0.08)] backdrop-blur">
            <div className="flex h-[62px] min-w-0 items-center px-5 md:h-[72px] md:px-8 xl:px-12">
                <Link href={adminNavigation.dashboardUrl} className="shrink-0 no-underline" aria-label="Ir al dashboard administrativo"><Brand /></Link>

                <div className="ml-auto flex min-w-0 shrink-0 items-center">
                    <div className="flex h-[46px] min-w-0 items-center rounded-full border border-slate-200 bg-white py-1 pl-3 pr-2 shadow-sm">
                        <div className="mr-3 hidden max-w-[240px] text-right md:block">
                            <strong className="block truncate text-xs font-bold leading-4 text-slate-900">{auth.user.name}</strong>
                            <span className="block text-[10px] leading-4 text-slate-500">Administrador</span>
                        </div>
                        <Link href={navigation.profileUrl} className="block h-9 w-9 shrink-0 overflow-hidden rounded-full shadow-sm" aria-label="Abrir perfil">
                            <AdminAvatar user={auth.user} />
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

        <div className="flex min-w-0 flex-1 flex-col lg:flex-row">
            <aside className="z-20 w-full shrink-0 border-b border-white/60 bg-white/60 px-3 py-3 shadow-sm backdrop-blur lg:sticky lg:top-[72px] lg:h-[calc(100vh-72px)] lg:w-[280px] lg:border-b-0 lg:border-r lg:px-5 lg:py-8">
                <p className="mb-4 hidden px-4 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500 lg:block">Menú principal</p>
                <nav className="flex w-full gap-2 overflow-x-auto overscroll-x-contain pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden lg:flex-col lg:overflow-visible lg:pb-0" aria-label="Administración">
                    {items.map(({ label, mobileLabel, url, icon: Icon, active }) => <Link key={label} href={url} title={label} className={`flex shrink-0 items-center justify-center gap-2.5 whitespace-nowrap rounded-2xl px-3 py-2.5 text-xs font-semibold transition lg:w-full lg:justify-start lg:gap-4 lg:px-5 lg:py-3.5 lg:text-sm ${active ? 'bg-cyan-100 text-cyan-700' : 'text-slate-500 hover:bg-white hover:text-cyan-600 hover:shadow-sm'}`}>
                        <Icon size={19} strokeWidth={active ? 2.5 : 2} className="shrink-0" />
                        <span className="lg:hidden">{mobileLabel}</span>
                        <span className="hidden lg:inline">{label}</span>
                    </Link>)}
                </nav>
                <p className="mt-auto hidden pt-8 text-center text-[11px] text-slate-400 lg:block">Protegido por DiabTrack Security</p>
            </aside>

            <div className="min-w-0 flex-1 p-3 sm:p-5 lg:p-8 xl:px-12">
                <main className="mx-auto min-h-[calc(100vh-136px)] w-full min-w-0 max-w-[1400px] overflow-hidden rounded-[24px] border border-white/70 bg-white/75 p-4 shadow-[0_8px_32px_rgba(31,38,135,0.06)] backdrop-blur sm:p-6 lg:p-8 xl:p-12">
                    <div className="mb-5 space-y-3"><Alert>{flash?.success || flash?.status}</Alert><Alert tone="error">{flash?.error}</Alert></div>
                    {children}
                </main>
            </div>
        </div>
    </div>;
}
