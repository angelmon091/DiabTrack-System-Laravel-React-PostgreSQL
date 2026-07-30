export default function OnboardingHeader({ icon: Icon, iconClassName = 'bg-cyan-100 text-cyan-600', title, description }) {
    return (
        <div className="mb-8 text-center">
            <span className={`mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl ${iconClassName}`}>
                <Icon aria-hidden="true" className="h-7 w-7" strokeWidth={1.8} />
            </span>
            <h2 className="text-2xl font-extrabold text-cyan-600">{title}</h2>
            <p className="mt-2 text-sm text-slate-500">{description}</p>
        </div>
    );
}
