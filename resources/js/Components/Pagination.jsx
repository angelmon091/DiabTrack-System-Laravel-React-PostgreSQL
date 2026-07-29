export default function Pagination({ links }) {
    if (!links || links.length <= 3) return null;
    return <nav aria-label="Paginación" className="mt-6 flex flex-wrap justify-center gap-2">{links.map((link, index) => link.url ? <a key={`${link.label}-${index}`} href={link.url} aria-current={link.active ? 'page' : undefined} className={`rounded-xl px-3 py-2 text-sm font-semibold ${link.active ? 'bg-cyan-600 text-white' : 'bg-white text-slate-600 hover:bg-cyan-50'}`} dangerouslySetInnerHTML={{ __html: link.label }} /> : <span key={`${link.label}-${index}`} className="rounded-xl px-3 py-2 text-sm text-slate-300" dangerouslySetInnerHTML={{ __html: link.label }} />)}</nav>;
}
