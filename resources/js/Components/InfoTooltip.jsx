import Info from 'lucide-react/dist/esm/icons/info.mjs';

export default function InfoTooltip({ text }) {
    return <span className="group relative inline-flex shrink-0" tabIndex="0" aria-label={text}><Info size={15} className="cursor-help text-slate-400" /><span role="tooltip" className="pointer-events-none absolute left-0 top-6 z-30 w-max max-w-[min(16rem,calc(100vw-2rem))] rounded-xl bg-slate-900 px-3 py-2 text-left text-xs font-normal leading-5 text-white opacity-0 shadow-xl transition group-hover:opacity-100 group-focus:opacity-100">{text}</span></span>;
}
