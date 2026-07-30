import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

import ChartCard from '../../Components/ChartCard';
import FormInput from '../../Components/FormInput';
import Modal from '../../Components/Modal';
import SubmitButton from '../../Components/SubmitButton';
import Table from '../../Components/Table';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout';

function Metric({ label, value, unit }) {
    return <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"><p className="text-xs font-bold uppercase tracking-wide text-slate-500">{label}</p><p className="mt-3 text-3xl font-extrabold text-cyan-700">{value ?? '--'} <span className="text-sm font-medium text-slate-400">{unit}</span></p></div>;
}

function TargetsForm({ patient }) {
    const form = useForm({ target_glucose_min: patient.targetMin, target_glucose_max: patient.targetMax });
    return <form onSubmit={(event) => { event.preventDefault(); form.patch(patient.targetsUrl, { preserveScroll: true }); }} className="grid gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:grid-cols-[1fr_1fr_auto] sm:items-end">
        <FormInput id="target_min" type="number" min="40" max="150" label="Meta mínima (mg/dL)" value={form.data.target_glucose_min} onChange={(event) => form.setData('target_glucose_min', event.target.value)} error={form.errors.target_glucose_min} />
        <FormInput id="target_max" type="number" min="100" max="300" label="Meta máxima (mg/dL)" value={form.data.target_glucose_max} onChange={(event) => form.setData('target_glucose_max', event.target.value)} error={form.errors.target_glucose_max} />
        <SubmitButton processing={form.processing}>Actualizar metas</SubmitButton>
    </form>;
}

export default function Dashboard({ approval, patients, selectedPatient, metrics, recentLogs, urls }) {
    const [unlinkPatient, setUnlinkPatient] = useState(null);
    return <AuthenticatedLayout><Head title="Panel médico" />
        <div className="grid gap-6 xl:grid-cols-[20rem_1fr]">
            <aside className="space-y-5"><section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"><h1 className="text-xl font-bold">Panel médico</h1><p className="mt-2 text-sm text-slate-500">Estado: {approval.label}</p>{approval.approved && <Link href={urls.link} className="mt-4 block rounded-2xl bg-cyan-600 px-4 py-3 text-center font-semibold text-white">Vincular paciente</Link>}<Link href={urls.profile} className="mt-2 block rounded-2xl bg-slate-100 px-4 py-3 text-center font-semibold">Ajustes</Link></section>
                {approval.approved && patients.length > 0 && <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"><h2 className="mb-4 text-xs font-bold uppercase tracking-wide text-slate-500">Pacientes vinculados</h2><div className="space-y-3">{patients.map((patient) => <div key={patient.id} className={`rounded-2xl border-2 p-3 ${patient.selected ? 'border-cyan-500 bg-cyan-50' : 'border-slate-100'}`}><Link href={patient.dashboardUrl} preserveScroll className="block"><strong>{patient.name}</strong><span className="block text-xs text-slate-500">{patient.diabetesType}</span><span className="mt-2 block text-xs">Glucosa: <strong>{patient.latestGlucose ?? '--'} mg/dL</strong></span></Link><button type="button" onClick={() => setUnlinkPatient(patient)} className="mt-2 text-xs font-semibold text-red-600">Desvincular</button></div>)}</div></section>}
            </aside>
            <section className="space-y-6">{!approval.approved ? <section className="rounded-3xl border border-amber-200 bg-amber-50 p-8 text-center"><h2 className="text-2xl font-bold">{approval.rejected ? 'Perfil médico requiere corrección' : 'Perfil médico en revisión'}</h2><p className="mt-3 text-slate-600">{approval.rejected ? approval.notes : `La cédula ${approval.licenseNumber ?? '--'} será revisada por un administrador.`}</p></section> : selectedPatient ? <>
                <header className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><h2 className="text-2xl font-extrabold">{selectedPatient.name}</h2><p className="mt-1 text-sm text-slate-500">{selectedPatient.diabetesType} · {selectedPatient.age ?? '--'} años · {selectedPatient.weight ?? '--'} kg · {selectedPatient.height ?? '--'} cm</p></header>
                <TargetsForm patient={selectedPatient} />
                <div className="grid gap-4 sm:grid-cols-4"><Metric label="Última glucosa" value={metrics.latestGlucose} unit="mg/dL" /><Metric label="Tiempo en rango" value={metrics.timeInRange} unit="%" /><Metric label="Última HbA1c" value={metrics.latestHba1c} unit="%" /><Metric label="Calorías hoy" value={metrics.caloriesToday} unit="kcal" /></div>
                <ChartCard title="Tendencia semanal (glucosa)" labels={metrics.glucoseLabels} values={metrics.glucoseData} />
                <Table headers={['Fecha', 'Glucosa', 'Presión', 'Pulso', 'Estado']}>{recentLogs.length ? recentLogs.map((log) => <tr key={log.id}><td className="px-5 py-4 text-sm">{log.date}</td><td className="px-5 py-4 font-bold text-cyan-700">{log.glucose ?? '--'} mg/dL</td><td className="px-5 py-4 text-sm">{log.systolic ?? '--'}/{log.diastolic ?? '--'} mmHg</td><td className="px-5 py-4 text-sm">{log.heartRate ?? '--'} bpm</td><td className="px-5 py-4 text-sm">{log.outOfRange ? 'Fuera de rango' : 'En rango'}</td></tr>) : <tr><td colSpan="5" className="px-5 py-8 text-center text-sm text-slate-500">No hay registros recientes.</td></tr>}</Table>
            </> : <section className="rounded-3xl border border-slate-200 bg-white p-10 text-center shadow-sm"><h2 className="text-2xl font-bold">No hay pacientes vinculados</h2><Link href={urls.link} className="mt-4 inline-block font-semibold text-cyan-700">Vincular paciente</Link></section>}</section>
        </div>
        <Modal open={Boolean(unlinkPatient)} title="Desvincular paciente" onClose={() => setUnlinkPatient(null)} actions={<><button type="button" onClick={() => setUnlinkPatient(null)} className="rounded-xl px-4 py-2 font-semibold">Cancelar</button><button type="button" onClick={() => router.delete(unlinkPatient.unlinkUrl, { onFinish: () => setUnlinkPatient(null) })} className="rounded-xl bg-red-600 px-4 py-2 font-semibold text-white">Desvincular</button></>}><p className="text-sm text-slate-600">El paciente dejará de aparecer en tu panel clínico.</p></Modal>
    </AuthenticatedLayout>;
}
