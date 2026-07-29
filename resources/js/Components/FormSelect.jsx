import FormError from './FormError';

export default function FormSelect({
    id,
    label,
    error,
    children,
    className = '',
    selectClassName = '',
    required = false,
    ...selectProps
}) {
    const errorId = error ? `${id}-error` : undefined;

    return (
        <div className={className}>
            {label && (
                <label
                    htmlFor={id}
                    className="mb-1.5 block text-sm font-semibold text-slate-500"
                >
                    {label}
                    {required && <span className="ml-1 text-red-600" aria-hidden="true">*</span>}
                </label>
            )}

            <select
                {...selectProps}
                id={id}
                required={required}
                aria-invalid={Boolean(error)}
                aria-describedby={errorId}
                className={`w-full rounded-2xl border bg-cyan-50 px-4 py-3 text-[0.95rem] text-slate-700 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 disabled:cursor-not-allowed disabled:opacity-60 ${
                    error ? 'border-red-400' : 'border-cyan-500/20'
                } ${selectClassName}`}
            >
                {children}
            </select>

            <FormError id={errorId} message={error} />
        </div>
    );
}
