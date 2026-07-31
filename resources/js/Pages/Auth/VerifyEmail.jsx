import { Head, useForm, usePage } from '@inertiajs/react';

import AuthSessionStatus from '../../Components/AuthSessionStatus';
import FormInput from '../../Components/FormInput';
import SubmitButton from '../../Components/SubmitButton';
import GuestLayout from '../../Layouts/GuestLayout';

export default function VerifyEmail({ email, verificationCodeUrl, resendUrl, logoutUrl }) {
    const { flash } = usePage().props;
    const verificationForm = useForm({ code: '' });
    const resendForm = useForm({});
    const logoutForm = useForm({});
    const status = flash?.status === 'verification-code-sent'
        ? 'Se envió un código nuevo. Revisa también tu carpeta de correo no deseado.'
        : null;

    function verify(event) {
        event.preventDefault();
        verificationForm.post(verificationCodeUrl, { preserveScroll: true });
    }

    function resend(event) {
        event.preventDefault();
        resendForm.post(resendUrl, {
            preserveScroll: true,
            onSuccess: () => verificationForm.reset('code'),
        });
    }

    function logout(event) {
        event.preventDefault();
        logoutForm.post(logoutUrl);
    }

    return (
        <GuestLayout>
            <Head title="Verificar correo" />

            <p className="mb-4 text-sm leading-6 text-slate-600">
                Se envió un código de seis dígitos a <strong>{email}</strong>. Ingrésalo para verificar tu correo y continuar con el registro.
            </p>

            <AuthSessionStatus status={status} />

            <form onSubmit={verify} noValidate data-testid="verify-email-form">
                <FormInput
                    id="verification-code"
                    name="code"
                    type="text"
                    inputMode="numeric"
                    pattern="[0-9]{6}"
                    maxLength={6}
                    autoComplete="one-time-code"
                    value={verificationForm.data.code}
                    onChange={(event) => verificationForm.setData('code', event.target.value.replace(/\D/g, '').slice(0, 6))}
                    label="Código de verificación"
                    autoFocus
                    required
                    error={verificationForm.errors.code}
                />

                <SubmitButton processing={verificationForm.processing} className="mt-4">
                    Verificar correo
                </SubmitButton>
            </form>

            <div className="mt-5 flex flex-wrap items-center justify-between gap-4 text-sm">
                <form onSubmit={resend}>
                    <button
                        type="submit"
                        disabled={resendForm.processing}
                        className="text-slate-500 underline transition hover:text-cyan-600 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {resendForm.processing ? 'Reenviando...' : 'Reenviar código'}
                    </button>
                </form>

                <form onSubmit={logout}>
                    <button
                        type="submit"
                        disabled={logoutForm.processing}
                        className="text-slate-500 underline transition hover:text-cyan-600 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </GuestLayout>
    );
}
