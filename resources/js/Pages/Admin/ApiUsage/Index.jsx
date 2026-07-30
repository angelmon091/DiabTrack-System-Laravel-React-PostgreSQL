import { Head } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { Bar } from 'react-chartjs-2';

import DataChart from '../../../Components/DataChart';
import Pagination from '../../../Components/Pagination';
import Table from '../../../Components/Table';
import AdminLayout from '../../../Layouts/AdminLayout';

function SummaryCard({ label, value }) {
    return <section className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"><p className="text-xs font-bold uppercase tracking-wide text-slate-500">{label}</p><p className="mt-3 text-3xl font-extrabold">{value}</p></section>;
}

export default function Index({ adminNavigation, summary, periods, logs }) {
    const [period, setPeriod] = useState('weekly');
    const series = periods[period];
    const tokenData = useMemo(() => ({ labels: series.map((row) => row.label), datasets: [{ label: 'Anthropic', data: series.map((row) => row.anthropicTokens), backgroundColor: 'rgba(0,180,216,.3)', borderColor: '#00b4d8', borderWidth: 2 }, { label: 'Gemini', data: series.map((row) => row.geminiTokens), backgroundColor: 'rgba(40,199,111,.3)', borderColor: '#28c76f', borderWidth: 2 }] }), [series]);
    return <AdminLayout adminNavigation={adminNavigation}><Head title="Uso de APIs" />
        <header className="mb-6"><h1 className="text-3xl font-extrabold">Uso de APIs de IA</h1><p className="mt-2 text-slate-500">Consumo, costos y trazabilidad por proveedor.</p></header>
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"><SummaryCard label="Tokens totales" value={summary.totalTokens.toLocaleString()} /><SummaryCard label="Costo total" value={`$${summary.totalCost.toFixed(4)}`} /><SummaryCard label="Llamadas totales" value={summary.totalCalls.toLocaleString()} /><SummaryCard label="Costo promedio" value={`$${summary.averageCost.toFixed(6)}`} /></div>
        <section className="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><div className="mb-5 flex flex-wrap items-center justify-between gap-3"><h2 className="text-sm font-bold uppercase tracking-wide text-slate-500">Tokens por periodo</h2><div className="flex gap-2">{[['weekly','7 días'],['daily','30 días'],['monthly','6 meses']].map(([key,label]) => <button key={key} type="button" aria-pressed={period === key} onClick={() => setPeriod(key)} className={`rounded-xl px-4 py-2 text-sm font-semibold ${period === key ? 'bg-cyan-600 text-white' : 'bg-slate-100'}`}>{label}</button>)}</div></div><div className="h-80"><Bar data={tokenData} options={{ responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true }, x: { grid: { display: false } } } }} /></div></section>
        <div className="mt-6 grid gap-6 lg:grid-cols-2"><DataChart title="Llamadas por proveedor" type="doughnut" labels={['Anthropic','Gemini']} values={[summary.anthropicCalls,summary.geminiCalls]} colors={['#00b4d8','#28c76f']} label="Llamadas" /><DataChart title="Costo por proveedor" labels={['Anthropic','Gemini']} values={[summary.anthropicCost,summary.geminiCost]} colors={['rgba(0,180,216,.65)','rgba(40,199,111,.65)']} label="Costo USD" /></div>
        <section className="mt-6"><h2 className="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">Llamadas recientes</h2><Table headers={['Proveedor','Modelo','Tipo','Entrada','Salida','Costo','Paciente','Fecha']}>{logs.data.length ? logs.data.map((log) => <tr key={log.id}><td className="px-5 py-4 text-sm font-semibold capitalize">{log.provider}</td><td className="px-5 py-4 font-mono text-xs">{log.model}</td><td className="px-5 py-4 text-sm">{log.dailyTip ? 'Tip diario' : 'Otro'}</td><td className="px-5 py-4 text-sm">{log.inputTokens.toLocaleString()}</td><td className="px-5 py-4 text-sm">{log.outputTokens.toLocaleString()}</td><td className="px-5 py-4 text-sm">${log.cost.toFixed(6)}</td><td className="px-5 py-4 text-sm">{log.patient ?? '—'}</td><td className="px-5 py-4 text-sm">{log.createdAt}</td></tr>) : <tr><td colSpan="8" className="px-5 py-10 text-center text-sm text-slate-500">No hay llamadas registradas.</td></tr>}</Table><Pagination links={logs.links} /></section>
    </AdminLayout>;
}
