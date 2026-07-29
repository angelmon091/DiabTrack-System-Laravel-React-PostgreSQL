export default function BrandMark({ className = '' }) {
    return (
        <span
            className={`inline-flex items-baseline text-4xl font-extrabold tracking-[-0.04em] text-slate-900 ${className}`}
            aria-label="DiabTrack"
        >
            D<span className="mx-px text-cyan-500">ia</span>bTrack
        </span>
    );
}
