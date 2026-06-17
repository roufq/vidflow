import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Connections({ connections, credentials = {}, hasGlobalCredentials = {}, flash }) {
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
                {platforms.map(platform => (
                    <PlatformCard 
                        key={platform.id} 
                        platform={platform} 
                        connectedAccounts={connections[platform.id] || []} 
                        credential={credentials[platform.id === 'instagram' ? 'facebook' : platform.id]}
                        hasGlobal={hasGlobalCredentials[platform.id]}
                        user={user} 
                    />
                ))}
            </div>
            
            <div className="mt-8 bg-[#0a0a0a] border border-indigo-500/20 rounded-2xl p-6 text-sm text-indigo-200/70 leading-relaxed">
                <strong className="text-indigo-400">Penting:</strong> Untuk melakukan koneksi, pastikan Anda telah mendaftarkan aplikasi VidFlow di masing-masing portal developer platform (Google Cloud Console, Facebook Developers, TikTok Developers) dan memasukkan Client ID & Secret ke dalam file konfigurasi server Anda.
            </div>
        </AuthenticatedLayout>
    );
}

function PlatformCard({ platform, connectedAccounts, credential, hasGlobal, user }) {
    const { data, setData, post, processing, errors } = useForm({
        app_id: credential?.app_id || '',
        app_secret: credential?.app_secret || ''
    });

    const [showSettings, setShowSettings] = useState(false);

    const submit = (e) => {
        e.preventDefault();
        const platformToSave = platform.id === 'instagram' ? 'facebook' : platform.id;
        post(route('platform.credentials', platformToSave), {
            preserveScroll: true,
            onSuccess: () => setShowSettings(false)
        });
    };

    return (
        <div className="bg-[#111] border border-white/5 rounded-3xl p-6 shadow-lg flex flex-col gap-6 transition-all hover:border-white/10">
            <div className="flex items-center gap-5">
                <div className={`w-16 h-16 rounded-2xl flex items-center justify-center text-white shadow-lg shrink-0 ${platform.color}`}>
                    <span className="font-bold text-2xl">{platform.name.charAt(0)}</span>
                </div>
                <div className="flex-1">
                    <div className="flex items-center gap-2 mb-1">
                        <h3 className="text-xl font-bold text-white tracking-tight">{platform.name}</h3>
                        {hasGlobal && (
                            <span className="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20" title="Kredensial aplikasi global siap digunakan">
                                Sistem Ready
                            </span>
                        )}
                    </div>
                    {connectedAccounts.length === 0 ? (
                        <p className="text-sm text-slate-400 font-medium">Belum ada akun yang terhubung</p>
                    ) : (
                        <p className="text-sm text-emerald-400 font-medium">{connectedAccounts.length} Akun terhubung</p>
                    )}
                </div>
                <div className="shrink-0 flex items-center gap-2">
                    <button 
                        type="button"
                        onClick={() => setShowSettings(!showSettings)}
                        className={`px-3 py-2.5 rounded-xl text-sm font-bold transition-all border ${showSettings ? 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30' : 'text-slate-300 bg-white/5 hover:bg-white/10 border-white/10'}`}
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

            {showSettings && (
                <form onSubmit={submit} className="mt-4 pt-4 border-t border-white/5 space-y-4">
                    {hasGlobal ? (
                        <div className="bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 p-3 rounded-xl text-xs mb-3">
                            Aplikasi sudah terkonfigurasi secara global oleh admin. Anda bisa langsung menghubungkan akun tanpa mengisi form ini. Isi form ini hanya jika Anda ingin menimpa (override) dengan App ID Anda sendiri.
                        </div>
                    ) : (
                        <div className="bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 p-3 rounded-xl text-xs mb-3 space-y-1">
                            <div>
                                <strong>Pemberitahuan Penting & Privasi:</strong> Menginput App ID & App Secret kustom milik Anda sendiri <strong>diharuskan</strong> demi menjaga keamanan, perlindungan privasi data Anda, serta menghindari limitasi kuota API bersama.
                            </div>
                            {platform.id === 'instagram' && (
                                <div className="text-slate-400">
                                    *(Gunakan kredensial App ID & Secret yang sama dengan Meta/Facebook Developer Anda)
                                </div>
                            )}
                        </div>
                    )}
                    <div>
                        <label className="block text-xs font-bold text-slate-400 mb-1">{platform.name} App / Client ID</label>
                        <input 
                            name="app_id"
                            type="text" 
                            value={data.app_id}
                            onChange={e => setData('app_id', e.target.value)}
                            required={true}
                            className="w-full bg-black/50 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"
                            placeholder="Masukkan ID"
                        />
                        {errors.app_id && <div className="text-red-500 text-xs mt-1">{errors.app_id}</div>}
                    </div>
                    <div>
                        <label className="block text-xs font-bold text-slate-400 mb-1">{platform.name} App / Client Secret</label>
                        <input 
                            name="app_secret"
                            type="password" 
                            value={data.app_secret}
                            onChange={e => setData('app_secret', e.target.value)}
                            required={true}
                            className="w-full bg-black/50 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"
                            placeholder="Masukkan Secret"
                        />
                        {errors.app_secret && <div className="text-red-500 text-xs mt-1">{errors.app_secret}</div>}
                    </div>
                    <div className="mt-4 pt-3 border-t border-white/5">
                        <label className="block text-xs font-bold text-slate-400 mb-2">OAuth Redirect / Callback URI:</label>
                        <div className="flex items-center gap-2">
                            <code className="flex-1 bg-black/60 text-indigo-400 px-3 py-2 rounded-lg border border-indigo-500/20 select-all font-mono text-xs shadow-inner">
                                {platform.id === 'tiktok' ? `${window.location.origin}/auth/tt/callback` : `${window.location.origin}/auth/${platform.id}/callback`}
                            </code>
                        </div>
                        <p className="text-[10px] text-slate-500 mt-1.5 leading-tight">
                            Salin URL di atas dan tempel pada bagian "Redirect URI" di pengaturan aplikasi {platform.name} Developer Anda.
                        </p>
                    </div>
                    <button 
                        type="submit" 
                        disabled={processing}
                        className={`w-full py-2 text-white text-sm font-bold rounded-lg transition-colors mt-2 ${processing ? 'bg-indigo-600/50 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-500'}`}
                    >
                        {processing ? 'Menyimpan...' : 'Simpan Kredensial'}
                    </button>
                </form>
            )}
            
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
        </div>
    );
}
