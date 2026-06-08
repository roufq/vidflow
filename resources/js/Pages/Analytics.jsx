import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage, useForm } from '@inertiajs/react';

export default function Analytics({ totalUploads, activeConnections, totalViews, totalLikes, recentUploads }) {
    const user = usePage().props.auth.user;
    const { post, processing } = useForm();

    const handleSync = () => {
        post(route('analytics.sync'), {
            preserveScroll: true
        });
    };

    return (
        <AuthenticatedLayout header="Analytics Overview">
            <Head title="Analytics" />

            <div className="space-y-6">
                {/* Header Actions */}
                <div className="flex items-center justify-between bg-[#111] border border-white/5 p-4 rounded-2xl shadow-lg">
                    <div>
                        <h2 className="text-lg font-bold text-white">Data Sinkronisasi API</h2>
                        <p className="text-sm text-slate-400">Tarik data views dan likes terbaru langsung dari server YouTube, TikTok, dan Meta.</p>
                    </div>
                    <button 
                        onClick={handleSync}
                        disabled={processing}
                        className="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 rounded-xl font-semibold transition-all disabled:opacity-50"
                    >
                        <svg className={`w-5 h-5 ${processing ? 'animate-spin' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        {processing ? 'Menyinkronkan...' : 'Sinkronkan Analitik'}
                    </button>
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {/* Total Views */}
                    <div className="bg-[#111] border border-white/5 rounded-3xl p-6 shadow-lg">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-sm font-semibold text-slate-400">Total Views</h3>
                            <div className="p-2 bg-pink-500/10 text-pink-400 rounded-lg">
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </div>
                        </div>
                        <p className="text-3xl font-bold text-white mb-2">{totalViews.toLocaleString()}</p>
                        <p className="text-xs font-medium text-slate-500 flex items-center gap-1">
                            Diperbarui saat sinkronisasi API
                        </p>
                    </div>

                    {/* Total Likes */}
                    <div className="bg-[#111] border border-white/5 rounded-3xl p-6 shadow-lg">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-sm font-semibold text-slate-400">Total Likes</h3>
                            <div className="p-2 bg-red-500/10 text-red-400 rounded-lg">
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                            </div>
                        </div>
                        <p className="text-3xl font-bold text-white mb-2">{totalLikes.toLocaleString()}</p>
                        <p className="text-xs font-medium text-slate-500 flex items-center gap-1">
                            Diperbarui saat sinkronisasi API
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

                {/* Details Table */}
                <div className="bg-[#111] border border-white/5 rounded-3xl p-7 shadow-lg overflow-hidden">
                    <h3 className="text-lg font-bold text-white mb-6">Detail Performa Video</h3>
                    {recentUploads && recentUploads.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse">
                                <thead>
                                    <tr className="border-b border-white/10 text-sm font-semibold text-slate-400">
                                        <th className="pb-3 pr-4 font-medium">Platform</th>
                                        <th className="pb-3 pr-4 font-medium">Judul Video</th>
                                        <th className="pb-3 pr-4 font-medium">Views</th>
                                        <th className="pb-3 pr-4 font-medium">Likes</th>
                                        <th className="pb-3 font-medium">Terakhir Sinkron</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {recentUploads.map((upload) => (
                                        <tr key={upload.id} className="border-b border-white/5 hover:bg-white/5 transition-colors">
                                            <td className="py-4 pr-4">
                                                <span className={`inline-block px-3 py-1 text-xs font-semibold rounded-lg capitalize ${
                                                    upload.platform === 'youtube' ? 'bg-red-500/10 text-red-400' :
                                                    upload.platform === 'tiktok' ? 'bg-slate-800 text-white' :
                                                    upload.platform === 'instagram' ? 'bg-pink-500/10 text-pink-400' :
                                                    'bg-blue-500/10 text-blue-400'
                                                }`}>
                                                    {upload.platform}
                                                </span>
                                            </td>
                                            <td className="py-4 pr-4 text-sm font-medium text-slate-200 truncate max-w-[200px]" title={upload.upload_job?.video_title}>
                                                {upload.upload_job?.video_title || 'Video Upload'}
                                            </td>
                                            <td className="py-4 pr-4 text-sm font-bold text-white">
                                                {upload.views.toLocaleString()}
                                            </td>
                                            <td className="py-4 pr-4 text-sm font-bold text-white">
                                                {upload.likes.toLocaleString()}
                                            </td>
                                            <td className="py-4 text-xs text-slate-400">
                                                {upload.last_synced_at ? new Date(upload.last_synced_at).toLocaleString('id-ID') : 'Belum pernah'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="text-center py-10">
                            <svg className="w-16 h-16 mx-auto mb-4 text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <p className="text-slate-400">Belum ada data upload video yang sukses di platform.</p>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
