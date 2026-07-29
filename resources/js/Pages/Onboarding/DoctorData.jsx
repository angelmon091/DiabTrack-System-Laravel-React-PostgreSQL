import { Head, Link, useForm } from '@inertiajs/react';

import FormError from '../../Components/FormError';
import FormInput from '../../Components/FormInput';
import FormSelect from '../../Components/FormSelect';
import SubmitButton from '../../Components/SubmitButton';
import GuestLayout from '../../Layouts/GuestLayout';

export default function DoctorData({ storeUrl, backUrl, specialties }) {
    const form = useForm({ gender: '', license_number: '', specialty: '' });

    function submit(event) {
        event.preventDefault();
        form.post(storeUrl, { preserveScroll: true });
    }

    return (
        <GuestLayout>
            <Head title="Perfil profesional" />
            <div className="mb-8 text-center">
                <h2 className="text-3xl font-extrabold text-cyan-600">Perfil profesional</h2>
                <p className="mt-2 text-sm text-slate-500">Habilita tus herramientas de monitoreo clínico.</p>
            </div>
            <form onSubmit={submit} noValidate data-testid="doctor-data-form" className="space-y-5">
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
                <FormInput id="license_number" name="license_number" label="Cédula profesional" value={form.data.license_number} onChange={(event) => form.setData('license_number', event.target.value)} placeholder="Ej. 12345678" error={form.errors.license_number} required />
                <FormSelect id="specialty" name="specialty" label="Especialidad" value={form.data.specialty} onChange={(event) => form.setData('specialty', event.target.value)} error={form.errors.specialty} required>
                    <option value="">Selecciona especialidad</option>
                    {specialties.map((specialty) => <option key={specialty} value={specialty}>{specialty}</option>)}
                </FormSelect>
                <SubmitButton processing={form.processing}>Activar perfil médico</SubmitButton>
                <p className="text-center text-sm"><Link href={backUrl} className="font-semibold text-slate-500 hover:text-cyan-600">Volver a selección de rol</Link></p>
            </form>
        </GuestLayout>
    );
}
