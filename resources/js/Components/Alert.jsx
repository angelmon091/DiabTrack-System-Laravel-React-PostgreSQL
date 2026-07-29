export default function Alert({ children, tone = 'success' }) {
    if (!children) return null;
    const styles = tone === 'error' ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700';
    return <div role="alert" className={`rounded-2xl border px-4 py-3 text-sm font-medium ${styles}`}>{children}</div>;
}
