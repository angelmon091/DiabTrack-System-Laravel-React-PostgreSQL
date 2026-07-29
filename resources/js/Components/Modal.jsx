import { useEffect } from 'react';

export default function Modal({ open, title, children, actions, onClose }) {
    useEffect(() => {
        if (!open) return undefined;

        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        function handleKeyDown(event) {
            if (event.key === 'Escape') onClose();
        }

        document.addEventListener('keydown', handleKeyDown);

        return () => {
            document.removeEventListener('keydown', handleKeyDown);
            document.body.style.overflow = previousOverflow;
        };
    }, [open, onClose]);

    if (!open) return null;
    return <div data-testid="modal-backdrop" className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" role="presentation" onMouseDown={(event) => { if (event.target === event.currentTarget) onClose(); }}>
        <section data-testid="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modal-title" className="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
            <div className="mb-5 flex items-center justify-between gap-4"><h2 id="modal-title" className="text-xl font-bold text-slate-900">{title}</h2><button type="button" onClick={onClose} aria-label="Cerrar" className="rounded-full p-2 text-slate-500 hover:bg-slate-100">Cerrar</button></div>
            {children}
            {actions && <div className="mt-6 flex flex-wrap justify-end gap-3">{actions}</div>}
        </section>
    </div>;
}
