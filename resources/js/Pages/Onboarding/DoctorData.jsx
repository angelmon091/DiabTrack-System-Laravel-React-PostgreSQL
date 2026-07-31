import { Head, Link, useForm } from '@inertiajs/react';
import Stethoscope from 'lucide-react/dist/esm/icons/stethoscope.mjs';

import GenderSelector from '../../Components/GenderSelector';
import FormInput from '../../Components/FormInput';
import FormSelect from '../../Components/FormSelect';
import OnboardingHeader from '../../Components/OnboardingHeader';
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
            <OnboardingHeader icon={Stethoscope} iconClassName="bg-blue-100 text-blue-600" title="Perfil Profesional" description="Habilita tus herramientas de monitoreo clínico." />
            <form onSubmit={submit} noValidate data-testid="doctor-data-form" className="space-y-5">
                <GenderSelector value={form.data.gender} onChange={(gender) => form.setData('gender', gender)} error={form.errors.gender} />
                <FormInput id="license_number" name="license_number" label="Cédula profesional" value={form.data.license_number} onChange={(event) => form.setData('license_number', event.target.value)} placeholder="Ej. 12345678" error={form.errors.license_number} required />
                <FormSelect id="specialty" name="specialty" label="Especialidad" value={form.data.specialty} onChange={(event) => form.setData('specialty', event.target.value)} error={form.errors.specialty} required>
                    <option value="">Selecciona especialidad</option>
                    {specialties.map((specialty) => <option key={specialty} value={specialty}>{specialty}</option>)}
                </FormSelect>
                <SubmitButton processing={form.processing}>Activar Perfil Médico</SubmitButton>
            </form>
        </GuestLayout>
    );
}
