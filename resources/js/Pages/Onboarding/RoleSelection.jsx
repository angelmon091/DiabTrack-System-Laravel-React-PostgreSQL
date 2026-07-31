import { Head, Link } from '@inertiajs/react';
import HandHeart from 'lucide-react/dist/esm/icons/hand-heart.mjs';
import HeartPulse from 'lucide-react/dist/esm/icons/heart-pulse.mjs';
import Stethoscope from 'lucide-react/dist/esm/icons/stethoscope.mjs';

import GuestLayout from '../../Layouts/GuestLayout';

const choices = [
    {
        key: 'patient',
        title: 'Soy Paciente',
        description: 'Gestiona tu glucosa, alimentación y actividad con IA.',
        urlProp: 'patientUrl',
        accent: 'bg-cyan-100 text-cyan-600',
        icon: HeartPulse,
    },
    {
        key: 'caregiver',
        title: 'Soy Cuidador',
        description: 'Supervisa y acompaña a tus seres queridos en su salud.',
        urlProp: 'caregiverUrl',
        accent: 'bg-amber-100 text-amber-600',
        icon: HandHeart,
    },
    {
        key: 'doctor',
        title: 'Soy Médico',
        description: 'Monitorea métricas clínicas y ajusta metas terapéuticas.',
        urlProp: 'doctorUrl',
        accent: 'bg-blue-100 text-blue-600',
        icon: Stethoscope,
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
                    <Link
                        key={choice.key}
                        href={props[choice.urlProp]}
                        className="flex items-center gap-4 rounded-2xl border border-cyan-500/20 bg-white/80 p-4 text-left transition hover:-translate-y-0.5 hover:border-cyan-500 hover:bg-cyan-50 hover:shadow-lg"
                    >
                        <span className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-xl ${choice.accent}`}>
                            <choice.icon aria-hidden="true" className="h-7 w-7" strokeWidth={1.8} />
                        </span>
                        <span>
                            <strong className="block text-slate-900">{choice.title}</strong>
                            <span className="mt-1 block text-xs leading-5 text-slate-500">{choice.description}</span>
                        </span>
                    </Link>
                ))}
            </div>
        </GuestLayout>
    );
}
