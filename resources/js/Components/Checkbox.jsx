export default function Checkbox({ id, label, className = '', ...inputProps }) {
    return (
        <label
            htmlFor={id}
            className={`inline-flex cursor-pointer items-center gap-2 text-sm text-slate-500 ${className}`}
        >
            <input
                {...inputProps}
                id={id}
                type="checkbox"
                className="rounded border-cyan-500/30 text-cyan-500 shadow-sm focus:ring-cyan-500/30"
            />
            <span>{label}</span>
        </label>
    );
}
