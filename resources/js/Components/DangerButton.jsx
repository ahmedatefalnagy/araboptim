export default function DangerButton({ className = '', disabled, children, ...props }) {
    return (
        <button
            {...props}
            disabled={disabled}
            className={
                `btn-danger ${className} ${disabled ? 'opacity-25' : ''}`
            }
        >
            {children}
        </button>
    );
}
