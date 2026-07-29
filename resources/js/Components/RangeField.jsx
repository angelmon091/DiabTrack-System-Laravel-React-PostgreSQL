import FormError from './FormError';

export default function RangeField({ id, label, value, unit, error, ...inputProps }) {
    return <div><label htmlFor={id} className="mb-3 flex justify-between text-sm font-semibold text-slate-600"><span>{label}</span><strong className="text-cyan-700">{value} {unit}</strong></label><input {...inputProps} id={id} type="range" value={value} aria-invalid={Boolean(error)} className="w-full accent-cyan-600" /><FormError message={error} /></div>;
}
