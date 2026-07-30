import { Head, useForm } from '@inertiajs/react';

import FormInput from '../../Components/FormInput';
import SubmitButton from '../../Components/SubmitButton';
import GuestLayout from '../../Layouts/GuestLayout';

export default function ResetPassword({ token, email, passwordStoreUrl }) {
    const form = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    function submit(event) {
        event.preventDefault();

        form.post(passwordStoreUrl, {
            preserveScroll: true,
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    }

    return (
        <GuestLayout>
            <Head title="Restablecer contraseña" />

            <form onSubmit={submit} noValidate data-testid="reset-password-form">
                <input type="hidden" name="token" value={form.data.token} readOnly />

                <div className="space-y-4">
                    <FormInput
                        id="reset-password-email"
                        name="email"
                        type="email"
                        value={form.data.email}
                        onChange={(event) => form.setData('email', event.target.value)}
                        placeholder="Correo Electrónico"
                        autoComplete="username"
                        required
                        readOnly
                        inputClassName="cursor-not-allowed bg-slate-100"
                        error={form.errors.email}
                    />

                    <FormInput
                        id="new-password"
                        name="password"
                        type="password"
                        value={form.data.password}
                        onChange={(event) => form.setData('password', event.target.value)}
                        placeholder="Nueva Contraseña"
                        autoComplete="new-password"
                        autoFocus
                        required
                        error={form.errors.password}
                    />

                    <FormInput
                        id="new-password-confirmation"
                        name="password_confirmation"
                        type="password"
                        value={form.data.password_confirmation}
                        onChange={(event) => form.setData('password_confirmation', event.target.value)}
                        placeholder="Confirmar Nueva Contraseña"
                        autoComplete="new-password"
                        required
                        error={form.errors.password_confirmation}
                    />
                </div>

                <SubmitButton processing={form.processing} className="mt-4">
                    Restablecer Contraseña
                </SubmitButton>
            </form>
        </GuestLayout>
    );
}
