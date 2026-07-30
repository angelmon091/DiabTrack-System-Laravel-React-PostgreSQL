import { FaFacebook, FaInstagram, FaReddit, FaTwitter } from 'react-icons/fa';

const iconByNetwork = {
    Instagram: FaInstagram,
    Facebook: FaFacebook,
    Reddit: FaReddit,
    Twitter: FaTwitter,
};

export default function SocialLinks({ networks = ['Instagram', 'Facebook', 'Reddit'], className = '', linkClassName = '' }) {
    return (
        <nav className={`flex flex-wrap items-center justify-center gap-x-4 gap-y-2 ${className}`} aria-label="Redes sociales">
            {networks.map((network) => {
                const Icon = iconByNetwork[network];

                return (
                    <a key={network} href="#" aria-label={network} title={network} className={linkClassName}>
                        <Icon aria-hidden="true" className="h-[19px] w-[19px]" />
                    </a>
                );
            })}
        </nav>
    );
}
