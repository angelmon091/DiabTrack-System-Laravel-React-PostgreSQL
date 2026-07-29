import { Head, Link, useForm, usePage } from '@inertiajs/react';

import AuthSessionStatus from '../../Components/AuthSessionStatus';
import FormInput from '../../Components/FormInput';
import SubmitButton from '../../Components/SubmitButton';
import GuestLayout from '../../Layouts/GuestLayout';

export default function ForgotPassword({ passwordEmailUrl, loginUrl }) {
    const { flash } = usePage().props;
    const form = useForm({
        email: '',
    });

    function submit(event) {
        event.preventDefault();
        form.post(passwordEmailUrl, { preserveScroll: true });
    }

    return (
        <GuestLayout>
            <Head title="Recuperar contraseña" />

            <AuthSessionStatus status={flash?.status} />

            <form onSubmit={submit} noValidate data-testid="forgot-password-form">
                <FormInput
                    id="forgot-password-email"
                    name="email"
                    type="email"
                    value={form.data.email}
                    onChange={(event) => form.setData('email', event.target.value)}
                    placeholder="Correo electrónico"
                    autoComplete="username"
                    autoFocus
                    required
                    error={form.errors.email}
                />

                <SubmitButton processing={form.processing} className="mt-4">
                    Enviar enlace
                </SubmitButton>

                <p className="mt-5 text-center text-sm text-slate-500">
                    ¿Recordaste tu contraseña?{' '}
                    <Link href={loginUrl} className="font-semibold text-cyan-600 hover:text-cyan-700 hover:underline">
                        Inicia sesión
                    </Link>
                </p>
            </form>
        </GuestLayout>
    );
}
