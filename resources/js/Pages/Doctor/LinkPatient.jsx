import { Head, useForm } from '@inertiajs/react';
import Check from 'lucide-react/dist/esm/icons/check.mjs';
import LinkIcon from 'lucide-react/dist/esm/icons/link.mjs';

import FormInput from '../../Components/FormInput';
import SubmitButton from '../../Components/SubmitButton';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout';

export default function LinkPatient({ storeUrl, dashboardUrl }) {
    const form = useForm({ invite_code: '' });
    function submit(event) { event.preventDefault(); form.post(storeUrl, { preserveScroll: true }); }
    return <AuthenticatedLayout>
        <Head title="Vincular paciente" />
        <section className="mx-auto max-w-xl rounded-3xl border border-slate-100 bg-white p-6 shadow-lg sm:p-10">
            <div className="mb-8 text-center"><span className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-600"><LinkIcon aria-hidden="true" className="h-6 w-6" /></span><h1 className="text-2xl font-bold text-slate-900">Vincular Paciente</h1><p className="mt-2 text-sm text-slate-500">Ingresa el código de 6 dígitos que te compartió tu paciente.</p></div>
            <form onSubmit={submit} noValidate data-testid="doctor-link-form" className="space-y-5">
                <FormInput id="invite_code" name="invite_code" label="Código de invitación" value={form.data.invite_code} onChange={(event) => form.setData('invite_code', event.target.value.toUpperCase().slice(0, 6))} maxLength={6} placeholder="ABC123" error={form.errors.invite_code} required inputClassName="text-center text-xl font-bold uppercase tracking-widest" />
                <SubmitButton processing={form.processing}><Check aria-hidden="true" className="mr-2 inline h-4 w-4" />Vincular</SubmitButton>
            </form>
        </section>
    </AuthenticatedLayout>;
}
