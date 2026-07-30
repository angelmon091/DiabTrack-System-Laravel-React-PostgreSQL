import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import HandHeart from 'lucide-react/dist/esm/icons/hand-heart.mjs';
import LinkIcon from 'lucide-react/dist/esm/icons/link.mjs';
import SlidersHorizontal from 'lucide-react/dist/esm/icons/sliders-horizontal.mjs';
import UserRound from 'lucide-react/dist/esm/icons/user-round.mjs';

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
            <aside className="order-2 space-y-5 xl:order-1">
                <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"><h1 className="flex items-center gap-2 text-lg font-bold text-cyan-700"><HandHeart size={22} />Panel de cuidador</h1><div className="mt-4 grid gap-2"><Link href={urls.link} className="flex items-center gap-3 rounded-2xl bg-slate-50 p-3 text-slate-900 transition hover:bg-white hover:shadow-sm"><span className="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-400 text-white"><LinkIcon size={20} /></span><span><strong className="block text-sm">Vincular paciente</strong><small className="text-xs text-slate-500">Conecta usando un código</small></span></Link><Link href={urls.profile} className="flex items-center gap-3 rounded-2xl bg-slate-50 p-3 text-slate-900 transition hover:bg-white hover:shadow-sm"><span className="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-500 text-white"><SlidersHorizontal size={20} /></span><span><strong className="block text-sm">Ajustes</strong><small className="text-xs text-slate-500">Configurar perfil</small></span></Link></div></section>
                {patients.length ? <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"><h2 className="mb-4 text-xs font-bold uppercase tracking-wide text-slate-500">Pacientes vinculados</h2><div className="space-y-3">{patients.map((patient) => <div key={patient.id} className={`rounded-2xl border-2 p-3 ${patient.selected ? 'border-cyan-500 bg-cyan-50' : 'border-slate-100'}`}><Link href={patient.dashboardUrl} preserveScroll className="block"><strong>{patient.name}</strong><span className="block text-xs text-slate-500">Parentesco: {patient.relationship}</span><span className="mt-2 block text-xs">Glucosa: <strong>{patient.latestGlucose ?? '--'} mg/dL</strong></span></Link><button type="button" onClick={() => setUnlinkPatient(patient)} className="mt-2 text-xs font-semibold text-red-600">Desvincular</button></div>)}</div></section> : <section className="rounded-3xl border-2 border-dashed border-slate-200 p-6 text-center"><p className="text-sm text-slate-600">Aún no tienes pacientes vinculados.</p><Link href={urls.link} className="mt-4 inline-block font-semibold text-cyan-700">Vincular mi primer paciente</Link></section>}
            </aside>
            <section className="order-1 space-y-6 xl:order-2">{selectedPatient ? <>
                <header className="flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><div className="flex items-center gap-4"><span className="flex h-14 w-14 shrink-0 items-center justify-center rounded-full border-2 border-cyan-500 bg-cyan-50 text-cyan-700"><UserRound size={27} /></span><div><h2 className="text-2xl font-bold">{selectedPatient.name}</h2><p className="mt-1 text-sm text-slate-500">{selectedPatient.diabetesType} · {selectedPatient.age ?? '--'} años · {selectedPatient.weight ?? '--'} kg</p></div></div><Link href={selectedPatient.vitalCreateUrl} className="rounded-xl bg-gradient-to-br from-cyan-500 to-cyan-600 px-5 py-3 font-semibold text-white shadow-md shadow-cyan-500/20">Registrar datos</Link></header>
                <div className="grid gap-4 sm:grid-cols-3"><Metric label="Última glucosa" value={metrics.latestGlucose} unit="mg/dL" tone={metrics.latestGlucose > 140 ? 'text-red-600' : 'text-cyan-700'} /><Metric label="Tiempo en rango" value={metrics.timeInRange} unit="%" tone="text-emerald-600" /><Metric label="Última HbA1c" value={metrics.latestHba1c} unit="%" /></div>
                <ChartCard title="Tendencia semanal (glucosa)" labels={metrics.glucoseLabels} values={metrics.glucoseData} />
                <section><h2 className="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">Registros recientes</h2><Table headers={['Fecha', 'Nivel', 'Momento', 'Estado']}>{recentLogs.length ? recentLogs.map((log) => <tr key={log.id}><td className="px-5 py-4 text-sm">{log.date}</td><td className="px-5 py-4 font-bold text-cyan-700">{log.glucose} mg/dL</td><td className="px-5 py-4 text-sm text-slate-500">{log.moment}</td><td className="px-5 py-4"><span className={`rounded-full px-3 py-1 text-xs font-semibold ${log.elevated ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700'}`}>{log.elevated ? 'Elevado' : 'Normal'}</span></td></tr>) : <tr><td colSpan="4" className="px-5 py-8 text-center text-sm text-slate-500">No hay registros recientes.</td></tr>}</Table></section>
            </> : <section className="rounded-3xl border border-slate-200 bg-white p-10 text-center shadow-sm"><h1 className="text-2xl font-bold">Acompaña la salud de tus pacientes</h1><p className="mt-3 text-slate-500">Vincula un paciente para consultar sus métricas.</p></section>}</section>
        </div>
        <Modal open={Boolean(unlinkPatient)} title="Desvincular paciente" onClose={() => setUnlinkPatient(null)} actions={<><button type="button" onClick={() => setUnlinkPatient(null)} className="rounded-xl px-4 py-2 font-semibold">Cancelar</button><button type="button" onClick={() => router.delete(unlinkPatient.unlinkUrl, { onFinish: () => setUnlinkPatient(null) })} className="rounded-xl bg-red-600 px-4 py-2 font-semibold text-white">Desvincular</button></>}><p className="text-sm text-slate-600">Perderás el acceso a los datos de salud de {unlinkPatient?.name}.</p></Modal>
    </AuthenticatedLayout>;
}
