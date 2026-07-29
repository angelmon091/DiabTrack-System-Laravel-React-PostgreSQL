import { useForm, usePage } from '@inertiajs/react';

import Alert from '../Components/Alert';
import BrandMark from '../Components/BrandMark';
import GlobalSearch from '../Components/GlobalSearch';
import NotificationMenu from '../Components/NotificationMenu';

export default function AuthenticatedLayout({ children }) {
    const { auth, flash, navigation, notifications = [] } = usePage().props;
    const logoutForm = useForm({});
    return <div className="flex min-h-screen flex-col bg-slate-50 text-slate-800">
        <header className="sticky top-0 z-20 border-b border-slate-200/80 bg-white/95 shadow-sm backdrop-blur">
            <div className="mx-auto flex max-w-7xl items-center gap-5 px-4 py-3 sm:px-6 lg:px-8">
                <a href={navigation.dashboardUrl} aria-label="Ir al dashboard"><BrandMark className="text-xl" /></a>
                {auth.permissions.puedeBuscar && navigation.searchUrl && <GlobalSearch searchUrl={navigation.searchUrl} />}
                <div className="ml-auto flex items-center gap-2">
                    <NotificationMenu notifications={notifications} navigation={navigation} />
                    <a href={navigation.profileUrl} className="hidden text-right sm:block"><strong className="block text-sm">{auth.user.name}</strong><span className="block text-xs text-slate-500">{auth.user.email}</span></a>
                    <form onSubmit={(event) => { event.preventDefault(); logoutForm.post(navigation.logoutUrl); }}><button type="submit" disabled={logoutForm.processing} className="rounded-xl px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">Salir</button></form>
                </div>
            </div>
        </header>
        <main className="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 lg:px-8"><div className="mb-5 space-y-3"><Alert>{flash?.success || flash?.status}</Alert><Alert tone="error">{flash?.error}</Alert></div>{children}</main>
        <footer className="border-t bg-white px-4 py-6 text-center text-sm text-slate-500">© {new Date().getFullYear()} DiabTrack App. Cuidando tu salud.</footer>
    </div>;
}
