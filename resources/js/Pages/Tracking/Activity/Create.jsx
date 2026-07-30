import { Head, useForm } from '@inertiajs/react';

import ChoiceCards from '../../../Components/ChoiceCards';
import FormInput from '../../../Components/FormInput';
import FormSelect from '../../../Components/FormSelect';
import RangeField from '../../../Components/RangeField';
import SubmitButton from '../../../Components/SubmitButton';
import TrackingNav from '../../../Components/TrackingNav';
import TrackingPageHeader from '../../../Components/TrackingPageHeader';
import AuthenticatedLayout from '../../../Layouts/AuthenticatedLayout';

const defaults = {
    activity_type: '',
    duration_minutes: 30,
    intensity: 'media',
    start_time: '',
    end_time: '',
    energy_level: 'normal',
};

export default function Create({ storeUrl, dashboardUrl, trackingNavigation, activityTypes, intensities, energyLevels }) {
    const form = useForm(defaults);

    function submit(event) {
        event.preventDefault();
        form.post(storeUrl, { preserveScroll: true });
    }

    function reset() {
        form.setData({ ...defaults });
        form.clearErrors();
    }

    return <AuthenticatedLayout>
        <Head title="Registro de movimiento" />
        <section data-testid="activity-create">
            <TrackingPageHeader title="Registro de Actividad Física" subtitle="Registra tu movimiento y nivel de energía diario" />

            <TrackingNav items={trackingNavigation} active="activity" />

            <form onSubmit={submit} noValidate className="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(300px,0.75fr)]">
                <div className="space-y-6">
                    <div className="space-y-7 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <FormSelect id="activity_type" name="activity_type" label="Tipo de Actividad" value={form.data.activity_type} onChange={(event) => form.setData('activity_type', event.target.value)} error={form.errors.activity_type}>
                            <option value="" disabled>Selecciona una actividad</option>
                            {activityTypes.map((activity) => <option key={activity.value} value={activity.value}>{activity.label}</option>)}
                        </FormSelect>

                        <RangeField id="duration_minutes" name="duration_minutes" label="Duración" value={form.data.duration_minutes} unit="min" min="1" max="180" onChange={(event) => form.setData('duration_minutes', Number(event.target.value))} error={form.errors.duration_minutes} />

                        <ChoiceCards legend="Nivel de Energía" name="energy_level" options={energyLevels} value={form.data.energy_level} onChange={(value) => form.setData('energy_level', value)} error={form.errors.energy_level} />
                    </div>
                </div>

                <aside className="space-y-6">
                    <div className="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <ChoiceCards legend="Intensidad" name="intensity" options={intensities} value={form.data.intensity} onChange={(value) => form.setData('intensity', value)} error={form.errors.intensity} />
                        <div className="grid gap-4 sm:grid-cols-2">
                            <FormInput id="start_time" name="start_time" type="time" label="Inicio (opcional)" value={form.data.start_time} onChange={(event) => form.setData('start_time', event.target.value)} error={form.errors.start_time} />
                            <FormInput id="end_time" name="end_time" type="time" label="Fin (opcional)" value={form.data.end_time} onChange={(event) => form.setData('end_time', event.target.value)} error={form.errors.end_time} />
                        </div>
                    </div>

                    <div className="flex flex-col-reverse gap-3 sm:flex-row">
                        <button type="button" onClick={reset} className="flex-1 rounded-2xl border px-4 py-3 text-sm font-semibold text-slate-600">Borrar</button>
                        <SubmitButton processing={form.processing} className="flex-1">Guardar</SubmitButton>
                    </div>
                </aside>
            </form>
        </section>
    </AuthenticatedLayout>;
}
