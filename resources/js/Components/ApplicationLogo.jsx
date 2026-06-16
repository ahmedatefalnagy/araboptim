export default function ApplicationLogo(props) {
    return (
        <img {...props} src="/logo.png?v=3" alt="Arab Optim Logo" className={props.className || "w-12 h-12 object-contain"} />
    );
}
