export default function SubmitButton({
    processing = false,
    processingLabel = 'Procesando...',
    children,
    className = '',
    ...buttonProps
}) {
    return (
        <button
            {...buttonProps}
            type="submit"
            disabled={processing || buttonProps.disabled}
            aria-busy={processing}
            className={`inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-cyan-600 px-4 py-3.5 text-base font-semibold tracking-wide text-white shadow-lg shadow-cyan-500/20 transition hover:-translate-y-0.5 hover:brightness-105 hover:shadow-xl hover:shadow-cyan-500/30 focus:outline-none focus:ring-4 focus:ring-cyan-500/20 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0 ${className}`}
        >
            {processing ? processingLabel : children}
        </button>
    );
}
