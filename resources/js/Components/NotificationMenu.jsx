import { router } from '@inertiajs/react';
import { Bell, BellOff, CheckCheck, Sparkles, Trash2, X } from 'lucide-react';
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
        return () => {
            document.removeEventListener('keydown', closeOnEscape);
            document.removeEventListener('mousedown', closeOutside);
        };
    }, [open]);

    const mutate = (method, url) => router.visit(url, { method, only: ['notifications'], preserveScroll: true });

    return <div ref={menuRef} className="relative">
        <button type="button" onClick={() => setOpen((value) => !value)} aria-expanded={open} aria-haspopup="menu" aria-label="Notificaciones" className="relative flex h-10 w-10 items-center justify-center rounded-full text-slate-600 transition hover:bg-slate-100 hover:text-cyan-600">
            <Bell size={20} fill="currentColor" strokeWidth={1.6} />
            {unread > 0 && <span className="absolute right-0.5 top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-bold text-white">{unread > 9 ? '9+' : unread}</span>}
        </button>

        {open && <div role="menu" className="fixed left-3 right-3 top-[66px] z-50 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl sm:absolute sm:left-auto sm:right-0 sm:top-[calc(100%+10px)] sm:w-[340px]">
            <div className="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <strong className="text-sm text-slate-900">Notificaciones</strong>
                {notifications.length > 0 && <div className="flex items-center gap-3">
                    {unread > 0 && <button type="button" className="text-slate-400 transition hover:text-cyan-600" title="Marcar todas como leídas" onClick={() => mutate('post', navigation.notificationsReadAllUrl)}><CheckCheck size={18} /></button>}
                    <button type="button" className="text-slate-400 transition hover:text-red-500" title="Borrar todas" onClick={() => mutate('delete', navigation.notificationsDestroyAllUrl)}><Trash2 size={17} /></button>
                </div>}
            </div>
            <div className="max-h-[360px] overflow-y-auto">
                {notifications.length ? notifications.map((notification) => <div key={notification.id} className={`group flex gap-3 border-b border-slate-100 px-4 py-3 transition hover:bg-slate-50 ${notification.read ? '' : 'bg-cyan-50/50'}`}>
                    <button type="button" className="flex min-w-0 flex-1 gap-3 text-left" onClick={() => mutate('post', notification.readUrl)}>
                        <span className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-full ${notification.type === 'ai_reminder' ? 'bg-cyan-100 text-cyan-600' : 'bg-indigo-100 text-indigo-600'}`}>{notification.type === 'ai_reminder' ? <Sparkles size={16} /> : <Bell size={16} />}</span>
                        <span className="min-w-0 flex-1"><span className="flex items-center gap-2"><strong className="truncate text-xs text-slate-800">{notification.title}</strong>{!notification.read && <span className="ml-auto h-2 w-2 shrink-0 rounded-full bg-cyan-500" />}</span><span className="mt-1 block text-xs leading-5 text-slate-500">{notification.body}</span><span className="mt-1 block text-[11px] text-slate-400">{notification.createdAt}</span></span>
                    </button>
                    <button type="button" className="self-start text-slate-300 opacity-60 transition hover:text-red-500 sm:opacity-0 sm:group-hover:opacity-100" title="Eliminar" onClick={() => mutate('delete', notification.destroyUrl)}><X size={16} /></button>
                </div>) : <div className="flex flex-col items-center py-10 text-slate-400"><BellOff size={25} /><p className="mt-2 text-xs">Sin notificaciones</p></div>}
            </div>
        </div>}
    </div>;
}
