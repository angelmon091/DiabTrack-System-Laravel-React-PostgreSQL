import Mars from 'lucide-react/dist/esm/icons/mars.mjs';
import Venus from 'lucide-react/dist/esm/icons/venus.mjs';

import FormError from './FormError';

const options = [{ value: 'Masculino', icon: Mars }, { value: 'Femenino', icon: Venus }];

export default function GenderSelector({ value, onChange, error }) {
    return (
        <fieldset>
            <legend className="mb-3 block w-full text-center text-xs font-bold uppercase tracking-wide text-slate-500">Género</legend>
            <div className="grid grid-cols-2 gap-3">
                {options.map(({ value: option, icon: Icon }) => (
                    <label key={option} className={`cursor-pointer rounded-2xl border px-4 py-3 text-center text-sm font-semibold transition ${value === option ? 'border-cyan-500 bg-cyan-50 text-cyan-700' : 'border-slate-200 text-slate-600 hover:border-cyan-300'}`}>
                        <input type="radio" name="gender" value={option} checked={value === option} onChange={(event) => onChange(event.target.value)} className="sr-only" />
                        <Icon aria-hidden="true" className="mx-auto mb-1 h-6 w-6" strokeWidth={1.8} />
                        {option}
                    </label>
                ))}
            </div>
            <FormError message={error} />
        </fieldset>
    );
}
