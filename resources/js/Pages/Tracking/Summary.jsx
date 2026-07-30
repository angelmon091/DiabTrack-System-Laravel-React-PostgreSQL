import { Head } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import CalendarDays from 'lucide-react/dist/esm/icons/calendar-days.mjs';
import Clock3 from 'lucide-react/dist/esm/icons/clock-3.mjs';
import FileText from 'lucide-react/dist/esm/icons/file-text.mjs';
import Info from 'lucide-react/dist/esm/icons/info.mjs';

import DataChart from '../../Components/DataChart';
import Table from '../../Components/Table';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout';

const tabs = [{ key: 'vitals', label: 'Signos vitales' }, { key: 'nutrition', label: 'Nutrición' }, { key: 'activity', label: 'Actividad' }, { key: 'symptoms', label: 'Síntomas' }];

function Metric({ label, value, unit, status, tone = 'border-l-cyan-500' }) {
    return <div className={`rounded-3xl border border-slate-200 border-l-[6px] ${tone} bg-white/90 p-5 shadow-sm`}><div className="flex items-center justify-between gap-2"><p className="text-xs font-bold uppercase tracking-wide text-slate-500">{label}</p><Info size={15} className="shrink-0 text-slate-300" /></div><p className="mt-2 text-3xl font-extrabold">{value ?? '--'} <span className="text-sm text-slate-400">{unit}</span></p>{status && <p className="mt-3 text-xs font-semibold text-cyan-700">{status}</p>}</div>;
}

function History({ activeTab, histories, period }) {
    const [page, setPage] = useState(1);
    const filtered = useMemo(() => histories[activeTab].filter((row) => period === 'all' || new Date(row.isoDate) >= new Date(Date.now() - Number(period) * 86400000)), [activeTab, histories, period]);
    const pages = Math.max(1, Math.ceil(filtered.length / 8));
    const rows = filtered.slice((Math.min(page, pages) - 1) * 8, Math.min(page, pages) * 8);
    const definitions = {
        vitals: { headers: ['Fecha', 'Glucosa', 'Momento', 'Presión', 'Pulso', 'Peso'], cells: (r) => [r.date, `${r.glucose ?? '--'} mg/dL`, r.moment ?? '--', r.pressure, r.heartRate ? `${r.heartRate} bpm` : '--', r.weight ? `${r.weight} kg` : '--'] },
        nutrition: { headers: ['Fecha', 'Comida', 'Carbohidratos', 'Calorías', 'Categorías', 'Medicación'], cells: (r) => [r.date, r.mealType, `${r.carbs} g`, r.carbs * 4, r.categories.join(', ') || '--', r.medication ?? '--'] },
        activity: { headers: ['Fecha', 'Actividad', 'Duración', 'Intensidad', 'Energía'], cells: (r) => [r.date, r.type, `${r.duration} min`, r.intensity, r.energy ?? '--'] },
        symptoms: { headers: ['Fecha', 'Síntoma', 'Categoría', 'Hora'], cells: (r) => [r.date, r.name, r.category, r.time] },
    };
    const definition = definitions[activeTab];
    return <><Table headers={definition.headers}>{rows.length ? rows.map((row, index) => <tr key={`${row.isoDate}-${index}`}>{definition.cells(row).map((cell, cellIndex) => <td key={cellIndex} className="px-5 py-4 text-sm text-slate-600">{cell}</td>)}</tr>) : <tr><td colSpan={definition.headers.length} className="px-5 py-10 text-center text-sm text-slate-500">No hay registros en este periodo.</td></tr>}</Table>{filtered.length > 8 && <div className="mt-4 flex items-center justify-center gap-4"><button type="button" disabled={page <= 1} onClick={() => setPage((value) => value - 1)} className="rounded-xl border px-3 py-2 disabled:opacity-40">Anterior</button><span className="text-sm">Página {Math.min(page, pages)} de {pages}</span><button type="button" disabled={page >= pages} onClick={() => setPage((value) => value + 1)} className="rounded-xl border px-3 py-2 disabled:opacity-40">Siguiente</button></div>}</>;
}

export default function Summary({ metrics, charts, histories }) {
    const [activeTab, setActiveTab] = useState('vitals');
    const [period, setPeriod] = useState('all');
    return <AuthenticatedLayout><Head title="Resumen de salud" />
        <header className="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center"><div><h1 className="text-[clamp(1.7rem,3vw,2rem)] font-bold text-slate-900">Visualización <span className="text-cyan-500">Integral</span></h1><p className="mt-1 text-sm text-slate-500">Análisis detallado de todos tus registros históricos</p></div><div className="flex flex-wrap gap-2"><button type="button" className="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600"><CalendarDays size={17} />Historial Completo</button><span className="inline-flex items-center gap-2 rounded-full bg-cyan-600 px-4 py-2 text-sm font-semibold text-white opacity-55"><FileText size={17} />Reporte Médico <small className="inline-flex items-center gap-1 rounded-full border border-white/40 bg-white/20 px-2 py-0.5 text-[9px]"><Clock3 size={10} />Próximamente</small></span></div></header>
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"><Metric label="Glucosa promedio" value={metrics.avgGlucose} unit="mg/dL" status={metrics.glucoseStatus} /><Metric label="Tiempo en rango" value={metrics.timeInRange} unit="%" tone="border-l-emerald-500" /><Metric label="Última HbA1c" value={metrics.latestHba1c} unit="%" tone="border-l-violet-500" /><Metric label="Peso" value={metrics.weight} unit="kg" tone="border-l-amber-500" /><Metric label="Presión promedio" value={`${metrics.avgSystolic || '--'}/${metrics.avgDiastolic || '--'}`} unit="mmHg" status={metrics.bpStatus} tone="border-l-red-500" /><Metric label="Pulso promedio" value={metrics.avgHeartRate} unit="bpm" status={metrics.hrStatus} tone="border-l-rose-500" /><Metric label="Carbohidratos" value={metrics.totalCarbs} unit="g" tone="border-l-orange-500" /><Metric label="Actividad" value={metrics.activityHours} unit="h" tone="border-l-sky-500" /></div>
        <div className="mt-6 grid gap-6 lg:grid-cols-2"><DataChart title="Tendencia semanal de glucosa" type="line" labels={charts.glucose.labels} values={charts.glucose.values} label="Glucosa promedio" /><DataChart title="Composición de alimentación" type="doughnut" labels={charts.food.labels} values={charts.food.values} colors={['#00b4d8','#ff9f43','#28c76f','#7367f0','#ea5455','#94a3b8']} label="Registros" /><DataChart title="Frecuencia de síntomas" labels={charts.symptoms.labels} values={charts.symptoms.values} label="Frecuencia" /><DataChart title="Glucosa por momento" labels={charts.moments.labels} values={charts.moments.values} colors={charts.moments.colors} label="Promedio mg/dL" /></div>
        <section className="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><div className="flex flex-wrap items-center justify-between gap-4"><div className="flex flex-wrap gap-2" role="tablist">{tabs.map((tab) => <button key={tab.key} type="button" role="tab" aria-selected={activeTab === tab.key} onClick={() => { setActiveTab(tab.key); }} className={`rounded-xl px-4 py-2 text-sm font-semibold ${activeTab === tab.key ? 'bg-cyan-600 text-white' : 'bg-slate-100'}`}>{tab.label}</button>)}</div><label className="text-sm font-semibold">Periodo <select value={period} onChange={(event) => setPeriod(event.target.value)} className="ml-2 rounded-xl border-slate-200"><option value="all">Todo</option><option value="7">7 días</option><option value="30">30 días</option><option value="90">90 días</option></select></label></div><div className="mt-5"><History activeTab={activeTab} histories={histories} period={period} /></div></section>
    </AuthenticatedLayout>;
}
