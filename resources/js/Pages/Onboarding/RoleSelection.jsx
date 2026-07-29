import { Head, Link } from '@inertiajs/react';

import GuestLayout from '../../Layouts/GuestLayout';

const choices = [
    {
        key: 'patient',
        title: 'Soy paciente',
        description: 'Gestiona tu glucosa, alimentación y actividad con IA.',
        urlProp: 'patientUrl',
        accent: 'bg-cyan-100 text-cyan-600',
        path: 'M12 21s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 5.6-7 10-7 10Z',
    },
    {
        key: 'caregiver',
        title: 'Soy cuidador',
        description: 'Supervisa y acompaña a tus seres queridos en su salud.',
        urlProp: 'caregiverUrl',
        accent: 'bg-amber-100 text-amber-600',
        path: 'M4 12h4l2-3 4 6 2-3h4M7 6a3 3 0 0 1 5 2 3 3 0 0 1 5-2c3 0 4 4 2 7-2 3-7 7-7 7s-5-4-7-7c-2-3-1-7 2-7Z',
    },
    {
        key: 'doctor',
        title: 'Soy médico',
        description: 'Monitorea métricas clínicas y ajusta metas terapéuticas.',
        urlProp: 'doctorUrl',
        accent: 'bg-blue-100 text-blue-600',
        path: 'M9 3h6v4a3 3 0 0 1-6 0V3Zm3 7v3a5 5 0 0 0 5 5h1m0-3v6m-3-3h6',
    },
];

export default function RoleSelection(props) {
    return (
        <GuestLayout>
            <Head title="Seleccionar rol" />

            <div className="mb-8 text-center">
                <h2 className="text-3xl font-extrabold text-cyan-600">Bienvenido a DiabTrack</h2>
                <p className="mt-2 text-sm text-slate-500">Para comenzar, selecciona el rol que mejor te describa.</p>
            </div>

            <div className="space-y-4" data-testid="role-selection">
                {choices.map((choice) => (
                    ['patient', 'caregiver'].includes(choice.key) ? (
                    <Link
                        key={choice.key}
                        href={props[choice.urlProp]}
                        className="flex items-center gap-4 rounded-2xl border border-cyan-500/20 bg-white/80 p-4 text-left transition hover:-translate-y-0.5 hover:border-cyan-500 hover:bg-cyan-50 hover:shadow-lg"
                    >
                        <span className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-xl ${choice.accent}`}>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className="h-7 w-7">
                                <path d={choice.path} />
                            </svg>
                        </span>
                        <span>
                            <strong className="block text-slate-900">{choice.title}</strong>
                            <span className="mt-1 block text-xs leading-5 text-slate-500">{choice.description}</span>
                        </span>
                    </Link>
                    ) : (
                    <a
                        key={choice.key}
                        href={props[choice.urlProp]}
                        className="flex items-center gap-4 rounded-2xl border border-cyan-500/20 bg-white/80 p-4 text-left transition hover:-translate-y-0.5 hover:border-cyan-500 hover:bg-cyan-50 hover:shadow-lg"
                    >
                        <span className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-xl ${choice.accent}`}>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" className="h-7 w-7">
                                <path d={choice.path} />
                            </svg>
                        </span>
                        <span>
                            <strong className="block text-slate-900">{choice.title}</strong>
                            <span className="mt-1 block text-xs leading-5 text-slate-500">{choice.description}</span>
                        </span>
                    </a>
                    )
                ))}
            </div>
        </GuestLayout>
    );
}
