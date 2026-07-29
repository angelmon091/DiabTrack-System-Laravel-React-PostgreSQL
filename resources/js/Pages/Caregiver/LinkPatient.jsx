import { Head, useForm } from '@inertiajs/react';

import FormInput from '../../Components/FormInput';
import FormSelect from '../../Components/FormSelect';
import SubmitButton from '../../Components/SubmitButton';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout';

export default function LinkPatient({ storeUrl, dashboardUrl, relationships }) {
    const form = useForm({ invite_code: '', relationship: '' });
    function submit(event) { event.preventDefault(); form.post(storeUrl, { preserveScroll: true }); }
    return <AuthenticatedLayout>
        <Head title="Vincular paciente" />
        <section className="mx-auto max-w-xl rounded-3xl border border-slate-100 bg-white p-6 shadow-lg sm:p-10">
            <div className="mb-8 text-center"><h1 className="text-3xl font-extrabold text-slate-900">Vincular paciente</h1><p className="mt-2 text-sm text-slate-500">Ingresa el código de 6 dígitos que te compartió tu paciente.</p></div>
            <form onSubmit={submit} noValidate data-testid="caregiver-link-form" className="space-y-5">
                <FormInput id="invite_code" name="invite_code" label="Código de invitación" value={form.data.invite_code} onChange={(event) => form.setData('invite_code', event.target.value.toUpperCase().slice(0, 6))} maxLength={6} placeholder="ABC123" inputMode="text" error={form.errors.invite_code} required inputClassName="text-center text-xl font-bold uppercase tracking-widest" />
                <FormSelect id="relationship" name="relationship" label="Parentesco con el paciente" value={form.data.relationship} onChange={(event) => form.setData('relationship', event.target.value)} error={form.errors.relationship} required><option value="">Selecciona el parentesco...</option>{relationships.map((relationship) => <option key={relationship.value} value={relationship.value}>{relationship.label}</option>)}</FormSelect>
                <SubmitButton processing={form.processing}>Vincular</SubmitButton>
                <p className="text-center"><a href={dashboardUrl} className="text-sm font-semibold text-slate-500 hover:text-cyan-600">Volver al panel</a></p>
            </form>
        </section>
    </AuthenticatedLayout>;
}
