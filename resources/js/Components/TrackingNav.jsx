import { Link } from '@inertiajs/react';

export default function TrackingNav({ items, active }) {
    return <nav aria-label="Tipos de registro" className="mb-7 flex gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2">{items.map((item) => <Link key={item.key} href={item.url} className={`whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold ${active === item.key ? 'bg-cyan-600 text-white' : 'text-slate-600 hover:bg-cyan-50'}`}>{item.label}</Link>)}</nav>;
}
