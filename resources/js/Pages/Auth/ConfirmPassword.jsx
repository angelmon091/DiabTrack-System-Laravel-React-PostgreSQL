import { Head, useForm } from '@inertiajs/react';

import FormInput from '../../Components/FormInput';
import SubmitButton from '../../Components/SubmitButton';
import GuestLayout from '../../Layouts/GuestLayout';

export default function ConfirmPassword({ confirmPasswordUrl }) {
    const form = useForm({
        password: '',
    });

    function submit(event) {
        event.preventDefault();

        form.post(confirmPasswordUrl, {
            preserveScroll: true,
            onFinish: () => form.reset('password'),
        });
    }

    return (
        <GuestLayout>
            <Head title="Confirmar contraseña" />

            <p className="mb-4 text-sm leading-6 text-slate-600">
                Esta es un área segura. Por favor confirma tu contraseña antes de continuar.
            </p>

            <form onSubmit={submit} noValidate data-testid="confirm-password-form">
                <FormInput
                    id="confirm-current-password"
                    name="password"
                    type="password"
                    value={form.data.password}
                    onChange={(event) => form.setData('password', event.target.value)}
                    placeholder="Contraseña"
                    autoComplete="current-password"
                    autoFocus
                    required
                    error={form.errors.password}
                />

                <SubmitButton processing={form.processing} className="mt-4">
                    Confirmar
                </SubmitButton>
            </form>
        </GuestLayout>
    );
}
