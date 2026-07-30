import BrandMark from '../Components/BrandMark';
import SocialLinks from '../Components/SocialLinks';

const footerLinks = [
    { label: 'Políticas de Privacidad', href: '#' },
    { label: 'Términos y Condiciones', href: '#' },
    { label: 'Desarrolladores', href: '#' },
];

export default function GuestLayout({
    children,
    slogan = 'Monitorea tu salud, vive mejor',
    description = 'Con DiabTrack lleva un control más inteligente para una vida más saludable',
}) {
    return <div className="flex min-h-screen flex-col bg-[linear-gradient(160deg,#f0f7ff_0%,#e8f4f8_50%,#f1f5f9_100%)] font-sans text-slate-900 antialiased">
        <main className="mx-auto flex min-h-screen w-full max-w-[1200px] flex-1 flex-col items-center justify-center gap-8 px-3 py-6 sm:px-6 sm:py-12 lg:flex-row lg:justify-around lg:gap-8 lg:px-8">
            <section className="flex max-w-[450px] flex-col items-center text-center lg:items-start lg:text-left" aria-labelledby="guest-brand-title">
                <h1 id="guest-brand-title"><BrandMark className="text-[35px] sm:text-5xl lg:text-[56px]" /></h1>
                <p className="mt-2 text-[15px] text-slate-500 sm:text-xl">{slogan}</p>
                <p className="mt-6 hidden text-[17px] leading-[1.7] text-slate-600 sm:block">{description}</p>
            </section>

            <section className="mb-8 w-full max-w-[460px] rounded-[20px] border border-white/60 bg-white/85 p-4 shadow-[0_8px_32px_rgba(31,38,135,0.05)] backdrop-blur-xl sm:rounded-3xl sm:p-10 lg:mb-12 lg:p-12">
                {children}
            </section>
        </main>

        <footer id="guest-footer" className="mt-auto border-t border-slate-200 bg-white px-6 py-6 text-xs text-slate-500">
            <div className="mx-auto flex max-w-[1200px] flex-col items-center justify-between gap-4 sm:flex-row">
                <nav className="flex flex-wrap justify-center gap-x-4 gap-y-2" aria-label="Información legal">
                    {footerLinks.map((link) => <a key={link.label} href={link.href} className="transition hover:text-cyan-600">{link.label}</a>)}
                </nav>
                <SocialLinks linkClassName="text-slate-500 transition hover:text-cyan-600" />
            </div>
        </footer>
    </div>;
}
