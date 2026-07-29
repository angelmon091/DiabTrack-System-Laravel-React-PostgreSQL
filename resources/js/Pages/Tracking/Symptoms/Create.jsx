import { Head, Link, useForm } from '@inertiajs/react';

import FormError from '../../../Components/FormError';
import SubmitButton from '../../../Components/SubmitButton';
import TrackingNav from '../../../Components/TrackingNav';
import AuthenticatedLayout from '../../../Layouts/AuthenticatedLayout';

export default function Create({ storeUrl, dashboardUrl, trackingNavigation, symptomGroups }) {
    const form = useForm({ symptoms: [] });

    function toggleSymptom(id) {
        form.setData('symptoms', form.data.symptoms.includes(id)
            ? form.data.symptoms.filter((symptomId) => symptomId !== id)
            : [...form.data.symptoms, id]);
    }

    function submit(event) {
        event.preventDefault();
        form.post(storeUrl, { preserveScroll: true });
    }

    function reset() {
        form.setData('symptoms', []);
        form.clearErrors();
    }

    return <AuthenticatedLayout>
        <Head title="Registro de síntomas" />
        <section data-testid="symptoms-create">
            <div className="mb-7">
                <h1 className="text-3xl font-extrabold text-slate-900">Registro de síntomas</h1>
                <p className="mt-2 text-slate-500">Selecciona los síntomas que presentas hoy. Si te sientes bien, no es necesario marcar nada.</p>
            </div>

            <TrackingNav items={trackingNavigation} active="symptoms" />

            <form onSubmit={submit} noValidate>
                {symptomGroups.length > 0 ? <div className="grid gap-5 md:grid-cols-2">
                    {symptomGroups.map((group) => <fieldset key={group.key} className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <legend className="px-2 text-lg font-bold text-slate-800">{group.label}</legend>
                        <div className="mt-2 space-y-3">
                            {group.symptoms.map((symptom) => <label key={symptom.id} className={`flex cursor-pointer items-center gap-3 rounded-2xl border p-4 transition ${form.data.symptoms.includes(symptom.id) ? 'border-cyan-500 bg-cyan-50 ring-2 ring-cyan-500/10' : 'border-slate-200 hover:border-cyan-300'}`}>
                                <input type="checkbox" name="symptoms[]" value={symptom.id} checked={form.data.symptoms.includes(symptom.id)} onChange={() => toggleSymptom(symptom.id)} className="rounded border-cyan-500/30 text-cyan-600 focus:ring-cyan-500/30" />
                                <span className="text-sm font-semibold text-slate-700">{symptom.name}</span>
                            </label>)}
                        </div>
                    </fieldset>)}
                </div> : <div className="rounded-3xl border border-slate-200 bg-white p-10 text-center text-slate-500">No hay síntomas configurados. Contacte al administrador.</div>}

                <FormError message={form.errors.symptoms} />

                <div className="mt-6 flex flex-col-reverse justify-end gap-3 sm:flex-row">
                    <Link href={dashboardUrl} className="rounded-2xl px-5 py-3 text-center text-sm font-semibold text-cyan-700">Volver al dashboard</Link>
                    <button type="button" onClick={reset} className="rounded-2xl border px-5 py-3 text-sm font-semibold text-slate-600">Borrar</button>
                    <SubmitButton processing={form.processing}>Guardar</SubmitButton>
                </div>
            </form>
        </section>
    </AuthenticatedLayout>;
}
