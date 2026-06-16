export default function InputLabel({ value, className = '', children, ...props }) {
    return (
        <label {...props} className={`block font-bold text-sm text-slate-700 mb-1.5 ` + className}>
            {value ? value : children}
        </label>
    );
}
