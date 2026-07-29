export default function AuthSessionStatus({ status, className = '' }) {
    if (!status) {
        return null;
    }

    return (
        <div
            className={`mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-2.5 text-sm leading-relaxed text-emerald-700 ${className}`}
            role="status"
        >
            {status}
        </div>
    );
}
