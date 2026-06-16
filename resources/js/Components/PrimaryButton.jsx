export default function PrimaryButton({ className = '', disabled, children, ...props }) {
    return (
        <button
            {...props}
            disabled={disabled}
            className={
                `btn-primary ${className} ${disabled ? 'opacity-25' : ''}`
            }
        >
            {children}
        </button>
    );
}
