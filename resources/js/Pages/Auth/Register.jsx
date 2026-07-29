import { Head, Link, useForm } from '@inertiajs/react';

import FormInput from '../../Components/FormInput';
import SubmitButton from '../../Components/SubmitButton';
import GuestLayout from '../../Layouts/GuestLayout';

export default function Register({ registerUrl, loginUrl, googleLoginUrl }) {
    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    function submit(event) {
        event.preventDefault();

        form.post(registerUrl, {
            preserveScroll: true,
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    }

    return (
        <GuestLayout>
            <Head title="Registro" />

            <form onSubmit={submit} noValidate data-testid="register-form">
                <div className="space-y-4">
                    <FormInput
                        id="name"
                        name="name"
                        type="text"
                        value={form.data.name}
                        onChange={(event) => form.setData('name', event.target.value)}
                        placeholder="Nombre completo"
                        autoComplete="name"
                        autoFocus
                        required
                        error={form.errors.name}
                    />

                    <FormInput
                        id="register-email"
                        name="email"
                        type="email"
                        value={form.data.email}
                        onChange={(event) => form.setData('email', event.target.value)}
                        placeholder="Correo electrónico"
                        autoComplete="username"
                        required
                        error={form.errors.email}
                    />

                    <FormInput
                        id="register-password"
                        name="password"
                        type="password"
                        value={form.data.password}
                        onChange={(event) => form.setData('password', event.target.value)}
                        placeholder="Contraseña"
                        autoComplete="new-password"
                        required
                        error={form.errors.password}
                    />

                    <FormInput
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        value={form.data.password_confirmation}
                        onChange={(event) => form.setData('password_confirmation', event.target.value)}
                        placeholder="Confirmar contraseña"
                        autoComplete="new-password"
                        required
                        error={form.errors.password_confirmation}
                    />
                </div>

                <SubmitButton processing={form.processing} className="mt-4">
                    Registrarse
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
                    <img src="/img/medios/logos/google.png" alt="" className="h-4 w-4" />
                    Continuar con Google
                </a>

                <p className="mt-5 text-center text-sm text-slate-500">
                    ¿Ya tienes una cuenta?{' '}
                    <Link href={loginUrl} className="font-semibold text-cyan-600 hover:text-cyan-700 hover:underline">
                        Inicia sesión
                    </Link>
                </p>
            </form>
        </GuestLayout>
    );
}
