import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

export default function NotificationMenu({ notifications, navigation }) {
    const [open, setOpen] = useState(false);
    const menuRef = useRef(null);
    const unread = notifications.filter((notification) => !notification.read).length;
    useEffect(() => {
        if (!open) return undefined;
        const closeOnEscape = (event) => { if (event.key === 'Escape') setOpen(false); };
        const closeOutside = (event) => { if (!menuRef.current?.contains(event.target)) setOpen(false); };
        document.addEventListener('keydown', closeOnEscape);
        document.addEventListener('mousedown', closeOutside);
        return () => { document.removeEventListener('keydown', closeOnEscape); document.removeEventListener('mousedown', closeOutside); };
    }, [open]);

    const mutate = (method, url) => router.visit(url, {
        method,
        only: ['notifications'],
        preserveScroll: true,
    });

    return <div ref={menuRef} className="relative">
        <button type="button" onClick={() => setOpen((value) => !value)} aria-expanded={open} aria-haspopup="menu" aria-label="Notificaciones" className="relative rounded-full p-2 text-slate-500 hover:bg-slate-100">
            <span aria-hidden="true">&#9679;</span>{unread > 0 && <span className="absolute -right-1 -top-1 rounded-full bg-red-600 px-1.5 text-[10px] text-white">{unread > 9 ? '9+' : unread}</span>}
        </button>
        {open && <div role="menu" className="absolute right-0 z-40 mt-2 w-80 max-w-[90vw] overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-xl">
            <div className="flex items-center justify-between border-b px-4 py-3"><strong className="text-sm">Notificaciones</strong><div className="flex gap-3 text-xs">{unread > 0 && <button type="button" onClick={() => mutate('post', navigation.notificationsReadAllUrl)}>Leer todas</button>}{notifications.length > 0 && <button type="button" className="text-red-600" onClick={() => mutate('delete', navigation.notificationsDestroyAllUrl)}>Borrar</button>}</div></div>
            <div className="max-h-80 overflow-auto">{notifications.length ? notifications.map((notification) => <div key={notification.id} className={`border-b px-4 py-3 ${notification.read ? '' : 'bg-cyan-50/60'}`}><button type="button" onClick={() => mutate('post', notification.readUrl)} className="w-full text-left"><strong className="block text-sm text-slate-800">{notification.title}</strong><span className="block text-xs text-slate-500">{notification.body}</span><span className="text-[11px] text-slate-400">{notification.createdAt}</span></button><button type="button" onClick={() => mutate('delete', notification.destroyUrl)} className="mt-1 text-xs text-red-600">Eliminar</button></div>) : <p className="p-6 text-center text-sm text-slate-500">Sin notificaciones</p>}</div>
        </div>}
    </div>;
}
