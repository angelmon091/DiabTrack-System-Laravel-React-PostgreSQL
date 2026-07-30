import { Head, Link, useForm, usePage } from '@inertiajs/react';

import AuthSessionStatus from '../../Components/AuthSessionStatus';
import Checkbox from '../../Components/Checkbox';
import FormInput from '../../Components/FormInput';
import SubmitButton from '../../Components/SubmitButton';
import GuestLayout from '../../Layouts/GuestLayout';

export default function Login({
    loginUrl,
    forgotPasswordUrl,
    registerUrl,
    googleLoginUrl,
}) {
    const { flash } = usePage().props;
    const form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    function submit(event) {
        event.preventDefault();

        form.post(loginUrl, {
            preserveScroll: true,
            onFinish: () => form.reset('password'),
        });
    }

    return (
        <GuestLayout>
            <Head title="Iniciar sesión" />

            <AuthSessionStatus status={flash?.status} />

            <form onSubmit={submit} noValidate data-testid="login-form">
                <div className="space-y-4">
                    <FormInput
                        id="email"
                        name="email"
                        type="email"
                        value={form.data.email}
                        onChange={(event) => form.setData('email', event.target.value)}
                        placeholder="Correo Electrónico"
                        autoComplete="username"
                        autoFocus
                        required
                        error={form.errors.email}
                    />

                    <FormInput
                        id="password"
                        name="password"
                        type="password"
                        value={form.data.password}
                        onChange={(event) => form.setData('password', event.target.value)}
                        placeholder="Contraseña"
                        autoComplete="current-password"
                        required
                        error={form.errors.password}
                    />
                </div>

                <div className="mt-3 flex flex-col items-start gap-3">
                    <Link
                        href={forgotPasswordUrl}
                        className="text-sm text-slate-500 transition hover:text-cyan-600"
                    >
                        ¿Olvidó su contraseña?
                    </Link>

                    <Checkbox
                        id="remember"
                        name="remember"
                        checked={form.data.remember}
                        onChange={(event) => form.setData('remember', event.target.checked)}
                        label="Recuérdame"
                    />
                </div>

                <SubmitButton processing={form.processing} className="mt-4">
                    Iniciar Sesión
                </SubmitButton>

                <div className="relative my-6 text-center">
                    <div className="absolute inset-0 flex items-center" aria-hidden="true">
                        <div className="w-full border-t border-slate-200" />
                    </div>
                    <span className="relative bg-white/85 px-4 text-xs font-semibold uppercase tracking-widest text-slate-500">
                        O
                    </span>
                </div>

                <a
                    href={googleLoginUrl}
                    className="flex w-full items-center justify-center gap-3 rounded-2xl border border-cyan-500/20 bg-white/80 px-4 py-3 text-sm font-medium text-slate-600 transition hover:-translate-y-px hover:border-cyan-500 hover:bg-cyan-50"
                >
                    <img
                        src="/img/medios/logos/google.png"
                        alt=""
                        className="h-4 w-4"
                    />
                    Continuar con Google
                </a>

                <p className="mt-5 text-center text-sm text-slate-500">
                    ¿No tienes una cuenta?{' '}
                    <Link href={registerUrl} className="font-semibold text-cyan-600 hover:text-cyan-700 hover:underline">
                        Regístrate
                    </Link>
                </p>
            </form>
        </GuestLayout>
    );
}
