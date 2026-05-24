interface Props {
    src?: string | null;
    className?: string;
}

export default function ApplicationLogo({ src, className }: Props) {
    if (src) {
        return (
            <img
                src={src}
                alt="Logo"
                className={className ?? 'h-10 w-10 object-contain'}
            />
        );
    }

    return (
        <div
            className={`flex items-center justify-center rounded-lg bg-gold-500 text-navy-900 font-bold ${className ?? 'h-10 w-10'}`}
        >
            <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
    );
}
