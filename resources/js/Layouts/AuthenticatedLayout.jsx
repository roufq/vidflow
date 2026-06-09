import { Link, usePage, router } from '@inertiajs/react';
import { useState } from 'react';
import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';

export default function AuthenticatedLayout({ header, children }) {
    const { auth, notifications } = usePage().props;
    const user = auth.user;
    const [sidebarOpen, setSidebarOpen] = useState(false);

    return (
        <div className="flex h-screen bg-[#0a0a0a] text-slate-300 font-sans selection:bg-indigo-500/30">
            {/* Mobile Sidebar Overlay */}
            {sidebarOpen && (
                <div 
                    className="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 md:hidden"
                    onClick={() => setSidebarOpen(false)}
                ></div>
            )}

            {/* Sidebar */}
            <aside className={`fixed inset-y-0 left-0 w-64 bg-[#111] border-r border-white/10 transform transition-transform duration-300 ease-in-out z-50 md:relative md:translate-x-0 flex flex-col flex-shrink-0 ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}`}>
                <div className="h-16 flex items-center px-6 border-b border-white/10">
                    <img src="/vidflow-icon.png" alt="VidFlow Logo" className="h-8 w-8 object-contain drop-shadow-lg rounded-lg" />
                    <span className="ml-3 text-xl font-bold text-white tracking-tight">VidFlow</span>
                </div>
                
                <div className="flex-1 overflow-y-auto py-6 px-4 space-y-1.5">
                    <Link href={route('dashboard')} className={`flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all ${route().current('dashboard') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]' : 'text-slate-400 hover:bg-white/5 hover:text-slate-200'}`}>
                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span className="font-semibold text-sm">Upload Video</span>
                    </Link>

                    <Link href={route('analytics')} className={`flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all ${route().current('analytics') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]' : 'text-slate-400 hover:bg-white/5 hover:text-slate-200'}`}>
                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span className="font-semibold text-sm">Analytics</span>
                    </Link>
                    
                    <Link href={route('history')} className={`flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all ${route().current('history') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]' : 'text-slate-400 hover:bg-white/5 hover:text-slate-200'}`}>
                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span className="font-medium text-sm">History & Status</span>
                    </Link>
                    
                    <Link href={route('connections')} className={`flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all ${route().current('connections') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]' : 'text-slate-400 hover:bg-white/5 hover:text-slate-200'}`}>
                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        <span className="font-medium text-sm">Platform Connections</span>
                    </Link>

                    {user?.is_super_admin && (
                        <>
                            <div className="pt-4 mt-4 border-t border-white/10">
                                <span className="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Admin</span>
                            </div>
                            <Link href={route('admin.roles.index')} className={`flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all ${route().current('admin.roles.*') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]' : 'text-slate-400 hover:bg-white/5 hover:text-slate-200'}`}>
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                <span className="font-medium text-sm">Role Management</span>
                            </Link>

                            <Link href={route('admin.users.index')} className={`flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all ${route().current('admin.users.*') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]' : 'text-slate-400 hover:bg-white/5 hover:text-slate-200'}`}>
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                <span className="font-medium text-sm">User Management</span>
                            </Link>
                        </>
                    )}
                </div>
                

            </aside>

            {/* Main Content */}
            <main className="flex-1 flex flex-col min-w-0 overflow-hidden relative">
                {/* Background glow effects */}
                <div className="absolute top-[-20%] right-[-10%] h-[600px] w-[600px] rounded-full bg-indigo-600/10 blur-[120px] pointer-events-none" />
                
                <header className="h-16 flex items-center justify-between px-6 border-b border-white/10 bg-[#0a0a0a]/80 backdrop-blur-md z-30 sticky top-0">
                    <div className="flex items-center gap-3">
                        {/* Mobile Menu Toggle */}
                        <button 
                            onClick={() => setSidebarOpen(true)}
                            className="md:hidden text-slate-400 hover:text-white transition-colors"
                        >
                            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <h1 className="text-lg font-bold text-white tracking-tight">{header || 'Dashboard'}</h1>
                    </div>
                    <div className="flex items-center gap-4">
                        <Dropdown>
                            <Dropdown.Trigger>
                                <button className="text-slate-400 hover:text-white transition-colors relative mt-1">
                                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    {notifications?.length > 0 && (
                                        <span className="absolute -top-1 -right-1 flex h-3 w-3">
                                            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                            <span className="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                                        </span>
                                    )}
                                </button>
                            </Dropdown.Trigger>
                            <Dropdown.Content align="right" width="64" contentClasses="py-1 bg-[#1a1a1a] border border-white/10 shadow-2xl">
                                <div className="px-4 py-2 border-b border-white/10 flex justify-between items-center">
                                    <span className="font-semibold text-white text-sm">Notifications</span>
                                    {notifications?.length > 0 && (
                                        <button onClick={() => router.post(route('notifications.read'), {}, { preserveScroll: true })} className="text-xs text-indigo-400 hover:text-indigo-300">Mark all as read</button>
                                    )}
                                </div>
                                <div className="max-h-64 overflow-y-auto">
                                    {notifications?.length > 0 ? notifications.map(notification => (
                                        <div key={notification.id} className="px-4 py-3 border-b border-white/5 hover:bg-white/5 transition-colors">
                                            <div className="flex items-start gap-3">
                                                <div className={`mt-0.5 w-2 h-2 rounded-full flex-shrink-0 ${notification.data.status === 'failed' ? 'bg-red-500' : 'bg-emerald-500'}`}></div>
                                                <div>
                                                    <p className="text-sm font-medium text-white mb-0.5">
                                                        <span className="capitalize">{notification.data.platform}</span>: {notification.data.title}
                                                    </p>
                                                    <p className="text-xs text-slate-400 line-clamp-2">
                                                        {notification.data.status === 'failed' ? notification.data.error_message : 'Video berhasil dipublikasikan.'}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    )) : (
                                        <div className="px-4 py-6 text-center text-sm text-slate-500">Tidak ada notifikasi baru</div>
                                    )}
                                </div>
                            </Dropdown.Content>
                        </Dropdown>
                        
                        <div className="h-6 w-px bg-white/10 mx-1 hidden sm:block"></div>
                        
                        <Dropdown>
                            <Dropdown.Trigger>
                                <button className="flex items-center gap-3 rounded-xl p-1.5 hover:bg-white/5 transition-colors text-left group">
                                    <div className="h-9 w-9 rounded-lg bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold shadow-lg">
                                        {user.name.charAt(0)}
                                    </div>
                                    <div className="overflow-hidden pr-2">
                                        <p className="truncate text-sm font-semibold text-white group-hover:text-indigo-400 transition-colors">{user.name}</p>
                                        <p className="truncate text-xs text-indigo-300/70 font-medium tracking-wide uppercase">{user.plan} Plan</p>
                                    </div>
                                </button>
                            </Dropdown.Trigger>
                            <Dropdown.Content align="right" width="48" contentClasses="py-1 bg-[#1a1a1a] border border-white/10 shadow-2xl">
                                <Dropdown.Link href={route('profile.edit')} className="text-slate-300 hover:bg-white/5 hover:text-white">Profile Settings</Dropdown.Link>
                                <Dropdown.Link href={route('logout')} method="post" as="button" className="text-red-400 hover:bg-red-500/10 hover:text-red-300">Log Out</Dropdown.Link>
                            </Dropdown.Content>
                        </Dropdown>
                    </div>
                </header>
                
                <div className="flex-1 overflow-y-auto p-6 md:p-8 z-10">
                    <div className="max-w-5xl mx-auto">
                        {children}
                    </div>
                </div>
            </main>
        </div>
    );
}
