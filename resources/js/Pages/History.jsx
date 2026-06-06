import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useEffect } from 'react';

export default function History({ jobs }) {
    useEffect(() => {
        const interval = setInterval(() => {
            router.reload({ only: ['jobs'], preserveScroll: true, preserveState: true });
        }, 5000);

        return () => clearInterval(interval);
    }, []);
    return (
        <AuthenticatedLayout header="History & Status">
            <Head title="History & Status" />

            <div className="bg-[#111] border border-white/5 rounded-3xl shadow-lg overflow-hidden">
                <div className="p-6 border-b border-white/5">
                    <h2 className="text-lg font-bold text-white">Riwayat Upload Terakhir</h2>
                </div>
                
                <div className="overflow-x-auto">
                    <table className="w-full text-sm text-left">
                        <thead className="text-xs text-slate-400 uppercase bg-[#0a0a0a]/50">
                            <tr>
                                <th scope="col" className="px-6 py-4 font-semibold">Video</th>
                                <th scope="col" className="px-6 py-4 font-semibold">Waktu</th>
                                <th scope="col" className="px-6 py-4 font-semibold">Platform & Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {jobs.data.length > 0 ? jobs.data.map((job) => (
                                <tr key={job.id} className="border-b border-white/5 hover:bg-white/[0.02] transition-colors">
                                    <td className="px-6 py-4">
                                        <div className="font-medium text-white mb-1">{job.title}</div>
                                        <div className="text-xs text-slate-500">{(job.file_size_bytes / (1024*1024)).toFixed(2)} MB</div>
                                    </td>
                                    <td className="px-6 py-4 text-slate-300">
                                        {new Date(job.created_at).toLocaleString('id-ID')}
                                    </td>
                                    <td className="px-6 py-4">
                                        <div className="flex flex-wrap gap-2">
                                            {job.platform_uploads.map(pu => {
                                                let colorClass = 'bg-slate-500/10 text-slate-400 border-slate-500/20'; // default
                                                if (pu.status === 'done' || pu.status === 'success') {
                                                    colorClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                                                } else if (pu.status === 'failed') {
                                                    colorClass = 'bg-red-500/10 text-red-400 border-red-500/20';
                                                } else if (pu.status === 'uploading') {
                                                    colorClass = 'bg-amber-500/10 text-amber-400 border-amber-500/20';
                                                } else if (pu.status === 'pending') {
                                                    colorClass = 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20';
                                                }
                                                return (
                                                    <div key={pu.id} className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border ${colorClass}`}>
                                                        <span className="capitalize mr-1.5 font-bold">{pu.platform}</span> 
                                                        &bull; {pu.status}
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </td>
                                </tr>
                            )) : (
                                <tr>
                                    <td colSpan="3" className="px-6 py-12 text-center text-slate-500">
                                        Belum ada riwayat upload. Mulai upload video pertama Anda!
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
