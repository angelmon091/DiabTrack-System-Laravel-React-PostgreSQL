export default function Pagination({ links }) {
    if (!links || links.length <= 3) return null;

    const labelFor = (label) => {
        const normalized = String(label).toLowerCase();
        if (normalized.includes('previous') || normalized.includes('anterior')) return 'Anterior';
        if (normalized.includes('next') || normalized.includes('siguiente')) return 'Siguiente';
        return String(label).replace(/<[^>]+>/g, '');
    };

    return <nav aria-label="Paginación" className="mt-6 flex flex-wrap justify-center gap-2">
        {links.map((link, index) => link.url
            ? <a key={`${link.label}-${index}`} href={link.url} aria-current={link.active ? 'page' : undefined} className={`rounded-xl border px-4 py-2 text-sm font-semibold transition ${link.active ? 'border-cyan-600 bg-cyan-600 text-white' : 'border-slate-200 bg-white text-slate-700 hover:border-cyan-400 hover:bg-cyan-50'}`}>{labelFor(link.label)}</a>
            : <span key={`${link.label}-${index}`} className="rounded-xl border border-slate-100 px-4 py-2 text-sm text-slate-300">{labelFor(link.label)}</span>)}
    </nav>;
}
