export default function Table({ headers, children }) {
    return <div className="overflow-x-auto rounded-3xl border border-slate-200 bg-white shadow-sm"><table className="min-w-full divide-y divide-slate-200"><thead className="bg-slate-50"><tr>{headers.map((header) => <th key={header} className="px-5 py-4 text-left text-xs font-bold uppercase tracking-wide text-slate-500">{header}</th>)}</tr></thead><tbody className="divide-y divide-slate-100">{children}</tbody></table></div>;
}
