import { Head, Link, useForm, usePage } from '@inertiajs/react';
import CircleCheck from 'lucide-react/dist/esm/icons/circle-check.mjs';
import Filter from 'lucide-react/dist/esm/icons/filter.mjs';
import Stethoscope from 'lucide-react/dist/esm/icons/stethoscope.mjs';
import X from 'lucide-react/dist/esm/icons/x.mjs';
import { useState } from 'react';

import FormTextarea from '../../../Components/FormTextarea';
import Modal from '../../../Components/Modal';
import Pagination from '../../../Components/Pagination';
import AdminLayout from '../../../Layouts/AdminLayout';

const statusStyles = { pending: 'bg-amber-50 text-amber-800', approved: 'bg-emerald-50 text-emerald-700', rejected: 'bg-red-50 text-red-700' };
const statusLabels = { pending: 'Pendiente', approved: 'Aprobado', rejected: 'Rechazado' };

export default function Index({ doctors, filters, pendingCount, indexUrl, statusOptions }) {
    const { adminNavigation } = usePage().props;
    const [action, setAction] = useState(null);
    const form = useForm({ review_notes: '' });
    function closeModal() { if (!form.processing) { setAction(null); form.reset(); form.clearErrors(); } }
    function submitAction() {
        if (!action) return;
        const url = action.type === 'approve' ? action.doctor.approveUrl : action.doctor.rejectUrl;
        form.patch(url, { preserveScroll: true, onSuccess: () => { setAction(null); form.reset(); form.clearErrors(); } });
    }
    return <AdminLayout adminNavigation={adminNavigation}>
        <Head title="Aprobación de médicos" />
        <section data-testid="doctors-index">
            <div className="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><h1 className="text-3xl font-extrabold text-slate-900">Aprobación de médicos</h1><p className="mt-2 text-slate-500">Comprueba identidad, cédula y especialidad antes de habilitar la vinculación de pacientes.</p></div><span className="self-start rounded-full bg-amber-100 px-4 py-2 text-sm font-bold text-amber-800">{pendingCount} pendientes</span></div>
            <nav aria-label="Estado de solicitudes" className="mb-6 flex flex-col justify-between gap-4 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm lg:flex-row lg:items-center"><div className="flex items-center gap-3"><span className="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700"><Filter size={19} /></span><div><h2 className="font-bold text-slate-900">Filtrar solicitudes</h2><p className="text-xs text-slate-500">Consulta los perfiles por estado de validación</p></div></div><div className="flex flex-wrap gap-2">{statusOptions.map((option) => <Link key={option.value} href={indexUrl} data={{ status: option.value }} preserveState className={`rounded-lg border px-4 py-2 text-sm font-semibold ${filters.status === option.value ? 'border-cyan-600 bg-cyan-600 text-white' : 'border-slate-300 text-slate-600 hover:bg-cyan-50'}`}>{option.label}</Link>)}</div></nav>
            <div className="grid gap-6 xl:grid-cols-2">{doctors.data.length ? doctors.data.map((doctor) => <article key={doctor.id} className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><header className="mb-5 flex items-start justify-between gap-3"><div className="flex min-w-0 items-center gap-3"><span className="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600"><Stethoscope size={24} /></span><div className="min-w-0"><h2 className="truncate text-lg font-bold text-slate-900">{doctor.name}</h2><p className="truncate text-sm text-slate-500">{doctor.email}</p></div></div><span className={`shrink-0 rounded-full px-3 py-1 text-xs font-bold ${statusStyles[doctor.status]}`}>{statusLabels[doctor.status]}</span></header><dl className="grid grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)] gap-x-3 gap-y-2 text-sm"><dt className="text-slate-500">Cédula profesional</dt><dd className="font-bold text-slate-800">{doctor.licenseNumber}</dd><dt className="text-slate-500">Especialidad</dt><dd>{doctor.specialty}</dd><dt className="text-slate-500">Correo verificado</dt><dd>{doctor.emailVerified ? 'Sí' : 'No'}</dd><dt className="text-slate-500">Solicitud</dt><dd>{doctor.requestedAt}</dd>{doctor.reviewedBy && <><dt className="text-slate-500">Revisado por</dt><dd>{doctor.reviewedBy}</dd></>}</dl>{doctor.reviewNotes && <p className="mt-4 rounded-xl border bg-slate-50 p-3 text-sm text-slate-600"><strong>Observaciones:</strong> {doctor.reviewNotes}</p>}{doctor.status === 'approved' ? <div className="mt-5 flex items-center gap-3 rounded-2xl bg-emerald-50 p-4 text-emerald-700"><CircleCheck size={24} className="shrink-0" /><div><strong className="block text-sm">Validación completada</strong><span className="text-xs">El médico ya fue notificado y puede vincular pacientes.</span></div></div> : <button type="button" onClick={() => setAction({ type: 'approve', doctor })} className="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white"><CircleCheck size={18} />Aprobar y notificar</button>}{doctor.status !== 'rejected' && <button type="button" onClick={() => setAction({ type: 'reject', doctor })} className="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-red-300 px-4 py-3 text-sm font-bold text-red-700"><X size={18} />{doctor.status === 'approved' ? 'Revocar aprobación' : 'Rechazar solicitud'}</button>}</article>) : <div className="col-span-full rounded-3xl border border-slate-200 bg-white p-12 text-center text-slate-500"><Stethoscope className="mx-auto mb-3 opacity-25" size={56} /><h2 className="text-lg font-bold text-slate-800">No hay médicos en esta categoría</h2><p className="mt-1 text-sm">Las nuevas solicitudes aparecerán aquí después de completar el onboarding.</p></div>}</div><Pagination links={doctors.meta.links} />
        </section>
        <Modal open={Boolean(action)} title={action?.type === 'approve' ? 'Aprobar perfil médico' : action?.doctor.status === 'approved' ? 'Revocar aprobación médica' : 'Rechazar solicitud médica'} onClose={closeModal} actions={<><button type="button" onClick={closeModal} className="rounded-xl border px-4 py-2 text-sm font-semibold">Cancelar</button><button type="button" onClick={submitAction} disabled={form.processing} className={`rounded-xl px-4 py-2 text-sm font-semibold text-white disabled:opacity-50 ${action?.type === 'approve' ? 'bg-emerald-600' : 'bg-red-600'}`}>{form.processing ? 'Procesando...' : action?.type === 'approve' ? 'Aprobar y notificar' : 'Confirmar rechazo'}</button></>}>
            {action && <div><p className="mb-4 text-sm text-slate-600">Profesional: <strong>{action.doctor.name}</strong></p><FormTextarea id="review_notes" label={action.type === 'approve' ? 'Observaciones internas (opcional)' : 'Motivo del rechazo'} rows={3} maxLength={1000} value={form.data.review_notes} onChange={(event) => form.setData('review_notes', event.target.value)} error={form.errors.review_notes} required={action.type === 'reject'} /></div>}
        </Modal>
    </AdminLayout>;
}
