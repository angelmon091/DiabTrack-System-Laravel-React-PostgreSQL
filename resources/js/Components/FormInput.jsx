import FormError from './FormError';
import InfoTooltip from './InfoTooltip';

export default function FormInput({
    id,
    label,
    error,
    className = '',
    inputClassName = '',
    icon: Icon,
    required = false,
    help,
    ...inputProps
}) {
    const errorId = error ? `${id}-error` : undefined;

    return (
        <div className={className}>
            {label && (
                <label
                    htmlFor={id}
                    className="mb-1.5 block text-sm font-semibold text-slate-500"
                >
                    <span className="inline-flex items-center gap-1.5">{label}{help && <InfoTooltip text={help} />}</span>
                    {required && <span className="ml-1 text-red-600" aria-hidden="true">*</span>}
                </label>
            )}

            <div className="relative">
                <input
                    {...inputProps}
                    id={id}
                    required={required}
                    aria-invalid={Boolean(error)}
                    aria-describedby={errorId}
                    className={`w-full rounded-2xl border bg-cyan-50 px-4 py-3 text-[0.95rem] text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 disabled:cursor-not-allowed disabled:opacity-60 ${Icon ? 'pr-11' : ''} ${
                        error ? 'border-red-400' : 'border-cyan-500/20'
                    } ${inputClassName}`}
                />
                {Icon && <Icon aria-hidden="true" size={17} strokeWidth={1.9} className="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-500" />}
            </div>

            <FormError id={errorId} message={error} />
        </div>
    );
}
