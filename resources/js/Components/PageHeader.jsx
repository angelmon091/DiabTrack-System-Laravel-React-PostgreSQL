export default function PageHeader({ title, subtitle, className = '' }) {
    return <header className={className}>
        <h1 className="text-[1.625rem] font-bold leading-tight text-slate-900 sm:text-3xl">{title}</h1>
        {subtitle && <p className="mt-2 text-base font-normal text-slate-500">{subtitle}</p>}
    </header>;
}
