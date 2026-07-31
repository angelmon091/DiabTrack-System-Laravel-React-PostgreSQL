import Apple from 'lucide-react/dist/esm/icons/apple.mjs';
import BatteryFull from 'lucide-react/dist/esm/icons/battery-full.mjs';
import BatteryLow from 'lucide-react/dist/esm/icons/battery-low.mjs';
import BatteryMedium from 'lucide-react/dist/esm/icons/battery-medium.mjs';
import BatteryWarning from 'lucide-react/dist/esm/icons/battery-warning.mjs';
import Clock3 from 'lucide-react/dist/esm/icons/clock-3.mjs';
import CloudSun from 'lucide-react/dist/esm/icons/cloud-sun.mjs';
import Coffee from 'lucide-react/dist/esm/icons/coffee.mjs';
import Cookie from 'lucide-react/dist/esm/icons/cookie.mjs';
import Frown from 'lucide-react/dist/esm/icons/frown.mjs';
import Gauge from 'lucide-react/dist/esm/icons/gauge.mjs';
import Meh from 'lucide-react/dist/esm/icons/meh.mjs';
import Moon from 'lucide-react/dist/esm/icons/moon.mjs';
import Smile from 'lucide-react/dist/esm/icons/smile.mjs';
import Sun from 'lucide-react/dist/esm/icons/sun.mjs';
import Utensils from 'lucide-react/dist/esm/icons/utensils.mjs';

import FormError from './FormError';
import InfoTooltip from './InfoTooltip';

const icons = { apple: Apple, batteryFull: BatteryFull, batteryLow: BatteryLow, batteryMedium: BatteryMedium, batteryWarning: BatteryWarning, clock: Clock3, cloudSun: CloudSun, coffee: Coffee, cookie: Cookie, frown: Frown, gauge: Gauge, meh: Meh, moon: Moon, smile: Smile, sun: Sun, utensils: Utensils };

export default function ChoiceCards({ legend, name, options, value, onChange, error, help, optional = false }) {
    return <fieldset><legend className="inline-flex items-center gap-1.5 text-sm font-bold text-slate-700">{legend}{help && <InfoTooltip text={help} />}{optional && <span className="ml-1 font-normal text-slate-400">(opcional)</span>}</legend><div className="mt-3 grid gap-3 sm:grid-cols-2">{options.map((option) => { const Icon = icons[option.icon]; return <label key={option.value} className={`cursor-pointer rounded-2xl border p-4 transition ${value === option.value ? 'border-cyan-500 bg-cyan-50 ring-2 ring-cyan-500/10' : 'border-slate-200 bg-white hover:border-cyan-300'}`}><input type="radio" name={name} value={option.value} checked={value === option.value} onChange={() => onChange(option.value)} className="sr-only" />{Icon && <Icon aria-hidden="true" size={22} strokeWidth={2.1} className={`mb-2 ${option.iconClass || 'text-cyan-600'}`} />}<strong className="block text-sm text-slate-800">{option.label}</strong><span className="mt-1 block text-xs text-slate-500">{option.description}</span></label>; })}</div><FormError message={error} /></fieldset>;
}
