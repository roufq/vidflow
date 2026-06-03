import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { useState, useRef } from 'react';
import InputError from '@/Components/InputError';

export default function Dashboard({ flash, connections = {} }) {
    const fileInputRef = useRef(null);
    const [isScheduled, setIsScheduled] = useState(false);
    
    const { data, setData, post, processing, errors, progress } = useForm({
        title: '',
        description: '',
        tags: '',
        video: null,
        selected_connections: [],
        scheduled_at: '',
    });

    const handleFileChange = (e) => {
        if (e.target.files && e.target.files[0]) {
            setData('video', e.target.files[0]);
        }
    };

    const toggleConnection = (connectionId) => {
        const current = [...data.selected_connections];
        if (current.includes(connectionId)) {
            setData('selected_connections', current.filter(id => id !== connectionId));
        } else {
            setData('selected_connections', [...current, connectionId]);
        }
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('uploads.store'), {
            preserveScroll: true,
            onSuccess: () => {
                setData({ ...data, video: null, title: '', description: '', tags: '' });
                if (fileInputRef.current) fileInputRef.current.value = '';
            }
        });
    };

    const platforms = [
        { id: 'youtube', name: 'YouTube', color: 'bg-[#FF0000]' },
        { id: 'facebook', name: 'Facebook', color: 'bg-[#1877F2]' },
        { id: 'instagram', name: 'Instagram', color: 'bg-gradient-to-tr from-[#f09433] via-[#dc2743] to-[#bc1888]' },
        { id: 'tiktok', name: 'TikTok', color: 'bg-[#000000] border border-white/20' },
    ];

    // Check if user has any connections
    const hasConnections = Object.keys(connections).length > 0;

    return (
        <AuthenticatedLayout header="Upload Video">
            <Head title="Upload Video" />

            <form onSubmit={submit} className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Left Column: Video & Metadata */}
                <div className="lg:col-span-2 space-y-6">
                    
                    {/* Success Message */}
                    {flash?.success && (
                        <div className="bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-xl font-medium shadow-sm">
                            {flash.success}
                        </div>
                    )}

                    {/* Error Message */}
                    {flash?.error && (
                        <div className="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl font-medium shadow-sm flex items-center gap-3">
                            <svg className="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {flash.error}
                        </div>
                    )}

                    {/* Drag and Drop Zone */}
                    <div 
                        className={`border-2 border-dashed ${errors.video ? 'border-red-500/50 bg-red-500/5' : 'border-white/10 bg-gradient-to-b from-[#111] to-[#0a0a0a]'} rounded-3xl p-12 text-center hover:border-indigo-500/50 hover:bg-indigo-500/5 transition-all duration-300 cursor-pointer group shadow-sm relative overflow-hidden`}
                        onClick={() => fileInputRef.current?.click()}
                    >
                        <input type="file" ref={fileInputRef} className="hidden" accept="video/mp4,video/quicktime,video/webm" onChange={handleFileChange} />
                        
                        {data.video ? (
                            <div className="text-white z-10 relative">
                                <svg className="w-16 h-16 mx-auto mb-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <h3 className="text-xl font-bold">{data.video.name}</h3>
                                <p className="text-sm text-slate-400 mt-2">{(data.video.size / (1024*1024)).toFixed(2)} MB • Siap diupload</p>
                            </div>
                        ) : (
                            <div className="z-10 relative">
                                <div className="mx-auto h-20 w-20 text-indigo-400 mb-5 flex items-center justify-center rounded-2xl bg-indigo-500/10 group-hover:scale-110 transition-transform duration-300 shadow-inner">
                                    <svg className="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                </div>
                                <h3 className="text-xl font-bold text-white mb-2">Tarik & Lepas Video ke Sini</h3>
                                <p className="text-sm text-slate-400 mb-6">Atau klik untuk memilih file dari komputer Anda</p>
                                <div className="inline-flex items-center justify-center px-6 py-2.5 border border-white/10 rounded-full text-sm font-medium text-white bg-white/5 hover:bg-white/10 transition-colors">
                                    Pilih File Video
                                </div>
                                <p className="text-xs text-slate-500 mt-6 font-medium tracking-wide uppercase">MP4, MOV, WEBM • Maks 1GB</p>
                            </div>
                        )}
                        <InputError message={errors.video} className="mt-4" />
                    </div>

                    {/* Meta Data */}
                    <div className="bg-[#111] border border-white/5 rounded-3xl p-7 shadow-lg relative overflow-hidden">
                        <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 to-violet-500 opacity-50"></div>
                        
                        <h3 className="text-lg font-bold text-white mb-6">Detail Video</h3>
                        
                        <div className="space-y-5">
                            <div>
                                <label className="block text-sm font-semibold text-slate-300 mb-2">Judul Video (Maks 100 Karakter)</label>
                                <input type="text" maxLength="100" value={data.title} onChange={e => setData('title', e.target.value)} className="w-full bg-[#0a0a0a] border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder:text-slate-600" placeholder="Ketik judul yang mengundang interaksi..." required />
                                <InputError message={errors.title} className="mt-1" />
                            </div>
                            <div>
                                <label className="block text-sm font-semibold text-slate-300 mb-2">Deskripsi (Maks 2200 Karakter)</label>
                                <textarea rows="4" maxLength="2200" value={data.description} onChange={e => setData('description', e.target.value)} className="w-full bg-[#0a0a0a] border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder:text-slate-600 resize-none" placeholder="Ceritakan lebih banyak tentang video ini..."></textarea>
                                <InputError message={errors.description} className="mt-1" />
                            </div>
                            <div>
                                <label className="block text-sm font-semibold text-slate-300 mb-2">Tags / Hashtags (Maks 500 Karakter)</label>
                                <input type="text" maxLength="500" value={data.tags} onChange={e => setData('tags', e.target.value)} className="w-full bg-[#0a0a0a] border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all placeholder:text-slate-600" placeholder="pisahkan, dengan, koma" />
                                <InputError message={errors.tags} className="mt-1" />
                            </div>
                        </div>
                    </div>
                </div>

                {/* Right Column: Platforms & Actions */}
                <div className="space-y-6">
                    {/* Platform Selection */}
                    <div className="bg-[#111] border border-white/5 rounded-3xl p-7 shadow-lg">
                        <div className="flex items-center justify-between mb-5">
                            <h3 className="text-sm font-bold text-slate-400 uppercase tracking-wider">Tujuan Upload</h3>
                            <span className="text-xs font-bold text-indigo-400 bg-indigo-500/10 px-2.5 py-1 rounded-full">{data.selected_connections.length} Terpilih</span>
                        </div>
                        
                        {!hasConnections ? (
                            <div className="text-center p-6 bg-[#0a0a0a] rounded-2xl border border-white/5">
                                <svg className="w-10 h-10 mx-auto text-slate-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                <p className="text-sm text-slate-400 mb-4">Anda belum menghubungkan akun sosmed apapun.</p>
                                <a href={route('connections')} className="inline-block px-4 py-2 bg-indigo-500/10 text-indigo-400 font-semibold rounded-lg hover:bg-indigo-500/20 transition-colors text-sm">
                                    Kelola Koneksi
                                </a>
                            </div>
                        ) : (
                            <div className="space-y-5">
                                {platforms.map(platform => {
                                    const accs = connections[platform.id] || [];
                                    if (accs.length === 0) return null;
                                    
                                    return (
                                        <div key={platform.id} className="space-y-2">
                                            <div className="flex items-center gap-2 mb-2">
                                                <div className={`w-6 h-6 rounded flex items-center justify-center text-white text-xs font-bold ${platform.color}`}>
                                                    {platform.name.charAt(0)}
                                                </div>
                                                <span className="text-sm font-bold text-slate-300">{platform.name}</span>
                                            </div>
                                            
                                            {accs.map(acc => (
                                                <label key={acc.id} className={`flex items-center justify-between p-3 rounded-xl border cursor-pointer transition-all duration-200 ${data.selected_connections.includes(acc.id) ? 'border-indigo-500 bg-indigo-500/10' : 'border-white/5 bg-[#0a0a0a] hover:border-white/20'}`}>
                                                    <input type="checkbox" className="sr-only" checked={data.selected_connections.includes(acc.id)} onChange={() => toggleConnection(acc.id)} />
                                                    <div className="flex items-center gap-3">
                                                        <div className={`w-5 h-5 rounded flex items-center justify-center border ${data.selected_connections.includes(acc.id) ? 'bg-indigo-500 border-indigo-500' : 'bg-transparent border-slate-600'}`}>
                                                            {data.selected_connections.includes(acc.id) && <svg className="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="3" d="M5 13l4 4L19 7"/></svg>}
                                                        </div>
                                                        <span className="font-semibold text-slate-300 text-sm truncate max-w-[140px]">{acc.platform_username || 'Akun'}</span>
                                                    </div>
                                                </label>
                                            ))}
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                        <InputError message={errors.selected_connections} className="mt-2" />
                    </div>

                    {/* Schedule & Submit */}
                    <div className="bg-[#111] border border-white/5 rounded-3xl p-7 shadow-lg">
                        <div className="mb-6 bg-[#0a0a0a] p-4 rounded-2xl border border-white/5 transition-all">
                            <label className="flex items-center justify-between cursor-pointer">
                                <span className="text-sm font-semibold text-slate-300">Jadwalkan Postingan</span>
                                <input 
                                    type="checkbox" 
                                    checked={isScheduled}
                                    onChange={(e) => {
                                        setIsScheduled(e.target.checked);
                                        if (!e.target.checked) setData('scheduled_at', '');
                                    }}
                                    className="rounded bg-white/10 border-white/20 text-indigo-500 focus:ring-indigo-500 w-5 h-5 transition-all" 
                                />
                            </label>
                            
                            {isScheduled && (
                                <div className="mt-4 pt-4 border-t border-white/5 animate-in fade-in slide-in-from-top-2 duration-300">
                                    <label className="block text-xs font-semibold text-slate-400 mb-2">Pilih Tanggal & Waktu</label>
                                    <input 
                                        type="datetime-local" 
                                        value={data.scheduled_at}
                                        onChange={e => setData('scheduled_at', e.target.value)}
                                        className="w-full bg-[#111] border border-white/10 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm [color-scheme:dark]"
                                        min={new Date().toISOString().slice(0, 16)}
                                        required={isScheduled}
                                    />
                                    <InputError message={errors.scheduled_at} className="mt-1" />
                                    <p className="text-xs text-slate-500 mt-2">
                                        Video Anda akan diunggah secara otomatis pada waktu yang ditentukan menggunakan zona waktu lokal Anda.
                                    </p>
                                </div>
                            )}
                        </div>
                        
                        <button 
                            type="submit"
                            disabled={processing || !data.video || data.selected_connections.length === 0}
                            className={`w-full bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-400 hover:to-violet-500 text-white font-bold py-4 px-4 rounded-2xl shadow-lg shadow-indigo-500/25 transition-all duration-200 transform active:scale-[0.98] flex items-center justify-center gap-2 text-base ${processing || !data.video || data.selected_connections.length === 0 ? 'opacity-50 cursor-not-allowed' : ''}`}
                        >
                            {processing ? (
                                <>
                                    <svg className="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Uploading... {progress && `${progress.percentage}%`}
                                </>
                            ) : (
                                <>
                                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    Mulai Upload ke {data.selected_connections.length} Akun
                                </>
                            )}
                        </button>
                    </div>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}
