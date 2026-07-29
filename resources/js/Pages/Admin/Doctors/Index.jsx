import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

import FormTextarea from '../../../Components/FormTextarea';
import Modal from '../../../Components/Modal';
import Pagination from '../../../Components/Pagination';
import Table from '../../../Components/Table';
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
            <nav aria-label="Estado de solicitudes" className="mb-6 flex flex-wrap gap-2 rounded-3xl border border-slate-200 bg-white p-4">{statusOptions.map((option) => <Link key={option.value} href={indexUrl} data={{ status: option.value }} preserveState className={`rounded-xl px-4 py-2 text-sm font-semibold ${filters.status === option.value ? 'bg-cyan-600 text-white' : 'bg-slate-50 text-slate-600 hover:bg-cyan-50'}`}>{option.label}</Link>)}</nav>
            <Table headers={['Profesional', 'Cédula y especialidad', 'Estado', 'Revisión', 'Acciones']}>
                {doctors.data.length ? doctors.data.map((doctor) => <tr key={doctor.id}><td className="px-5 py-4"><strong className="block text-slate-900">{doctor.name}</strong><span className="text-sm text-slate-500">{doctor.email}</span><span className="mt-1 block text-xs text-slate-400">Correo verificado: {doctor.emailVerified ? 'Sí' : 'No'}</span></td><td className="px-5 py-4"><strong className="block text-slate-800">{doctor.licenseNumber}</strong><span className="text-sm text-slate-500">{doctor.specialty}</span><span className="mt-1 block text-xs text-slate-400">Solicitud: {doctor.requestedAt}</span></td><td className="px-5 py-4"><span className={`rounded-full px-3 py-1 text-xs font-bold ${statusStyles[doctor.status]}`}>{statusLabels[doctor.status]}</span></td><td className="max-w-xs px-5 py-4 text-sm text-slate-600">{doctor.reviewNotes || 'Sin observaciones'}{doctor.reviewedBy && <span className="mt-1 block text-xs text-slate-400">Por {doctor.reviewedBy}</span>}</td><td className="px-5 py-4"><div className="flex flex-col gap-2">{doctor.status !== 'approved' && <button type="button" onClick={() => setAction({ type: 'approve', doctor })} className="rounded-xl bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">Aprobar y notificar</button>}{doctor.status !== 'rejected' && <button type="button" onClick={() => setAction({ type: 'reject', doctor })} className="rounded-xl bg-red-50 px-3 py-2 text-sm font-semibold text-red-700">{doctor.status === 'approved' ? 'Revocar aprobación' : 'Rechazar solicitud'}</button>}</div></td></tr>) : <tr><td colSpan="5" className="px-5 py-12 text-center text-slate-500">No hay médicos en esta categoría.</td></tr>}
            </Table><Pagination links={doctors.meta.links} />
        </section>
        <Modal open={Boolean(action)} title={action?.type === 'approve' ? 'Aprobar perfil médico' : action?.doctor.status === 'approved' ? 'Revocar aprobación médica' : 'Rechazar solicitud médica'} onClose={closeModal} actions={<><button type="button" onClick={closeModal} className="rounded-xl border px-4 py-2 text-sm font-semibold">Cancelar</button><button type="button" onClick={submitAction} disabled={form.processing} className={`rounded-xl px-4 py-2 text-sm font-semibold text-white disabled:opacity-50 ${action?.type === 'approve' ? 'bg-emerald-600' : 'bg-red-600'}`}>{form.processing ? 'Procesando...' : action?.type === 'approve' ? 'Aprobar y notificar' : 'Confirmar rechazo'}</button></>}>
            {action && <div><p className="mb-4 text-sm text-slate-600">Profesional: <strong>{action.doctor.name}</strong></p><FormTextarea id="review_notes" label={action.type === 'approve' ? 'Observaciones internas (opcional)' : 'Motivo del rechazo'} rows={3} maxLength={1000} value={form.data.review_notes} onChange={(event) => form.setData('review_notes', event.target.value)} error={form.errors.review_notes} required={action.type === 'reject'} /></div>}
        </Modal>
    </AdminLayout>;
}
