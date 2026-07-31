export default function DashboardMetricCard({ label, value, unit, icon: Icon, tone = 'cyan' }) {
    const tones = {
        cyan: 'bg-cyan-50 text-cyan-600',
        emerald: 'bg-emerald-50 text-emerald-600',
        violet: 'bg-violet-50 text-violet-600',
        orange: 'bg-orange-50 text-orange-500',
        red: 'bg-red-50 text-red-500',
    };
    return <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div className="flex items-start justify-between gap-3"><p className="text-xs font-bold uppercase tracking-wide text-slate-500">{label}</p>{Icon && <span className={`grid h-9 w-9 shrink-0 place-items-center rounded-full ${tones[tone] ?? tones.cyan}`}><Icon size={18} /></span>}</div>
        <p className="mt-3 text-3xl font-extrabold text-slate-800">{value ?? '--'} <span className="text-sm font-medium text-slate-400">{unit}</span></p>
    </div>;
}
