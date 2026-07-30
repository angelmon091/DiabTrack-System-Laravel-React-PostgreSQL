import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

import ChartCard from '../../Components/ChartCard';
import Modal from '../../Components/Modal';
import Table from '../../Components/Table';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout';

function Metric({ label, value, unit, tone = 'text-cyan-700' }) {
    return <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"><p className="text-xs font-bold uppercase tracking-wide text-slate-500">{label}</p><p className={`mt-3 text-3xl font-extrabold ${tone}`}>{value ?? '--'} <span className="text-sm font-medium text-slate-400">{unit}</span></p></div>;
}

export default function Dashboard({ patients, selectedPatient, metrics, recentLogs, urls }) {
    const [unlinkPatient, setUnlinkPatient] = useState(null);
    return <AuthenticatedLayout>
        <Head title="Panel de cuidador" />
        <div className="grid gap-6 xl:grid-cols-[20rem_1fr]">
            <aside className="space-y-5">
                <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"><h1 className="text-xl font-bold">Panel de cuidador</h1><div className="mt-4 grid gap-2"><Link href={urls.link} className="rounded-2xl bg-cyan-600 px-4 py-3 text-center font-semibold text-white">Vincular paciente</Link><Link href={urls.profile} className="rounded-2xl bg-slate-100 px-4 py-3 text-center font-semibold">Ajustes</Link></div></section>
                {patients.length ? <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"><h2 className="mb-4 text-xs font-bold uppercase tracking-wide text-slate-500">Pacientes vinculados</h2><div className="space-y-3">{patients.map((patient) => <div key={patient.id} className={`rounded-2xl border-2 p-3 ${patient.selected ? 'border-cyan-500 bg-cyan-50' : 'border-slate-100'}`}><Link href={patient.dashboardUrl} preserveScroll className="block"><strong>{patient.name}</strong><span className="block text-xs text-slate-500">Parentesco: {patient.relationship}</span><span className="mt-2 block text-xs">Glucosa: <strong>{patient.latestGlucose ?? '--'} mg/dL</strong></span></Link><button type="button" onClick={() => setUnlinkPatient(patient)} className="mt-2 text-xs font-semibold text-red-600">Desvincular</button></div>)}</div></section> : <section className="rounded-3xl border-2 border-dashed border-slate-200 p-6 text-center"><p className="text-sm text-slate-600">Aún no tienes pacientes vinculados.</p><Link href={urls.link} className="mt-4 inline-block font-semibold text-cyan-700">Vincular mi primer paciente</Link></section>}
            </aside>
            <section className="space-y-6">{selectedPatient ? <>
                <header className="flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><div><h2 className="text-2xl font-extrabold">{selectedPatient.name}</h2><p className="mt-1 text-sm text-slate-500">{selectedPatient.diabetesType} · {selectedPatient.age ?? '--'} años · {selectedPatient.weight ?? '--'} kg</p></div><Link href={selectedPatient.vitalCreateUrl} className="rounded-2xl bg-cyan-600 px-5 py-3 font-semibold text-white">Registrar datos</Link></header>
                <div className="grid gap-4 sm:grid-cols-3"><Metric label="Última glucosa" value={metrics.latestGlucose} unit="mg/dL" tone={metrics.latestGlucose > 140 ? 'text-red-600' : 'text-cyan-700'} /><Metric label="Tiempo en rango" value={metrics.timeInRange} unit="%" tone="text-emerald-600" /><Metric label="Última HbA1c" value={metrics.latestHba1c} unit="%" /></div>
                <ChartCard title="Tendencia semanal (glucosa)" labels={metrics.glucoseLabels} values={metrics.glucoseData} />
                <section><h2 className="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">Registros recientes</h2><Table headers={['Fecha', 'Nivel', 'Momento', 'Estado']}>{recentLogs.length ? recentLogs.map((log) => <tr key={log.id}><td className="px-5 py-4 text-sm">{log.date}</td><td className="px-5 py-4 font-bold text-cyan-700">{log.glucose} mg/dL</td><td className="px-5 py-4 text-sm text-slate-500">{log.moment}</td><td className="px-5 py-4"><span className={`rounded-full px-3 py-1 text-xs font-semibold ${log.elevated ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700'}`}>{log.elevated ? 'Elevado' : 'Normal'}</span></td></tr>) : <tr><td colSpan="4" className="px-5 py-8 text-center text-sm text-slate-500">No hay registros recientes.</td></tr>}</Table></section>
            </> : <section className="rounded-3xl border border-slate-200 bg-white p-10 text-center shadow-sm"><h1 className="text-2xl font-bold">Acompaña la salud de tus pacientes</h1><p className="mt-3 text-slate-500">Vincula un paciente para consultar sus métricas.</p></section>}</section>
        </div>
        <Modal open={Boolean(unlinkPatient)} title="Desvincular paciente" onClose={() => setUnlinkPatient(null)} actions={<><button type="button" onClick={() => setUnlinkPatient(null)} className="rounded-xl px-4 py-2 font-semibold">Cancelar</button><button type="button" onClick={() => router.delete(unlinkPatient.unlinkUrl, { onFinish: () => setUnlinkPatient(null) })} className="rounded-xl bg-red-600 px-4 py-2 font-semibold text-white">Desvincular</button></>}><p className="text-sm text-slate-600">Perderás el acceso a los datos de salud de {unlinkPatient?.name}.</p></Modal>
    </AuthenticatedLayout>;
}
