import { forwardRef, useEffect, useImperativeHandle, useRef } from 'react';

export default forwardRef(function TextInput(
    { type = 'text', className = '', isFocused = false, ...props },
    ref,
) {
    const localRef = useRef(null);

    useImperativeHandle(ref, () => ({
        focus: () => localRef.current?.focus(),
    }));

    useEffect(() => {
        if (isFocused) {
            localRef.current?.focus();
        }
    }, [isFocused]);

    return (
        <input
            {...props}
            type={type}
            className={
                'bg-[#0a0a0a] border-white/10 text-white rounded-xl shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors ' +
                className
            }
            ref={localRef}
        />
    );
});
