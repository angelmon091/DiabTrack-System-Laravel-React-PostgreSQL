import { Head, useForm } from '@inertiajs/react';

import ChoiceCards from '../../../Components/ChoiceCards';
import FormError from '../../../Components/FormError';
import FormInput from '../../../Components/FormInput';
import RangeField from '../../../Components/RangeField';
import SubmitButton from '../../../Components/SubmitButton';
import TrackingNav from '../../../Components/TrackingNav';
import TrackingPageHeader from '../../../Components/TrackingPageHeader';
import AuthenticatedLayout from '../../../Layouts/AuthenticatedLayout';

const defaults = { meal_type: 'desayuno', carbs_grams: 50, consumed_at: '', food_categories: [], medication_taken: '', medication_dose: '' };

export default function Create({ storeUrl, dashboardUrl, trackingNavigation, mealTypes, foodCategories }) {
    const form = useForm(defaults);
    const toggleCategory = (value) => form.setData('food_categories', form.data.food_categories.includes(value) ? form.data.food_categories.filter((item) => item !== value) : [...form.data.food_categories, value]);
    const submit = (event) => { event.preventDefault(); form.post(storeUrl, { preserveScroll: true }); };
    const reset = () => { form.setData({ ...defaults }); form.clearErrors(); };

    return <AuthenticatedLayout>
        <Head title="Registro de nutrición" />
        <section data-testid="nutrition-create">
            <TrackingPageHeader title="Registro de Alimentación" subtitle="Anota lo que comiste y cuántos carbohidratos aproximados tenía" />
            <TrackingNav items={trackingNavigation} active="nutrition" />
            <form onSubmit={submit} noValidate className="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(300px,0.75fr)] items-start">
                <div className="space-y-6">
                    <div className="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <RangeField id="carbs_grams" name="carbs_grams" label="Carbohidratos" help="Cantidad aproximada de carbohidratos de tu comida, en gramos." value={form.data.carbs_grams} unit="g" min="0" max="300" onChange={(event) => form.setData('carbs_grams', Number(event.target.value))} error={form.errors.carbs_grams} />
                        <FormInput id="consumed_at" name="consumed_at" type="time" label="Hora de consumo (opcional)" help="Hora aproximada en que comiste." value={form.data.consumed_at} onChange={(event) => form.setData('consumed_at', event.target.value)} error={form.errors.consumed_at} />
                        <fieldset><legend className="text-sm font-bold text-slate-700">Categorías de alimentos (opcional)</legend><div className="mt-3 grid gap-3 sm:grid-cols-2">{foodCategories.map((category) => <label key={category.value} className={`flex cursor-pointer items-center gap-3 rounded-2xl border p-4 ${form.data.food_categories.includes(category.value) ? 'border-cyan-500 bg-cyan-50' : 'border-slate-200'}`}><input type="checkbox" value={category.value} checked={form.data.food_categories.includes(category.value)} onChange={() => toggleCategory(category.value)} className="rounded border-cyan-500/30 text-cyan-600" /><span className="text-sm font-semibold text-slate-700">{category.label}</span></label>)}</div><FormError message={form.errors.food_categories} /></fieldset>
                    </div>
                    <div className="space-y-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="font-bold text-slate-800">Medicación <span className="font-normal text-slate-400">(opcional)</span></h2>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <FormInput id="medication_taken" name="medication_taken" label="Medicamento" help="Nombre del medicamento que tomaste con esta comida." maxLength={100} placeholder="Ej: Insulina, Metformina..." value={form.data.medication_taken} onChange={(event) => form.setData('medication_taken', event.target.value)} error={form.errors.medication_taken} />
                            <FormInput id="medication_dose" name="medication_dose" label="Dosis" help="Cantidad del medicamento tomado." maxLength={50} placeholder="Ej: 10 unidades, 500mg..." value={form.data.medication_dose} onChange={(event) => form.setData('medication_dose', event.target.value)} error={form.errors.medication_dose} />
                        </div>
                    </div>
                </div>
                <aside className="space-y-6">
                    <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <ChoiceCards legend="¿Qué comida fue?" help="Selecciona si fue desayuno, comida, cena o una colacion." name="meal_type" options={mealTypes} value={form.data.meal_type} onChange={(value) => form.setData('meal_type', value)} error={form.errors.meal_type} />
                    </div>
                    <div className="flex flex-col-reverse gap-3 sm:flex-row"><button type="button" onClick={reset} className="flex-1 rounded-2xl border px-4 py-3 text-sm font-semibold text-slate-600">Borrar</button><SubmitButton processing={form.processing} className="flex-1">Guardar</SubmitButton></div>
                </aside>
            </form>
        </section>
    </AuthenticatedLayout>;
}
