import { Head, Link, usePage } from '@inertiajs/react';
import BarChart3 from 'lucide-react/dist/esm/icons/bar-chart-3.mjs';
import ChevronDown from 'lucide-react/dist/esm/icons/chevron-down.mjs';
import Cpu from 'lucide-react/dist/esm/icons/cpu.mjs';
import HeartPulse from 'lucide-react/dist/esm/icons/heart-pulse.mjs';
import Menu from 'lucide-react/dist/esm/icons/menu.mjs';
import X from 'lucide-react/dist/esm/icons/x.mjs';
import { useState } from 'react';

import BrandMark from '../Components/BrandMark';
import SocialLinks from '../Components/SocialLinks';

const features = [
    {
        title: 'Gestión de datos',
        description: 'Centraliza toda tu información de salud en un solo lugar seguro y accesible.',
        accent: 'bg-cyan-100 text-cyan-600',
        icon: BarChart3,
    },
    {
        title: 'Análisis con IA',
        description: 'Recibe información valiosa procesada por inteligencia artificial sobre tus hábitos.',
        accent: 'bg-emerald-100 text-emerald-600',
        icon: Cpu,
    },
    {
        title: 'Consejos vitales',
        description: 'Recomendaciones personalizadas basadas en tu monitoreo diario y necesidades.',
        accent: 'bg-amber-100 text-amber-600',
        icon: HeartPulse,
    },
];

function FeatureIcon({ icon: Icon }) {
    return <Icon aria-hidden="true" size={32} strokeWidth={2} />;
}

export default function Welcome({ homeUrl, loginUrl, registerUrl, dashboardUrl, ogImageUrl, year }) {
    const { auth } = usePage().props;
    const [menuOpen, setMenuOpen] = useState(false);

    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <Head title="Monitorea tu salud, vive mejor">
                <meta name="description" content="DiabTrack: La plataforma inteligente para el monitoreo de diabetes. Controla tu glucosa, nutrición y actividad con análisis de IA para una vida más saludable." />
                <meta name="keywords" content="diabetes, monitoreo de salud, glucosa, insulina, salud inteligente, seguimiento médico, nutrición diabetes" />
                <meta name="author" content="DiabTrack" />
                <meta name="robots" content="index, follow" />
                <meta property="og:type" content="website" />
                <meta property="og:url" content={homeUrl} />
                <meta property="og:title" content="DiabTrack - Monitorea tu salud, vive mejor" />
                <meta property="og:description" content="Control inteligente de la diabetes con análisis de IA y monitoreo constante de signos vitales." />
                <meta property="og:image" content={ogImageUrl} />
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content="DiabTrack - Monitorea tu salud, vive mejor" />
                <meta name="twitter:description" content="Control inteligente de la diabetes con análisis de IA y monitoreo constante de signos vitales." />
                <meta name="twitter:image" content={ogImageUrl} />
                <link rel="icon" type="image/png" href="/favicon.png" />
            </Head>

            <header className="absolute inset-x-0 top-0 z-20 px-4 py-5 sm:px-8">
                <nav className="mx-auto flex max-w-6xl items-center justify-between" aria-label="Navegación principal">
                    <a href={homeUrl} aria-label="DiabTrack, inicio">
                        <BrandMark className="text-3xl text-white" />
                    </a>

                    <button
                        type="button"
                        className="rounded-xl border border-white/30 p-2 text-white md:hidden"
                        aria-expanded={menuOpen}
                        aria-controls="welcome-menu"
                        onClick={() => setMenuOpen((open) => !open)}
                    >
                        <span className="sr-only">Abrir navegación</span>
                        {menuOpen ? <X aria-hidden="true" size={24} /> : <Menu aria-hidden="true" size={24} />}
                    </button>

                    <div
                        id="welcome-menu"
                        className={`${menuOpen ? 'flex' : 'hidden'} absolute left-4 right-4 top-20 flex-col items-center gap-4 rounded-2xl bg-slate-900/95 p-5 text-white shadow-xl md:static md:flex md:flex-row md:bg-transparent md:p-0 md:shadow-none`}
                    >
                        <a href="#features" className="transition hover:text-cyan-300">Información</a>
                        <a href="#features" className="transition hover:text-cyan-300">Nosotros</a>
                        <a href="#footer" className="transition hover:text-cyan-300">Soporte</a>
                        {auth?.user ? (
                            <a href={dashboardUrl} className="rounded-full bg-cyan-500 px-5 py-2.5 font-semibold transition hover:bg-cyan-400">
                                Dashboard
                            </a>
                        ) : (
                            <>
                                <Link href={loginUrl} className="transition hover:text-cyan-300">Iniciar sesión</Link>
                                <Link href={registerUrl} className="rounded-full bg-cyan-500 px-5 py-2.5 font-semibold transition hover:bg-cyan-400">
                                    Registrarse
                                </Link>
                            </>
                        )}
                    </div>
                </nav>
            </header>

            <main>
                <section
                    className="relative flex min-h-screen items-center overflow-hidden bg-cover bg-center px-4 pb-16 pt-28 text-white sm:px-8"
                    style={{ backgroundImage: "linear-gradient(135deg, rgba(15,23,42,.9), rgba(15,23,42,.55)), url('https://images.unsplash.com/photo-1576091160550-2173dba999ef?q=60&w=1280&auto=format&fit=crop')" }}
                >
                    <div className="mx-auto w-full max-w-6xl">
                        <div className="max-w-3xl text-center md:text-left">
                            <h1 className="text-5xl font-extrabold tracking-tight sm:text-6xl lg:text-7xl">
                                Monitorea tu salud, <span className="text-cyan-400">vive mejor</span>
                            </h1>
                            <p className="mt-6 max-w-2xl text-xl leading-8 text-slate-200 sm:text-2xl">
                                Control inteligente para una vida más saludable. Análisis con IA y monitoreo constante de tus signos vitales.
                            </p>
                            <div className="mt-10 flex flex-col justify-center gap-4 sm:flex-row md:justify-start">
                                <Link href={registerUrl} className="rounded-full bg-cyan-500 px-8 py-4 text-center font-bold shadow-lg shadow-cyan-950/30 transition hover:-translate-y-0.5 hover:bg-cyan-400">
                                    Comenzar ahora
                                </Link>
                                <a href="#features" className="rounded-full border-2 border-white px-8 py-4 text-center font-bold transition hover:bg-white hover:text-slate-900">
                                    Saber más
                                </a>
                            </div>
                        </div>
                    </div>
                    <a href="#features" className="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/80" aria-label="Ir a características">
                        <ChevronDown aria-hidden="true" size={32} />
                    </a>
                </section>

                <section id="features" className="px-4 py-20 sm:px-8">
                    <div className="mx-auto max-w-6xl">
                        <div className="mx-auto max-w-3xl text-center">
                            <h2 className="text-4xl font-extrabold tracking-tight sm:text-5xl">Gestión inteligente de la diabetes</h2>
                            <p className="mt-5 text-lg leading-8 text-slate-600">
                                DiabTrack mejora tu experiencia en la gestión de la salud, reduciendo riesgos mediante el monitoreo constante y análisis avanzados.
                            </p>
                        </div>

                        <div className="mt-12 grid gap-6 md:grid-cols-3">
                            {features.map((feature) => (
                                <article key={feature.title} className="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                                    <div className={`mx-auto flex h-16 w-16 items-center justify-center rounded-2xl ${feature.accent}`}>
                                        <FeatureIcon icon={feature.icon} />
                                    </div>
                                    <h3 className="mt-6 text-xl font-bold">{feature.title}</h3>
                                    <p className="mt-3 leading-7 text-slate-600">{feature.description}</p>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="bg-cyan-50 px-4 py-20 text-center sm:px-8">
                    <h2 className="text-4xl font-extrabold">¿Listo para tomar el control?</h2>
                    <p className="mt-5 text-lg text-slate-600">Únete a miles de personas que ya están mejorando su calidad de vida.</p>
                    <Link href={registerUrl} className="mt-8 inline-flex rounded-full bg-cyan-500 px-8 py-4 font-bold text-white transition hover:bg-cyan-600">
                        Crear mi cuenta gratuita
                    </Link>
                </section>
            </main>

            <footer id="footer" className="border-t border-slate-200 bg-white px-4 py-12 text-slate-600 sm:px-8">
                <div className="mx-auto grid max-w-6xl gap-8 text-center md:grid-cols-3 md:items-center md:text-left">
                    <div>
                        <BrandMark className="text-3xl" />
                        <p className="mt-2 text-sm text-slate-500">Tu compañero inteligente en el cuidado de la diabetes.</p>
                    </div>
                    <nav className="flex justify-center gap-4 text-sm" aria-label="Información legal">
                        <a href="#" className="hover:text-cyan-600">Privacidad</a>
                        <a href="#" className="hover:text-cyan-600">Términos</a>
                        <a href="#" className="hover:text-cyan-600">Soporte</a>
                    </nav>
                    <div className="flex flex-col items-center gap-3 md:items-end">
                        <SocialLinks networks={['Instagram', 'Facebook', 'Twitter']} linkClassName="text-slate-500 transition hover:text-cyan-600" />
                        <p className="text-sm text-slate-500">© {year} DiabTrack. Todos los derechos reservados.</p>
                    </div>
                </div>
            </footer>
        </div>
    );
}
