import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage, router } from '@inertiajs/react';

export default function Connections({ connections, credentials = {}, flash }) {
    const user = usePage().props.auth.user;
    const platforms = [
        { id: 'youtube', name: 'YouTube', color: 'bg-[#FF0000]', hover: 'hover:bg-[#cc0000]' },
        { id: 'facebook', name: 'Facebook', color: 'bg-[#1877F2]', hover: 'hover:bg-[#166fe5]' },
        { id: 'instagram', name: 'Instagram', color: 'bg-gradient-to-tr from-[#f09433] via-[#dc2743] to-[#bc1888]', hover: 'opacity-90' },
        { id: 'tiktok', name: 'TikTok', color: 'bg-[#000000] border border-white/20', hover: 'hover:bg-[#111111]' },
    ];

    return (
        <AuthenticatedLayout header="Platform Connections">
            <Head title="Platform Connections" />

            {flash?.success && (
                <div className="mb-6 bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-xl font-medium flex items-center gap-3">
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"/></svg>
                    {flash.success}
                </div>
            )}
            
            {flash?.error && (
                <div className="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl font-medium flex items-center gap-3">
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {flash.error}
                </div>
            )}

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {platforms.map(platform => {
                    const connectedAccounts = connections[platform.id] || [];

                    return (
                        <div key={platform.id} className="bg-[#111] border border-white/5 rounded-3xl p-6 shadow-lg flex flex-col gap-6 transition-all hover:border-white/10">
                            <div className="flex items-center gap-5">
                                <div className={`w-16 h-16 rounded-2xl flex items-center justify-center text-white shadow-lg shrink-0 ${platform.color}`}>
                                    <span className="font-bold text-2xl">{platform.name.charAt(0)}</span>
                                </div>
                                <div className="flex-1">
                                    <h3 className="text-xl font-bold text-white tracking-tight mb-1">{platform.name}</h3>
                                    {connectedAccounts.length === 0 ? (
                                        <p className="text-sm text-slate-400 font-medium">Belum ada akun yang terhubung</p>
                                    ) : (
                                        <p className="text-sm text-emerald-400 font-medium">{connectedAccounts.length} Akun terhubung</p>
                                    )}
                                </div>
                                <div className="shrink-0 flex items-center gap-2">
                                    {/* Credentials Settings Toggle - Show for all platforms */}
                                    <button 
                                        onClick={() => {
                                            const form = document.getElementById(`cred-form-${platform.id}`);
                                            form.classList.toggle('hidden');
                                        }}
                                        className="px-3 py-2.5 rounded-xl text-sm font-bold text-slate-300 bg-white/5 hover:bg-white/10 transition-all border border-white/10"
                                        title={`Pengaturan API ${platform.name}`}
                                    >
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                    
                                    <a 
                                        href={route('platform.redirect', platform.id)}
                                        className={`inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-white transition-all active:scale-95 shadow-md ${platform.color} ${platform.hover}`}
                                    >
                                        + Tambah Akun
                                    </a>
                                </div>
                            </div>

                            {/* Credentials Form */}
                            <form 
                                id={`cred-form-${platform.id}`} 
                                className="hidden mt-4 pt-4 border-t border-white/5 space-y-4"
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    const formData = new FormData(e.target);
                                    const platformToSave = platform.id === 'instagram' ? 'facebook' : platform.id;
                                    router.post(route('platform.credentials', platformToSave), Object.fromEntries(formData), {
                                        preserveScroll: true
                                    });
                                }}
                            >
                                <div className="bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 p-3 rounded-xl text-xs mb-3">
                                    Untuk keamanan dan melewati limitasi kuota, masukkan Client ID & Secret dari Aplikasi Developer {platform.name} Anda sendiri.
                                    {platform.id === 'instagram' && " (Gunakan kredensial yang sama dengan Meta/Facebook)"}
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-slate-400 mb-1">{platform.name} App / Client ID</label>
                                    <input 
                                        name="app_id"
                                        type="text" 
                                        defaultValue={credentials[platform.id === 'instagram' ? 'facebook' : platform.id]?.app_id || ''}
                                        required
                                        className="w-full bg-black/50 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"
                                        placeholder="Masukkan ID"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-slate-400 mb-1">{platform.name} App / Client Secret</label>
                                    <input 
                                        name="app_secret"
                                        type="password" 
                                        defaultValue={credentials[platform.id === 'instagram' ? 'facebook' : platform.id]?.app_secret || ''}
                                        required
                                        className="w-full bg-black/50 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"
                                        placeholder="Masukkan Secret"
                                    />
                                </div>
                                <button type="submit" className="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-lg transition-colors">
                                    Simpan Kredensial
                                </button>
                            </form>
                            
                            {connectedAccounts.length > 0 && (
                                <div className="space-y-3 mt-2 border-t border-white/5 pt-5">
                                    {connectedAccounts.map(acc => (
                                        <div key={acc.id} className="flex items-center justify-between bg-[#0a0a0a] p-4 rounded-2xl border border-white/5 hover:border-white/10 transition-colors">
                                            <div className="flex flex-col">
                                                <span className="inline-flex items-center gap-1.5 text-sm text-slate-200 font-bold">
                                                    <svg className="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M5 13l4 4L19 7"/></svg>
                                                    {acc.platform_username || 'Akun ' + platform.name}
                                                </span>
                                                <span className="text-xs text-slate-500 font-medium mt-1">ID: {acc.platform_user_id}</span>
                                            </div>
                                            <Link 
                                                href={route('platform.disconnect', acc.id)} 
                                                method="delete"
                                                as="button"
                                                className="px-4 py-2 rounded-xl text-xs font-bold text-red-400 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 transition-all active:scale-95"
                                            >
                                                Putuskan
                                            </Link>
                                        </div>
                                    ))}
                                </div>
                            )}

                            {user?.is_super_admin && (
                                <div className="w-full mt-2 pt-4 border-t border-white/5 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                                    <span className="text-slate-500 font-medium">Developer Redirect URI (OAuth Callback):</span>
                                    <code className="bg-black/50 text-indigo-400 px-3 py-1.5 rounded-lg border border-indigo-500/20 select-all font-mono shadow-inner">
                                        {window.location.origin}/auth/{platform.id}/callback
                                    </code>
                                </div>
                            )}
                        </div>
                    );
                })}
            </div>
            
            <div className="mt-8 bg-[#0a0a0a] border border-indigo-500/20 rounded-2xl p-6 text-sm text-indigo-200/70 leading-relaxed">
                <strong className="text-indigo-400">Penting:</strong> Untuk melakukan koneksi, pastikan Anda telah mendaftarkan aplikasi VidFlow di masing-masing portal developer platform (Google Cloud Console, Facebook Developers, TikTok Developers) dan memasukkan Client ID & Secret ke dalam file konfigurasi server Anda.
            </div>
        </AuthenticatedLayout>
    );
}
