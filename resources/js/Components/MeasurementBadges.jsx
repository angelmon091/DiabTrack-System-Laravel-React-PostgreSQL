import AlertTriangle from 'lucide-react/dist/esm/icons/triangle-alert.mjs';
import CheckCircle2 from 'lucide-react/dist/esm/icons/circle-check.mjs';
import Clock3 from 'lucide-react/dist/esm/icons/clock-3.mjs';

export function MeasurementMomentBadge({ value }) {
    return <span className="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700"><Clock3 size={14} className="text-cyan-600" />{value || 'Ayunas'}</span>;
}

export function GlucoseStatusBadge({ statusKey, label }) {
    const out = statusKey === 'high' || statusKey === 'low' || statusKey === 'above_range' || statusKey === 'below_range';
    return <span className={`inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold ${out ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700'}`}>{out ? <AlertTriangle size={14} /> : <CheckCircle2 size={14} />}{label || (out ? 'Fuera de rango' : 'En rango')}</span>;
}
