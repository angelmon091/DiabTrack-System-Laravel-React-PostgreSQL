import { useEffect, useRef, useState } from 'react';

export default function GlobalSearch({ searchUrl }) {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState(null);
    const controller = useRef(null);

    useEffect(() => {
        if (query.trim().length < 2) { setResults(null); return undefined; }
        const timer = window.setTimeout(async () => {
            controller.current?.abort();
            controller.current = new AbortController();
            try {
                const response = await fetch(`${searchUrl}?q=${encodeURIComponent(query.trim())}`, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, signal: controller.current.signal });
                setResults(response.ok ? await response.json() : { sections: [], records: [] });
            } catch (error) { if (error.name !== 'AbortError') setResults({ sections: [], records: [] }); }
        }, 250);
        return () => window.clearTimeout(timer);
    }, [query, searchUrl]);

    const items = results ? [...results.sections, ...results.records] : [];
    return <div className="relative hidden w-full max-w-md lg:block">
        <label htmlFor="global-search" className="sr-only">Buscar secciones o registros</label>
        <input id="global-search" value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Buscar secciones o registros..." autoComplete="off" className="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10" />
        {results && <div className="absolute inset-x-0 top-full z-30 mt-2 max-h-80 overflow-auto rounded-2xl border border-slate-100 bg-white p-2 shadow-xl">
            {items.length ? items.map((item) => <a key={`${item.type}-${item.id ?? item.url}`} href={item.url} className="block rounded-xl px-3 py-2 hover:bg-cyan-50"><strong className="block text-sm text-slate-800">{item.title}</strong>{item.subtitle && <span className="text-xs text-slate-500">{item.subtitle}</span>}</a>) : <p className="p-3 text-center text-sm text-slate-500">Sin resultados para tu búsqueda</p>}
        </div>}
    </div>;
}
