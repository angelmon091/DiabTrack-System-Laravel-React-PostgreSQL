import { Head, Link, useForm } from '@inertiajs/react';
import HandHeart from 'lucide-react/dist/esm/icons/hand-heart.mjs';

import GenderSelector from '../../Components/GenderSelector';
import OnboardingHeader from '../../Components/OnboardingHeader';
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

            <OnboardingHeader icon={HandHeart} iconClassName="bg-amber-100 text-amber-600" title="Perfil de Cuidador" description="Ayúdanos a personalizar las herramientas de supervisión." />

            <form onSubmit={submit} noValidate data-testid="caregiver-data-form" className="space-y-6">
                <GenderSelector value={form.data.gender} onChange={(gender) => form.setData('gender', gender)} error={form.errors.gender} />

                <FormSelect id="relationship" name="relationship" label="Parentesco con el paciente" value={form.data.relationship} onChange={(event) => form.setData('relationship', event.target.value)} error={form.errors.relationship} required>
                    <option value="">Selecciona una opción</option>
                    {relationships.map((relationship) => <option key={relationship.value} value={relationship.value}>{relationship.label}</option>)}
                </FormSelect>

                <SubmitButton processing={form.processing}>Completar registro</SubmitButton>

            </form>
        </GuestLayout>
    );
}
