import CircleCheck from 'lucide-react/dist/esm/icons/circle-check.mjs';
import ClipboardCheck from 'lucide-react/dist/esm/icons/clipboard-check.mjs';
import IdCard from 'lucide-react/dist/esm/icons/id-card.mjs';
import Info from 'lucide-react/dist/esm/icons/info.mjs';
import KeyRound from 'lucide-react/dist/esm/icons/key-round.mjs';
import ShieldUser from 'lucide-react/dist/esm/icons/shield-user.mjs';

export default function EmptySidebarGuide({ profile }) {
    const isDoctor = profile === 'doctor';
    const accent = isDoctor ? 'bg-cyan-500 text-white' : 'bg-orange-400 text-white';
    const pendingAccent = isDoctor ? 'bg-cyan-50 text-cyan-700' : 'bg-orange-50 text-orange-700';
    const steps = [
        { icon: CircleCheck, title: 'Cuenta configurada', description: 'Tu información de acceso está completa.' },
        isDoctor
            ? { icon: IdCard, title: 'Perfil profesional aprobado', description: 'Tu cédula ya fue validada por DiabTrack.' }
            : { icon: ShieldUser, title: 'Perfil de cuidador activo', description: 'Ya puedes recibir autorización de un paciente.' },
        { icon: KeyRound, title: 'Código pendiente', description: 'Solicita al paciente una invitación temporal.' },
    ];

    return <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div className="mb-6 flex items-center gap-3">
            <span className={`grid h-11 w-11 shrink-0 place-items-center rounded-xl ${accent}`}><ClipboardCheck size={21} /></span>
            <div><h2 className="font-bold text-slate-900">Todo listo para comenzar</h2><p className="text-xs text-slate-500">Solo falta vincular al primer paciente</p></div>
        </div>
        <div className="space-y-4">{steps.map(({ icon: Icon, title, description }, index) => <div key={title} className="flex gap-3">
            <span className={`grid h-[34px] w-[34px] shrink-0 place-items-center rounded-full ${index < 2 ? 'bg-emerald-50 text-emerald-600' : pendingAccent}`}><Icon size={16} /></span>
            <div><strong className="block text-sm">{title}</strong><p className="mt-0.5 text-xs leading-5 text-slate-500">{description}</p></div>
        </div>)}</div>
        <div className="mt-6 flex gap-2 border-t border-slate-100 pt-4 text-slate-500"><Info size={16} className="mt-0.5 shrink-0" /><p className="text-xs leading-5">El código vence por seguridad. Si deja de funcionar, el paciente puede generar uno nuevo desde su panel.</p></div>
    </section>;
}
