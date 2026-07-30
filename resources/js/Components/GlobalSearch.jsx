import { Link } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

export default function GlobalSearch({ searchUrl }) {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState(null);
    const [open, setOpen] = useState(false);
    const containerRef = useRef(null);
    const controller = useRef(null);

    useEffect(() => {
        if (query.trim().length < 2) {
            setResults(null);
            setOpen(false);
            return undefined;
        }

        const timer = window.setTimeout(async () => {
            controller.current?.abort();
            controller.current = new AbortController();
            try {
                const response = await fetch(`${searchUrl}?q=${encodeURIComponent(query.trim())}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.current.signal,
                });
                setResults(response.ok ? await response.json() : { sections: [], records: [] });
                setOpen(true);
            } catch (error) {
                if (error.name !== 'AbortError') setResults({ sections: [], records: [] });
            }
        }, 250);

        return () => window.clearTimeout(timer);
    }, [query, searchUrl]);

    useEffect(() => {
        const closeOutside = (event) => {
            if (!containerRef.current?.contains(event.target)) setOpen(false);
        };
        document.addEventListener('mousedown', closeOutside);
        return () => document.removeEventListener('mousedown', closeOutside);
    }, []);

    const groups = [
        { title: 'Secciones', items: results?.sections ?? [] },
        { title: 'Registros', items: results?.records ?? [] },
    ].filter((group) => group.items.length > 0);

    return <div ref={containerRef} className="relative ml-8 hidden w-full max-w-[280px] lg:block xl:ml-16">
        <label htmlFor="global-search" className="sr-only">Buscar secciones o registros</label>
        <Search size={17} className="pointer-events-none absolute left-4 top-1/2 z-10 -translate-y-1/2 text-slate-500" />
        <input id="global-search" type="search" value={query} onChange={(event) => setQuery(event.target.value)} onFocus={() => results && setOpen(true)} onKeyDown={(event) => event.key === 'Escape' && setOpen(false)} className="h-11 w-full rounded-full border border-slate-200 bg-slate-50 py-2 pl-11 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-500 focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-500/10" placeholder="Buscar secciones o registros..." autoComplete="off" />
        {open && <div className="absolute inset-x-0 top-[calc(100%+8px)] z-50 max-h-96 overflow-y-auto rounded-2xl border border-slate-200 bg-white py-2 shadow-xl">
            {groups.length ? groups.map((group) => <div key={group.title}>
                <p className="px-4 pb-1 pt-2 text-[11px] font-bold uppercase tracking-wide text-slate-400">{group.title}</p>
                {group.items.map((item) => <Link key={`${item.type ?? group.title}-${item.id ?? item.url}`} href={item.url} className="flex items-center gap-3 px-4 py-2.5 text-slate-700 transition hover:bg-cyan-50" onClick={() => setOpen(false)}>
                    <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-cyan-600"><Search size={15} /></span>
                    <span className="min-w-0 flex-1"><strong className="block truncate text-sm">{item.title ?? item.label}</strong>{item.subtitle && <span className="block truncate text-xs text-slate-500">{item.subtitle}</span>}</span>
                </Link>)}
            </div>) : results && <p className="px-4 py-5 text-center text-sm text-slate-500">Sin resultados para tu búsqueda</p>}
        </div>}
    </div>;
}
