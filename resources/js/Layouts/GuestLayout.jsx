import BrandMark from '../Components/BrandMark';

const footerLinks = [
    { label: 'Políticas de Privacidad', href: '#' },
    { label: 'Términos y Condiciones', href: '#' },
    { label: 'Desarrolladores', href: '#' },
];

const socialLinks = [
    { label: 'Instagram', href: '#' },
    { label: 'Facebook', href: '#' },
    { label: 'Reddit', href: '#' },
];

export default function GuestLayout({
    children,
    slogan = 'Monitorea tu salud, vive mejor',
    description = 'Con DiabTrack lleva un control más inteligente para una vida más saludable',
}) {
    return (
        <div className="flex min-h-screen flex-col bg-gradient-to-br from-blue-50 via-cyan-50 to-slate-100 font-sans text-slate-900 antialiased">
            <main className="mx-auto flex w-full max-w-6xl flex-1 flex-col items-center justify-center gap-10 px-4 py-10 md:px-8 lg:flex-row lg:justify-around lg:gap-16">
                <section className="max-w-md text-center lg:text-left" aria-labelledby="guest-brand-title">
                    <h1 id="guest-brand-title">
                        <BrandMark className="text-5xl sm:text-6xl" />
                    </h1>
                    <p className="mt-2 text-lg text-slate-500 sm:text-xl">{slogan}</p>
                    <p className="mt-6 hidden text-base leading-7 text-slate-600 sm:block">
                        {description}
                    </p>
                </section>

                <section className="mb-8 w-full max-w-[460px] rounded-3xl border border-white/60 bg-white/85 p-6 shadow-[0_8px_32px_rgba(31,38,135,0.05)] backdrop-blur-xl sm:p-10 lg:p-12">
                    {children}
                </section>
            </main>

            <footer className="border-t border-slate-200 bg-white px-6 py-6 text-sm text-slate-500">
                <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 md:flex-row">
                    <nav className="flex flex-wrap justify-center gap-x-5 gap-y-2" aria-label="Información legal">
                        {footerLinks.map((link) => (
                            <a key={link.label} href={link.href} className="transition hover:text-cyan-600">
                                {link.label}
                            </a>
                        ))}
                    </nav>

                    <nav className="flex gap-4" aria-label="Redes sociales">
                        {socialLinks.map((link) => (
                            <a key={link.label} href={link.href} className="transition hover:text-cyan-600">
                                {link.label}
                            </a>
                        ))}
                    </nav>
                </div>
            </footer>
        </div>
    );
}
