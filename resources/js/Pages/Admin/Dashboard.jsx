import { Head, Link } from '@inertiajs/react';
import ChartPie from 'lucide-react/dist/esm/icons/chart-pie.mjs';
import ClipboardList from 'lucide-react/dist/esm/icons/clipboard-list.mjs';
import Cpu from 'lucide-react/dist/esm/icons/cpu.mjs';
import ShieldCheck from 'lucide-react/dist/esm/icons/shield-check.mjs';
import Stethoscope from 'lucide-react/dist/esm/icons/stethoscope.mjs';
import Users from 'lucide-react/dist/esm/icons/users.mjs';

import AdminLayout from '../../Layouts/AdminLayout';

const primaryCards = [
    { title: 'Resumen general', description: 'KPIs y métricas de salud', urlKey: 'dashboardUrl', icon: ChartPie, tone: 'text-cyan-500' },
    { title: 'Control de usuarios', description: 'Gestión de accesos y perfiles', urlKey: 'usersUrl', icon: Users, tone: 'text-cyan-500' },
    { title: 'Roles y permisos', description: 'Configuración de privilegios', urlKey: 'rolesUrl', icon: ShieldCheck, tone: 'text-emerald-500' },
];

const secondaryCards = [
    { title: 'Aprobación de médicos', description: 'Validar cédulas y perfiles profesionales', urlKey: 'doctorsUrl', icon: Stethoscope, tone: 'text-sky-500' },
    { title: 'Uso de APIs de IA', description: 'Tokens, costos y estadísticas por proveedor', urlKey: 'apiUsageUrl', icon: Cpu, tone: 'text-cyan-500' },
];

function IconTile({ icon: Icon, tone, large = false }) {
    return <span className={`flex shrink-0 items-center justify-center rounded-2xl border border-slate-100 bg-white shadow-sm ${large ? 'h-[72px] w-[72px]' : 'h-14 w-14'}`}>
        <Icon size={large ? 32 : 25} strokeWidth={2.2} className={tone} />
    </span>;
}

export default function Dashboard({ adminNavigation }) {
    return <AdminLayout adminNavigation={adminNavigation}>
        <Head title="Panel administrativo" />
        <section data-testid="admin-dashboard">
            <div className="mb-10 border-b border-slate-200 pb-8">
                <h1 className="text-3xl font-extrabold text-slate-900">Panel de control</h1>
                <p className="mt-2 text-slate-600">Visión general del sistema y accesos rápidos de administración.</p>
            </div>

            <div className="grid gap-6 md:grid-cols-3">
                {primaryCards.map(({ title, description, urlKey, icon, tone }) => <Link key={title} href={adminNavigation[urlKey]} className="flex min-h-[240px] flex-col items-center justify-center rounded-3xl border border-slate-100 bg-white p-7 text-center shadow-[0_8px_30px_rgba(15,23,42,0.06)] transition hover:-translate-y-1 hover:border-cyan-200 hover:shadow-lg">
                    <IconTile icon={icon} tone={tone} large />
                    <h2 className="mt-6 text-lg font-bold text-slate-900">{title}</h2>
                    <p className="mt-1 text-xs text-slate-500">{description}</p>
                </Link>)}
            </div>

            <div className="mt-6 grid gap-6 md:grid-cols-2">
                {secondaryCards.map(({ title, description, urlKey, icon, tone }) => <Link key={title} href={adminNavigation[urlKey]} className="flex min-h-[112px] items-center gap-5 rounded-3xl border border-slate-100 bg-white p-6 shadow-[0_8px_30px_rgba(15,23,42,0.06)] transition hover:-translate-y-1 hover:border-cyan-200 hover:shadow-lg">
                    <IconTile icon={icon} tone={tone} />
                    <span><strong className="block text-base text-slate-900">{title}</strong><span className="mt-1 block text-xs text-slate-500">{description}</span></span>
                </Link>)}

                <div className="flex min-h-[112px] items-center gap-5 rounded-3xl border border-dashed border-slate-300 bg-white/50 p-6">
                    <IconTile icon={ClipboardList} tone="text-slate-400" />
                    <span><strong className="block text-base text-slate-700">Auditoría de sistema</strong><span className="mt-2 inline-block rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Próximamente</span></span>
                </div>
            </div>
        </section>
    </AdminLayout>;
}
