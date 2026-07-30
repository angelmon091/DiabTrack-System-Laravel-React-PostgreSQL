import { Head, Link, useForm } from '@inertiajs/react';
import UserCog from 'lucide-react/dist/esm/icons/user-cog.mjs';

import FormError from '../../Components/FormError';
import FormInput from '../../Components/FormInput';
import FormSelect from '../../Components/FormSelect';
import OnboardingHeader from '../../Components/OnboardingHeader';
import SubmitButton from '../../Components/SubmitButton';
import GuestLayout from '../../Layouts/GuestLayout';

export default function PatientData({
    storeUrl,
    backUrl,
    months,
    maximumBirthYear,
    minimumBirthYear,
    glycemicConditions,
}) {
    const years = Array.from(
        { length: maximumBirthYear - minimumBirthYear + 1 },
        (_, index) => maximumBirthYear - index,
    );
    const form = useForm({
        birth_day: '1',
        birth_month: months[0],
        birth_year: String(maximumBirthYear),
        diabetes_type: glycemicConditions[0]?.value ?? '',
        weight: '',
        height: '',
        gender: '',
    });

    function submit(event) {
        event.preventDefault();
        form.post(storeUrl, { preserveScroll: true });
    }

    return (
        <GuestLayout>
            <Head title="Datos personales" />

            <OnboardingHeader icon={UserCog} title="Datos Personales" description="Completa tu perfil para una mejor experiencia." />

            <form onSubmit={submit} noValidate data-testid="patient-data-form" className="space-y-5">
                <fieldset>
                    <legend className="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Fecha de nacimiento</legend>
                    <div className="grid grid-cols-3 gap-2">
                        <FormSelect id="birth_day" name="birth_day" label="Día" value={form.data.birth_day} onChange={(event) => form.setData('birth_day', event.target.value)} error={form.errors.birth_day}>
                            {Array.from({ length: 31 }, (_, index) => index + 1).map((day) => <option key={day} value={day}>{day}</option>)}
                        </FormSelect>
                        <FormSelect id="birth_month" name="birth_month" label="Mes" value={form.data.birth_month} onChange={(event) => form.setData('birth_month', event.target.value)} error={form.errors.birth_month}>
                            {months.map((month) => <option key={month} value={month}>{month}</option>)}
                        </FormSelect>
                        <FormSelect id="birth_year" name="birth_year" label="Año" value={form.data.birth_year} onChange={(event) => form.setData('birth_year', event.target.value)} error={form.errors.birth_year}>
                            {years.map((year) => <option key={year} value={year}>{year}</option>)}
                        </FormSelect>
                    </div>
                    <FormError message={form.errors.birth_date} className="mt-2" />
                </fieldset>

                <FormSelect id="diabetes_type" name="diabetes_type" label="Condición glucémica" value={form.data.diabetes_type} onChange={(event) => form.setData('diabetes_type', event.target.value)} error={form.errors.diabetes_type} required>
                    {glycemicConditions.map((condition) => <option key={condition.value} value={condition.value}>{condition.label}</option>)}
                </FormSelect>

                <div className="grid grid-cols-2 gap-3">
                    <FormInput id="weight" name="weight" type="number" step="0.1" min="20" max="300" label="Peso (kg)" value={form.data.weight} onChange={(event) => form.setData('weight', event.target.value)} placeholder="00.0" required error={form.errors.weight} />
                    <FormInput id="height" name="height" type="number" min="50" max="250" label="Estatura (cm)" value={form.data.height} onChange={(event) => form.setData('height', event.target.value)} placeholder="000" required error={form.errors.height} />
                </div>

                <fieldset>
                    <legend className="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Género</legend>
                    <div className="grid grid-cols-2 gap-3">
                        {['Masculino', 'Femenino'].map((gender) => (
                            <label key={gender} className={`cursor-pointer rounded-2xl border px-4 py-3 text-center text-sm font-semibold transition ${form.data.gender === gender ? 'border-cyan-500 bg-cyan-50 text-cyan-700' : 'border-slate-200 text-slate-600 hover:border-cyan-300'}`}>
                                <input type="radio" name="gender" value={gender} checked={form.data.gender === gender} onChange={(event) => form.setData('gender', event.target.value)} className="sr-only" />
                                {gender}
                            </label>
                        ))}
                    </div>
                    <FormError message={form.errors.gender} />
                </fieldset>

                <SubmitButton processing={form.processing}>Registrar Datos</SubmitButton>
            </form>
        </GuestLayout>
    );
}
