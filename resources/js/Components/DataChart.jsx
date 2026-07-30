import {
    ArcElement,
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';
import { Bar, Doughnut, Line } from 'react-chartjs-2';

ChartJS.register(ArcElement, BarElement, CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Legend, Filler);

export default function DataChart({ title, type = 'bar', labels, values, colors, label, emptyTitle, emptyText, emptyIcon: EmptyIcon }) {
    const hasData = values.some((value) => Number(value) > 0);
    const data = { labels, datasets: [{ label, data: values, backgroundColor: colors ?? 'rgba(0,180,216,.65)', borderColor: type === 'line' ? '#00b4d8' : '#fff', borderWidth: type === 'line' ? 3 : 1, tension: .35, fill: type === 'line' }] };
    const Chart = type === 'doughnut' ? Doughnut : type === 'line' ? Line : Bar;
    return <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><h2 className="mb-5 text-sm font-bold uppercase tracking-wide text-slate-500">{title}</h2>{hasData ? <div className="h-72"><Chart data={data} options={{ responsive: true, maintainAspectRatio: false, plugins: { legend: { display: type === 'doughnut', position: 'bottom' } }, scales: type === 'doughnut' ? {} : { y: { beginAtZero: true }, x: { grid: { display: false } } } }} /></div> : <div className="flex h-72 flex-col items-center justify-center px-4 text-center">{EmptyIcon && <span className="mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-500"><EmptyIcon size={22} /></span>}<p className="font-bold text-slate-800">{emptyTitle ?? 'Sin datos para este análisis.'}</p>{emptyText && <p className="mt-1 max-w-md text-sm leading-6 text-slate-500">{emptyText}</p>}</div>}</section>;
}
