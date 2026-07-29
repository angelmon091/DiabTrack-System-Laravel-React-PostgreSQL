import FormError from './FormError';

export default function ChoiceCards({ legend, name, options, value, onChange, error, optional = false }) {
    return <fieldset><legend className="text-sm font-bold text-slate-700">{legend}{optional && <span className="ml-1 font-normal text-slate-400">(opcional)</span>}</legend><div className="mt-3 grid gap-3 sm:grid-cols-2">{options.map((option) => <label key={option.value} className={`cursor-pointer rounded-2xl border p-4 transition ${value === option.value ? 'border-cyan-500 bg-cyan-50 ring-2 ring-cyan-500/10' : 'border-slate-200 bg-white hover:border-cyan-300'}`}><input type="radio" name={name} value={option.value} checked={value === option.value} onChange={() => onChange(option.value)} className="sr-only" /><strong className="block text-sm text-slate-800">{option.label}</strong><span className="mt-1 block text-xs text-slate-500">{option.description}</span></label>)}</div><FormError message={error} /></fieldset>;
}
