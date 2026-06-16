import { Link } from '@inertiajs/react';

export default function BackButton({ href, className = '' }) {
    return (
        <Link
            href={href || '#'}
            onClick={(e) => {
                if (!href) {
                    e.preventDefault();
                    window.history.back();
                }
            }}
            className={`inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 hover:text-white hover:bg-blue-600 hover:border-blue-600 transition-all active:scale-95 shadow-sm group ${className}`}
            title="رجوع"
        >
            <svg 
                className="w-5 h-5 transition-transform group-hover:-translate-x-0.5" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path 
                    strokeLinecap="round" 
                    strokeLinejoin="round" 
                    strokeWidth="2.5" 
                    d="M10 19l-7-7m0 0l7-7m-7 7h18" 
                />
            </svg>
        </Link>
    );
}
