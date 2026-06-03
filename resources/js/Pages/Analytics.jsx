import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';

export default function Analytics({ totalUploads, activeConnections }) {
    const user = usePage().props.auth.user;

    return (
        <AuthenticatedLayout header="Analytics Overview">
            <Head title="Analytics" />

            <div className="space-y-6">
                {/* Stats Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {/* Total Views */}
                    <div className="bg-[#111] border border-white/5 rounded-3xl p-6 shadow-lg">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-sm font-semibold text-slate-400">Total Video Diupload</h3>
                            <div className="p-2 bg-indigo-500/10 text-indigo-400 rounded-lg">
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                        <p className="text-3xl font-bold text-white mb-2">{totalUploads}</p>
                        <p className="text-xs font-medium text-slate-500 flex items-center gap-1">
                            Berdasarkan histori sistem
                        </p>
                    </div>

                    {/* Engagement Rate */}
                    <div className="bg-[#111] border border-white/5 rounded-3xl p-6 shadow-lg">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-sm font-semibold text-slate-400">Total Views (Semua Sosmed)</h3>
                            <div className="p-2 bg-pink-500/10 text-pink-400 rounded-lg">
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </div>
                        </div>
                        <p className="text-3xl font-bold text-white mb-2">0</p>
                        <p className="text-xs font-medium text-slate-500 flex items-center gap-1">
                            Menunggu sinkronisasi API platform
                        </p>
                    </div>

                    {/* Upload Quota */}
                    <div className="bg-[#111] border border-white/5 rounded-3xl p-6 shadow-lg">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-sm font-semibold text-slate-400">Kuota Upload</h3>
                            <div className="p-2 bg-blue-500/10 text-blue-400 rounded-lg">
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                            </div>
                        </div>
                        <p className="text-3xl font-bold text-white mb-2">{user.plan === 'business' ? '∞' : '12/50'}</p>
                        <p className="text-xs font-medium text-slate-500 flex items-center gap-1">
                            Tersisa {user.plan === 'business' ? 'unlimited' : '38 upload'} bulan ini
                        </p>
                    </div>

                    {/* Active Platforms */}
                    <div className="bg-[#111] border border-white/5 rounded-3xl p-6 shadow-lg">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-sm font-semibold text-slate-400">Active Platforms</h3>
                            <div className="p-2 bg-emerald-500/10 text-emerald-400 rounded-lg">
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                            </div>
                        </div>
                        <p className="text-3xl font-bold text-white mb-2">{activeConnections}</p>
                        <p className="text-xs font-medium text-slate-500 flex items-center gap-1">
                            Sosmed yang saat ini terhubung
                        </p>
                    </div>
                </div>

                {/* Main Chart Mock */}
                <div className="bg-[#111] border border-white/5 rounded-3xl p-7 shadow-lg min-h-[400px] flex items-center justify-center">
                    <div className="text-center">
                        <svg className="w-16 h-16 mx-auto mb-4 text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <h3 className="text-lg font-bold text-white mb-2">Data Analitik Terpusat (Coming Soon)</h3>
                        <p className="text-slate-400 max-w-md mx-auto">
                            Fitur grafik performa dan komparasi views antar platform saat ini sedang dalam pengembangan untuk rilis fase berikutnya.
                        </p>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
