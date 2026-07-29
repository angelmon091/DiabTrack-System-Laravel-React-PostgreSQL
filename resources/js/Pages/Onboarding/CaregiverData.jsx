import { Head, Link, useForm } from '@inertiajs/react';

import FormError from '../../Components/FormError';
import FormSelect from '../../Components/FormSelect';
import SubmitButton from '../../Components/SubmitButton';
import GuestLayout from '../../Layouts/GuestLayout';

export default function CaregiverData({ storeUrl, backUrl, relationships }) {
    const form = useForm({ gender: '', relationship: '' });

    function submit(event) {
        event.preventDefault();
        form.post(storeUrl, { preserveScroll: true });
    }

    return (
        <GuestLayout>
            <Head title="Perfil de cuidador" />

            <div className="mb-8 text-center">
                <h2 className="text-3xl font-extrabold text-cyan-600">Perfil de cuidador</h2>
                <p className="mt-2 text-sm text-slate-500">Ayúdanos a personalizar las herramientas de supervisión.</p>
            </div>

            <form onSubmit={submit} noValidate data-testid="caregiver-data-form" className="space-y-6">
                <fieldset>
                    <legend className="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500">Género</legend>
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

                <FormSelect id="relationship" name="relationship" label="Parentesco con el paciente" value={form.data.relationship} onChange={(event) => form.setData('relationship', event.target.value)} error={form.errors.relationship} required>
                    <option value="">Selecciona una opción</option>
                    {relationships.map((relationship) => <option key={relationship.value} value={relationship.value}>{relationship.label}</option>)}
                </FormSelect>

                <SubmitButton processing={form.processing}>Completar registro</SubmitButton>

                <p className="text-center text-sm">
                    <Link href={backUrl} className="font-semibold text-slate-500 hover:text-cyan-600">Volver a selección de rol</Link>
                </p>
            </form>
        </GuestLayout>
    );
}
