import { Head } from '@inertiajs/react';

import AdminLayout from '../../Layouts/AdminLayout';

const cards = [
    ['Resumen general', 'KPIs y métricas de salud', 'dashboardUrl', 'border-cyan-200 bg-cyan-50'],
    ['Control de usuarios', 'Gestión de accesos y perfiles', 'usersUrl', 'border-blue-200 bg-blue-50'],
    ['Roles y permisos', 'Configuración de privilegios', 'rolesUrl', 'border-emerald-200 bg-emerald-50'],
    ['Aprobación de médicos', 'Validar cédulas y perfiles profesionales', 'doctorsUrl', 'border-violet-200 bg-violet-50'],
    ['Uso de APIs de IA', 'Tokens, costos y estadísticas por proveedor', 'apiUsageUrl', 'border-amber-200 bg-amber-50'],
];

export default function Dashboard({ adminNavigation }) {
    return <AdminLayout adminNavigation={adminNavigation}>
        <Head title="Panel administrativo" />
        <section data-testid="admin-dashboard">
            <div className="mb-8"><h1 className="text-3xl font-extrabold text-slate-900">Panel de control</h1><p className="mt-2 text-slate-500">Visión general del sistema y accesos rápidos de administración.</p></div>
            <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">{cards.map(([title, description, urlKey, style]) => <a key={title} href={adminNavigation[urlKey]} className={`rounded-3xl border p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg ${style}`}><h2 className="text-lg font-bold text-slate-900">{title}</h2><p className="mt-2 text-sm text-slate-600">{description}</p></a>)}<div className="rounded-3xl border border-dashed border-slate-300 bg-white/60 p-6"><h2 className="text-lg font-bold text-slate-700">Auditoría de sistema</h2><span className="mt-2 inline-block rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Próximamente</span></div></div>
        </section>
    </AdminLayout>;
}
