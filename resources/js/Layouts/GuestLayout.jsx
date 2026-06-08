import { Link } from '@inertiajs/react';

export default function GuestLayout({ children, title, subtitle }) {
    return (
        <div className="flex min-h-screen items-center justify-center bg-[#0a0a0a] text-slate-200 relative overflow-hidden">
            {/* Background elements */}
            <div className="absolute top-[-10%] left-[-10%] h-[500px] w-[500px] rounded-full bg-indigo-600/20 blur-[120px]" />
            <div className="absolute bottom-[-10%] right-[-10%] h-[500px] w-[500px] rounded-full bg-violet-600/20 blur-[120px]" />
            
            <div className="relative z-10 w-full max-w-md px-6 py-12">
                <div className="mb-8 text-center">
                    <Link href="/" className="inline-flex items-center gap-2 mb-6">
                        <img src="/vidflow-icon.png" alt="VidFlow Logo" className="h-10 w-10 object-contain drop-shadow-lg rounded-xl" />
                        <span className="text-2xl font-bold tracking-tight text-white">VidFlow</span>
                    </Link>
                    
                    {title && <h2 className="text-3xl font-bold tracking-tight text-white">{title}</h2>}
                    {subtitle && <p className="mt-2 text-sm text-slate-400">{subtitle}</p>}
                </div>

                <div className="overflow-hidden rounded-2xl border border-white/10 bg-white/5 px-8 py-8 shadow-2xl backdrop-blur-xl">
                    {children}
                </div>
            </div>
        </div>
    );
}
