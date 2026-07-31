import { Head, useForm } from '@inertiajs/react';

import ChoiceCards from '../../../Components/ChoiceCards';
import FormInput from '../../../Components/FormInput';
import FormTextarea from '../../../Components/FormTextarea';
import RangeField from '../../../Components/RangeField';
import InfoTooltip from '../../../Components/InfoTooltip';
import SubmitButton from '../../../Components/SubmitButton';
import TrackingNav from '../../../Components/TrackingNav';
import TrackingPageHeader from '../../../Components/TrackingPageHeader';
import AuthenticatedLayout from '../../../Layouts/AuthenticatedLayout';

const defaults = { glucose_level: 120, systolic: '', diastolic: '', heart_rate: 75, hba1c: '', measurement_moment: 'Ayunas', stress_level: '', notes: '' };

export default function Create({ storeUrl, dashboardUrl, trackingNavigation, measurementMoments, stressLevels }) {
    const form = useForm(defaults);
    function submit(event) { event.preventDefault(); form.post(storeUrl, { preserveScroll: true }); }
    function reset() { form.setData({ ...defaults }); form.clearErrors(); }
    return <AuthenticatedLayout>
        <Head title="Registro de signos vitales" />
        <section data-testid="vitals-create"><TrackingPageHeader title="Registro de Signos Vitales" subtitle="Registra tus mediciones corporales para un mejor control" /><TrackingNav items={trackingNavigation} active="vitals" />
            <form onSubmit={submit} noValidate className="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(300px,0.75fr)]">
                <div className="space-y-6"><div className="space-y-7 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><RangeField id="glucose_level" name="glucose_level" label="Nivel de Glucosa (Azúcar)" help="Tu nivel de azúcar se mide con un glucómetro pinchando el dedo o con un sensor." value={form.data.glucose_level} unit="mg/dL" min="40" max="300" onChange={(event) => form.setData('glucose_level', Number(event.target.value))} error={form.errors.glucose_level} /><div><p className="mb-3 text-sm font-bold text-slate-700">Presión Arterial (Sistólica / Diastólica) <span className="font-normal text-slate-400">(Opcional)</span></p><div className="grid gap-4 sm:grid-cols-2"><FormInput id="systolic" name="systolic" type="number" min="40" max="250" label="Sistólica" placeholder="Sistólica" value={form.data.systolic} onChange={(event) => form.setData('systolic', event.target.value)} error={form.errors.systolic} /><FormInput id="diastolic" name="diastolic" type="number" min="30" max="150" label="Diastólica" placeholder="Diastólica" value={form.data.diastolic} onChange={(event) => form.setData('diastolic', event.target.value)} error={form.errors.diastolic} /></div></div><RangeField id="heart_rate" name="heart_rate" label="Frecuencia Cardiaca (Opcional)" help="Son tus latidos por minuto. Puedes verlos en un reloj inteligente o sentir el pulso en tu muñeca." value={form.data.heart_rate} unit="bpm" min="40" max="200" onChange={(event) => form.setData('heart_rate', Number(event.target.value))} error={form.errors.heart_rate} /><FormInput id="hba1c" name="hba1c" type="number" step="0.1" min="3" max="15" label="Hemoglobina Glicosilada (HbA1c) (Opcional)" placeholder="% de HbA1c" value={form.data.hba1c} onChange={(event) => form.setData('hba1c', event.target.value)} error={form.errors.hba1c} /></div><div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><FormTextarea id="notes" name="notes" rows={4} maxLength={1000} label="Notas Adicionales (Opcional)" placeholder="Ej: Fui a una fiesta y comí pastel..." value={form.data.notes} onChange={(event) => form.setData('notes', event.target.value)} error={form.errors.notes} /></div></div>
                <aside className="space-y-6"><div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><ChoiceCards legend="¿Cuándo mediste?" name="measurement_moment" options={measurementMoments} value={form.data.measurement_moment} onChange={(value) => form.setData('measurement_moment', value)} error={form.errors.measurement_moment} /></div><div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><ChoiceCards legend="Nivel de Estrés" name="stress_level" options={stressLevels} value={form.data.stress_level} onChange={(value) => form.setData('stress_level', value)} error={form.errors.stress_level} optional /></div><div className="flex flex-col-reverse gap-3 sm:flex-row"><button type="button" onClick={reset} className="flex-1 rounded-2xl border px-4 py-3 text-sm font-semibold text-slate-600">Borrar</button><SubmitButton processing={form.processing} className="flex-1">Guardar</SubmitButton></div></aside>
            </form>
        </section>
    </AuthenticatedLayout>;
}
