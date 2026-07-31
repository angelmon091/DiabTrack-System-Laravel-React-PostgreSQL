import { Head } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { Bar } from 'react-chartjs-2';
import Bell from 'lucide-react/dist/esm/icons/bell.mjs';
import CircleDollarSign from 'lucide-react/dist/esm/icons/circle-dollar-sign.mjs';
import Info from 'lucide-react/dist/esm/icons/info.mjs';
import Lightbulb from 'lucide-react/dist/esm/icons/lightbulb.mjs';
import MessageCircle from 'lucide-react/dist/esm/icons/message-circle.mjs';
import PieChart from 'lucide-react/dist/esm/icons/chart-pie.mjs';
import Zap from 'lucide-react/dist/esm/icons/zap.mjs';

import DataChart from '../../../Components/DataChart';
import Pagination from '../../../Components/Pagination';
import Table from '../../../Components/Table';
import AdminLayout from '../../../Layouts/AdminLayout';

function SummaryCard({ label, value, detail, icon: Icon, iconClassName, iconBackground }) {
    return <section className="min-w-0 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div className="mb-4 flex items-center gap-3">
            <span className={`grid h-10 w-10 shrink-0 place-items-center rounded-xl ${iconBackground}`}><Icon className={`h-5 w-5 ${iconClassName}`} /></span>
            <p className="min-w-0 break-words text-xs font-medium text-slate-500 sm:text-sm">{label}</p>
        </div>
        <p className="text-[1.35rem] font-extrabold leading-none text-slate-900 sm:text-[1.6rem]">{value}</p>
        <p className="mt-2 text-xs text-slate-500">{detail}</p>
    </section>;
}

export default function Index({ adminNavigation, summary, periods, logs }) {
    const [period, setPeriod] = useState('weekly');
    const series = periods[period];
    const tokenData = useMemo(() => ({ labels: series.map((row) => row.label), datasets: [{ label: 'Anthropic', data: series.map((row) => row.anthropicTokens), backgroundColor: 'rgba(0,180,216,.3)', borderColor: '#00b4d8', borderWidth: 2 }, { label: 'Gemini', data: series.map((row) => row.geminiTokens), backgroundColor: 'rgba(40,199,111,.3)', borderColor: '#28c76f', borderWidth: 2 }] }), [series]);

    return <AdminLayout adminNavigation={adminNavigation}><Head title="Uso de APIs" />
        <header className="mb-6"><h1 className="text-[clamp(1.65rem,3vw,2rem)] font-bold text-slate-900">Uso de APIs de Inteligencia Artificial</h1><p className="mt-2 text-slate-500">Consumo de tokens y costos estimados por proveedor en la generación de tips diarios.</p></header>
        <div className="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <SummaryCard label="Total Tokens" value={summary.totalTokens.toLocaleString()} detail="histórico acumulado" icon={Zap} iconClassName="text-cyan-600" iconBackground="bg-cyan-50" />
            <SummaryCard label="Costo Estimado" value={`$${summary.totalCost.toFixed(4)}`} detail="USD total histórico" icon={CircleDollarSign} iconClassName="text-emerald-600" iconBackground="bg-emerald-50" />
            <SummaryCard label="Llamadas IA" value={summary.totalCalls.toLocaleString()} detail="tips + recordatorios · histórico" icon={MessageCircle} iconClassName="text-sky-600" iconBackground="bg-sky-50" />
            <SummaryCard label="Costo Prom./Llamada" value={`$${summary.averageCost.toFixed(6)}`} detail="USD · últimos 30 días" icon={PieChart} iconClassName="text-amber-500" iconBackground="bg-amber-50" />
        </div>
        <section className="mt-6 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <div className="mb-5 flex flex-wrap items-center justify-between gap-3"><div><h2 className="font-bold text-slate-900">Consumo de Tokens por Período</h2><p className="text-xs text-slate-500">Comparativa Anthropic vs Gemini</p></div><div className="flex gap-2">{[['weekly', '7 días'], ['daily', '30 días'], ['monthly', '6 meses']].map(([key, label]) => <button key={key} type="button" aria-pressed={period === key} onClick={() => setPeriod(key)} className={`rounded-xl px-3 py-2 text-xs font-semibold sm:px-4 sm:text-sm ${period === key ? 'bg-cyan-600 text-white' : 'bg-slate-100'}`}>{label}</button>)}</div></div>
            <div className="h-72"><Bar data={tokenData} options={{ responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true }, x: { grid: { display: false } } } }} /></div>
        </section>
        <div className="mt-6 grid gap-6 lg:grid-cols-2"><DataChart title="Llamadas por proveedor" type="doughnut" labels={['Anthropic', 'Gemini']} values={[summary.anthropicCalls, summary.geminiCalls]} colors={['#00b4d8', '#28c76f']} label="Llamadas" /><DataChart title="Costo por proveedor" labels={['Anthropic', 'Gemini']} values={[summary.anthropicCost, summary.geminiCost]} colors={['rgba(0,180,216,.65)', 'rgba(40,199,111,.65)']} label="Costo USD" /></div>
        <section className="mt-6"><h2 className="mb-1 font-bold text-slate-900">Registro de Llamadas Recientes</h2><p className="mb-3 text-xs text-slate-500">Últimas {logs.data.length} llamadas a las APIs</p><Table headers={['Proveedor', 'Modelo', 'Tipo', 'Entrada', 'Salida', 'Costo', 'Paciente', 'Fecha']}>{logs.data.length ? logs.data.map((log) => <tr key={log.id}><td className="px-5 py-4 text-sm font-semibold capitalize">{log.provider}</td><td className="px-5 py-4 font-mono text-xs">{log.model}</td><td className="px-5 py-4 text-sm"><span className="inline-flex items-center gap-1 rounded-full bg-sky-50 px-2 py-1 text-xs font-semibold text-sky-700">{log.dailyTip ? <Lightbulb className="h-3.5 w-3.5" /> : <Bell className="h-3.5 w-3.5" />}{log.dailyTip ? 'Tip' : 'Recordatorio'}</span></td><td className="px-5 py-4 text-sm">{log.inputTokens.toLocaleString()}</td><td className="px-5 py-4 text-sm">{log.outputTokens.toLocaleString()}</td><td className="px-5 py-4 text-sm">${log.cost.toFixed(6)}</td><td className="px-5 py-4 text-sm">{log.patient ?? '—'}</td><td className="px-5 py-4 text-sm">{log.createdAt}</td></tr>) : <tr><td colSpan="8" className="px-5 py-10 text-center text-sm text-slate-500"><Info className="mx-auto mb-2 h-8 w-8 text-slate-300" />No hay llamadas registradas.</td></tr>}</Table><Pagination links={logs.links} /></section>
    </AdminLayout>;
}
