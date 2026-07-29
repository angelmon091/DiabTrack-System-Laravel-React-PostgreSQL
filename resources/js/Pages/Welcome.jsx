import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

import BrandMark from '../Components/BrandMark';

const features = [
    {
        title: 'Gestión de datos',
        description: 'Centraliza toda tu información de salud en un solo lugar seguro y accesible.',
        accent: 'bg-cyan-100 text-cyan-600',
        icon: 'chart',
    },
    {
        title: 'Análisis con IA',
        description: 'Recibe información valiosa procesada por inteligencia artificial sobre tus hábitos.',
        accent: 'bg-emerald-100 text-emerald-600',
        icon: 'processor',
    },
    {
        title: 'Consejos vitales',
        description: 'Recomendaciones personalizadas basadas en tu monitoreo diario y necesidades.',
        accent: 'bg-amber-100 text-amber-600',
        icon: 'heart',
    },
];

function FeatureIcon({ type }) {
    const paths = {
        chart: <path d="M5 19V9m7 10V5m7 14v-7M3 21h18" />,
        processor: <path d="M9 3v3m6-3v3M9 18v3m6-3v3M3 9h3m-3 6h3m12-6h3m-3 6h3M7 7h10v10H7z" />,
        heart: <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" />,
    };

    return (
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="h-8 w-8">
            {paths[type]}
        </svg>
    );
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
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="h-6 w-6">
                            <path d={menuOpen ? 'M6 6l12 12M18 6 6 18' : 'M4 7h16M4 12h16M4 17h16'} />
                        </svg>
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
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="h-8 w-8">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
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
                                        <FeatureIcon type={feature.icon} />
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

            <footer id="footer" className="bg-slate-950 px-4 py-12 text-slate-300 sm:px-8">
                <div className="mx-auto grid max-w-6xl gap-8 text-center md:grid-cols-3 md:items-center md:text-left">
                    <div>
                        <BrandMark className="text-3xl text-white" />
                        <p className="mt-2 text-sm text-slate-400">Tu compañero inteligente en el cuidado de la diabetes.</p>
                    </div>
                    <nav className="flex justify-center gap-4 text-sm" aria-label="Información legal">
                        <a href="#" className="hover:text-cyan-300">Privacidad</a>
                        <a href="#" className="hover:text-cyan-300">Términos</a>
                        <a href="#" className="hover:text-cyan-300">Soporte</a>
                    </nav>
                    <p className="text-sm text-slate-400 md:text-right">© {year} DiabTrack. Todos los derechos reservados.</p>
                </div>
            </footer>
        </div>
    );
}
