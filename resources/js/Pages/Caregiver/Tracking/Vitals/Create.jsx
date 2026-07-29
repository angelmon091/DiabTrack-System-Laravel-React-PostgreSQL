import { Head, Link, useForm } from '@inertiajs/react';

import ChoiceCards from '../../../../Components/ChoiceCards';
import FormInput from '../../../../Components/FormInput';
import FormTextarea from '../../../../Components/FormTextarea';
import RangeField from '../../../../Components/RangeField';
import SubmitButton from '../../../../Components/SubmitButton';
import AuthenticatedLayout from '../../../../Layouts/AuthenticatedLayout';

const defaults = { glucose_level: 120, systolic: '', diastolic: '', heart_rate: 75, hba1c: '', measurement_moment: 'Ayunas', stress_level: '', notes: '' };

export default function Create({ patient, storeUrl, dashboardUrl, measurementMoments, stressLevels }) {
    const form = useForm(defaults);
    const submit = (event) => { event.preventDefault(); form.post(storeUrl, { preserveScroll: true }); };

    return <AuthenticatedLayout>
        <Head title={`Registrar signos de ${patient.name}`} />
        <section data-testid="caregiver-vitals-create">
            <div className="mb-7"><h1 className="text-3xl font-extrabold text-slate-900">Registro de signos vitales</h1><p className="mt-2 text-slate-500">Registrando datos para <strong>{patient.name}</strong>.</p></div>
            <form onSubmit={submit} noValidate className="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(300px,0.75fr)]">
                <div className="space-y-6"><div className="space-y-7 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><RangeField id="glucose_level" name="glucose_level" label="Nivel de glucosa" value={form.data.glucose_level} unit="mg/dL" min="40" max="300" onChange={(event) => form.setData('glucose_level', Number(event.target.value))} error={form.errors.glucose_level} /><div className="grid gap-4 sm:grid-cols-2"><FormInput id="systolic" name="systolic" type="number" min="40" max="250" label="Presión sistólica (opcional)" value={form.data.systolic} onChange={(event) => form.setData('systolic', event.target.value)} error={form.errors.systolic} /><FormInput id="diastolic" name="diastolic" type="number" min="30" max="180" label="Presión diastólica (opcional)" value={form.data.diastolic} onChange={(event) => form.setData('diastolic', event.target.value)} error={form.errors.diastolic} /></div><RangeField id="heart_rate" name="heart_rate" label="Frecuencia cardiaca (opcional)" value={form.data.heart_rate} unit="bpm" min="40" max="200" onChange={(event) => form.setData('heart_rate', Number(event.target.value))} error={form.errors.heart_rate} /><FormInput id="hba1c" name="hba1c" type="number" step="0.1" min="3" max="20" label="Hemoglobina glicosilada HbA1c (opcional)" value={form.data.hba1c} onChange={(event) => form.setData('hba1c', event.target.value)} error={form.errors.hba1c} /></div><div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><FormTextarea id="notes" name="notes" rows={3} maxLength={1000} label="Notas adicionales (opcional)" value={form.data.notes} onChange={(event) => form.setData('notes', event.target.value)} error={form.errors.notes} /></div></div>
                <aside className="space-y-6"><div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><ChoiceCards legend="¿Cuándo se midió?" name="measurement_moment" options={measurementMoments} value={form.data.measurement_moment} onChange={(value) => form.setData('measurement_moment', value)} error={form.errors.measurement_moment} /></div><div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><ChoiceCards legend="Nivel de estrés percibido" name="stress_level" options={stressLevels} value={form.data.stress_level} onChange={(value) => form.setData('stress_level', value)} error={form.errors.stress_level} optional /></div><div className="flex gap-3"><Link href={dashboardUrl} className="flex-1 rounded-2xl border px-4 py-3 text-center text-sm font-semibold text-slate-600">Cancelar</Link><SubmitButton processing={form.processing} className="flex-1">Guardar registro</SubmitButton></div></aside>
            </form>
        </section>
    </AuthenticatedLayout>;
}
