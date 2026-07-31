import Bell from 'lucide-react/dist/esm/icons/bell.mjs';
import ChartLine from 'lucide-react/dist/esm/icons/chart-line.mjs';
import CircleCheck from 'lucide-react/dist/esm/icons/circle-check.mjs';
import HeartPulse from 'lucide-react/dist/esm/icons/heart-pulse.mjs';
import LinkIcon from 'lucide-react/dist/esm/icons/link.mjs';
import LockKeyhole from 'lucide-react/dist/esm/icons/lock-keyhole.mjs';
import NotepadText from 'lucide-react/dist/esm/icons/notepad-text.mjs';
import Sparkles from 'lucide-react/dist/esm/icons/sparkles.mjs';
import Target from 'lucide-react/dist/esm/icons/target.mjs';
import UserPlus from 'lucide-react/dist/esm/icons/user-plus.mjs';

export default function EmptyLinkedPatients({ profile, linkUrl }) {
    const isDoctor = profile === 'doctor';
    const accent = isDoctor ? 'bg-cyan-50 text-cyan-700' : 'bg-orange-50 text-orange-700';
    const solid = isDoctor ? 'bg-cyan-500' : 'bg-orange-400';
    const capabilities = isDoctor
        ? [
            { icon: ChartLine, title: 'Consultar tendencias', description: 'Revisa registros y evolución de las métricas compartidas.' },
            { icon: Target, title: 'Definir objetivos', description: 'Personaliza los rangos glucémicos de cada paciente.' },
            { icon: NotepadText, title: 'Dar seguimiento', description: 'Accede al historial autorizado desde un mismo lugar.' },
        ]
        : [
            { icon: ChartLine, title: 'Acompañar tendencias', description: 'Consulta los registros que el paciente comparte contigo.' },
            { icon: Bell, title: 'Mantenerte informado', description: 'Identifica cambios recientes para brindar acompañamiento.' },
            { icon: HeartPulse, title: 'Apoyar su seguimiento', description: 'Observa su actividad clínica desde un mismo lugar.' },
        ];
    const steps = [
        ['1', 'Solicita el código', 'El paciente genera una invitación vigente desde su cuenta.'],
        ['2', 'Confirma la vinculación', isDoctor ? 'Ingresa el código para validar el acceso profesional.' : 'Ingresa el código e indica tu relación con el paciente.'],
        ['3', 'Comienza el seguimiento', 'Los datos autorizados aparecerán automáticamente en este panel.'],
    ];

    return <div className="space-y-6">
        <section className="rounded-3xl border border-slate-200 bg-white p-6 text-center shadow-sm md:p-8">
            <span className={`mx-auto grid h-16 w-16 place-items-center rounded-2xl ${accent}`}><UserPlus size={30} /></span>
            <h1 className="mt-5 text-2xl font-bold">Aún no tienes pacientes vinculados</h1>
            <p className="mx-auto mt-3 max-w-2xl text-sm leading-6 text-slate-500">Pide al paciente que genere un <strong className="text-slate-700">código de invitación</strong> desde su panel. El código es temporal y permite confirmar que autorizó el acceso.</p>
            <a href={linkUrl} className="mt-5 inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-sm"><LinkIcon size={17} />Vincular paciente</a>
            <div className="mt-7 grid gap-4 text-left md:grid-cols-3">{steps.map(([number, title, description]) => <div key={number} className="rounded-2xl border border-white bg-slate-50 p-5">
                <div className="mb-3 flex items-center gap-3"><span className={`grid h-[34px] w-[34px] place-items-center rounded-full font-bold ${accent}`}>{number}</span><strong className="text-sm">{title}</strong></div>
                <p className="text-xs leading-5 text-slate-500">{description}</p>
            </div>)}</div>
        </section>
        <div className="grid gap-6 lg:grid-cols-[2fr_1fr]">
            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div className="mb-5 flex items-center gap-3"><span className={`grid h-11 w-11 place-items-center rounded-xl text-white ${solid}`}><Sparkles size={20} /></span><div><h2 className="font-bold">Tu panel después de vincular</h2><p className="text-xs text-slate-500">Herramientas disponibles con autorización del paciente</p></div></div>
                <div className="grid gap-3 md:grid-cols-3">{capabilities.map(({ icon: Icon, title, description }) => <div key={title} className="rounded-2xl bg-slate-50 p-4"><span className={`mb-3 grid h-9 w-9 place-items-center rounded-xl ${accent}`}><Icon size={18} /></span><strong className="block text-sm">{title}</strong><p className="mt-1 text-xs leading-5 text-slate-500">{description}</p></div>)}</div>
            </section>
            <aside className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><span className={`grid h-11 w-11 place-items-center rounded-xl text-white ${solid}`}><LockKeyhole size={20} /></span><h2 className="mt-4 font-bold">Acceso privado y autorizado</h2><p className="mt-2 text-sm leading-6 text-slate-500">DiabTrack solo muestra información de pacientes que aceptaron vincular su cuenta mediante un código temporal.</p><p className="mt-4 flex items-center gap-2 text-sm font-bold text-emerald-600"><CircleCheck size={17} />El paciente conserva el control</p></aside>
        </div>
    </div>;
}
