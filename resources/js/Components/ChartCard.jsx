import {
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Title,
    Tooltip,
} from 'chart.js';
import { Line } from 'react-chartjs-2';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

export default function ChartCard({ title, labels, values, datasetLabel = 'Glucosa Promedio (mg/dL)' }) {
    const hasData = values.some((value) => value !== null);
    return <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 className="mb-5 text-sm font-bold uppercase tracking-wide text-slate-500">{title}</h2>
        {hasData ? <div className="h-72"><Line data={{ labels, datasets: [{ label: datasetLabel, data: values, borderColor: '#00b4d8', backgroundColor: 'rgba(0,180,216,.08)', borderWidth: 3, pointRadius: 4, tension: .4, fill: true, spanGaps: true }] }} options={{ responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: false }, x: { grid: { display: false } } } }} /></div> : <p className="py-16 text-center text-sm text-slate-500">No hay datos suficientes para mostrar la tendencia.</p>}
    </section>;
}
