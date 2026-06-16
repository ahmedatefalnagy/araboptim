import { forwardRef, useEffect, useRef } from 'react';

export default forwardRef(function TextInput({ type = 'text', className = '', isFocused = false, ...props }, ref) {
    const input = ref ? ref : useRef();

    useEffect(() => {
        if (isFocused) {
            input.current.focus();
        }
    }, []);

    return (
        <input
            {...props}
            type={type}
            className={
                'border-gray-200 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm transition-all duration-200 placeholder:text-gray-400 p-2.5 ' +
                className
            }
            ref={input}
        />
    );
});
