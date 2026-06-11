import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';

export default function Welcome({ auth }) {
    return (
        <>
            <Head title="VidFlow - Automasi Sosmed Terbaik" />
            
            <div className="min-h-screen bg-[#050505] text-white selection:bg-indigo-500 selection:text-white font-sans overflow-x-hidden">
                {/* Background Effects */}
                <div className="fixed inset-0 z-0 pointer-events-none">
                    <div className="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-600/20 blur-[120px]"></div>
                    <div className="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-purple-600/20 blur-[120px]"></div>
                    <div className="absolute top-[40%] left-[30%] w-[20%] h-[20%] rounded-full bg-blue-600/10 blur-[100px]"></div>
                    <div className="absolute inset-0 bg-[url('https://laravel.com/assets/img/welcome/background.svg')] bg-cover bg-center opacity-5 mix-blend-overlay"></div>
                </div>

                {/* Navigation */}
                <nav className="relative z-10 w-full px-6 py-6 md:px-12 flex justify-between items-center border-b border-white/5 bg-black/20 backdrop-blur-md">
                    <div className="flex items-center gap-3">
                        <img src="/vidflow-icon.png" alt="VidFlow Logo" className="w-10 h-10 object-contain rounded-xl shadow-lg shadow-indigo-500/20" />
                        <span className="text-2xl font-bold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-400">
                            VidFlow
                        </span>
                    </div>
                    
                    <div className="flex items-center gap-4">
                        {auth.user ? (
                            <Link
                                href={route('dashboard')}
                                className="text-sm font-semibold text-white bg-white/10 hover:bg-white/20 px-4 py-2 sm:px-5 sm:py-2.5 rounded-full transition-all border border-white/10 backdrop-blur-md"
                            >
                                Masuk Dashboard
                            </Link>
                        ) : (
                            <>
                                {/* Desktop */}
                                <div className="hidden sm:flex items-center gap-4">
                                    <Link
                                        href={route('login')}
                                        className="text-sm font-semibold text-slate-300 hover:text-white px-4 py-2 transition-colors"
                                    >
                                        Log in
                                    </Link>
                                    <Link
                                        href={route('register')}
                                        className="text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 px-6 py-2.5 rounded-full transition-all shadow-[0_0_20px_rgba(79,70,229,0.3)] hover:shadow-[0_0_25px_rgba(79,70,229,0.5)]"
                                    >
                                        Daftar Gratis
                                    </Link>
                                </div>
                                
                                {/* Mobile */}
                                <div className="sm:hidden flex items-center bg-white/5 p-1 rounded-full border border-white/10">
                                    <Link
                                        href={route('login')}
                                        className="text-xs font-semibold text-slate-300 hover:text-white px-4 py-2 transition-colors rounded-full"
                                    >
                                        Log in
                                    </Link>
                                    <Link
                                        href={route('register')}
                                        className="text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded-full transition-all shadow-lg shadow-indigo-500/30"
                                    >
                                        Daftar
                                    </Link>
                                </div>
                            </>
                        )}
                    </div>
                </nav>

                <main className="relative z-10">
                    {/* Hero Section */}
                    <section className="px-6 pt-24 pb-16 md:pt-32 md:pb-24 max-w-6xl mx-auto text-center flex flex-col items-center">
                        <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs font-semibold uppercase tracking-wider mb-8 animate-pulse">
                            <span className="w-2 h-2 rounded-full bg-indigo-500"></span>
                            Rilis Versi 2.0
                        </div>
                        
                        <h1 className="text-5xl md:text-7xl font-extrabold tracking-tight mb-8 leading-[1.1]">
                            Upload 1 Video ke <br />
                            <span className="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400">
                                Semua Sosmed Sekaligus
                            </span>
                        </h1>
                        
                        <p className="text-lg md:text-xl text-slate-400 max-w-2xl mx-auto mb-12 leading-relaxed">
                            Aplikasi andalan Agensi dan Kreator. Hemat waktu berjam-jam setiap harinya dengan menjadwalkan konten secara otomatis ke YouTube, TikTok, Instagram, dan Facebook.
                        </p>
                        
                        <div className="flex flex-col sm:flex-row gap-4 items-center justify-center mb-16">
                            <Link
                                href={auth.user ? route('dashboard') : route('register')}
                                className="w-full sm:w-auto px-8 py-4 bg-white text-black font-bold rounded-full hover:scale-105 transition-transform duration-300 shadow-[0_0_30px_rgba(255,255,255,0.2)]"
                            >
                                Mulai Sekarang - Gratis
                            </Link>
                            <a
                                href="#pricing"
                                className="w-full sm:w-auto px-8 py-4 bg-white/5 text-white font-bold rounded-full hover:bg-white/10 border border-white/10 transition-colors"
                            >
                                Lihat Harga
                            </a>
                        </div>
                    </section>

                    {/* Dashboard Mockup Placeholder */}
                    <section className="px-6 mb-24 max-w-5xl mx-auto relative group">
                        <div className="absolute -inset-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-2xl blur opacity-25 group-hover:opacity-40 transition duration-1000 group-hover:duration-200"></div>
                        <div className="relative rounded-2xl border border-white/10 bg-[#0a0a0a]/90 backdrop-blur-xl shadow-2xl overflow-hidden aspect-[16/9] flex items-center justify-center">
                            <div className="absolute top-0 left-0 w-full h-8 bg-white/5 border-b border-white/5 flex items-center px-4 gap-2">
                                <div className="w-3 h-3 rounded-full bg-red-500/50"></div>
                                <div className="w-3 h-3 rounded-full bg-amber-500/50"></div>
                                <div className="w-3 h-3 rounded-full bg-emerald-500/50"></div>
                            </div>
                            <div className="text-center opacity-40 group-hover:opacity-100 transition-opacity duration-500">
                                <img src="/vidflow-icon.png" alt="Dashboard Preview" className="w-20 h-20 mx-auto mb-4 drop-shadow-2xl" />
                                <p className="text-slate-300 font-medium tracking-wide">Screenshot Dashboard Aplikasi</p>
                                <p className="text-slate-500 text-sm mt-2">Ganti container ini dengan gambar screenshot dashboard asli</p>
                            </div>
                        </div>
                    </section>

                    {/* Platform Integrations */}
                    <section className="py-16 border-y border-white/5 bg-black/40 backdrop-blur-sm">
                        <div className="max-w-6xl mx-auto px-6 text-center">
                            <p className="text-sm font-semibold text-slate-500 uppercase tracking-widest mb-10">
                                Terintegrasi Resmi Dengan API Pihak Ketiga
                            </p>
                            <div className="flex flex-wrap justify-center gap-8 md:gap-16 opacity-80 grayscale hover:grayscale-0 transition-all duration-500">
                                {/* YouTube */}
                                <div className="flex items-center gap-3 text-2xl font-bold text-white hover:scale-110 transition-transform">
                                    <div className="w-12 h-12 bg-[#FF0000] rounded-2xl flex items-center justify-center">
                                        <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                    </div>
                                    YouTube
                                </div>
                                {/* TikTok */}
                                <div className="flex items-center gap-3 text-2xl font-bold text-white hover:scale-110 transition-transform">
                                    <div className="w-12 h-12 bg-white text-black rounded-2xl flex items-center justify-center">
                                        <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 2.23-.9 4.4-2.42 5.95-1.7 1.65-4.04 2.5-6.36 2.4-2.72-.05-5.32-1.35-6.95-3.41-1.46-1.92-2.05-4.42-1.55-6.72.44-2.18 1.75-4.11 3.58-5.26 1.76-1.07 3.89-1.4 5.91-1.05v4.21c-1.2-.1-2.42.2-3.41.87-.93.63-1.57 1.62-1.75 2.73-.2 1.25.1 2.55.85 3.53.7.88 1.83 1.35 2.95 1.35 1.62 0 3.09-1.12 3.46-2.7.13-.58.17-1.18.17-1.77V.02h4.51z"/></svg>
                                    </div>
                                    TikTok
                                </div>
                                {/* Instagram */}
                                <div className="flex items-center gap-3 text-2xl font-bold text-white hover:scale-110 transition-transform">
                                    <div className="w-12 h-12 bg-gradient-to-tr from-[#f09433] via-[#dc2743] to-[#bc1888] rounded-2xl flex items-center justify-center">
                                        <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                    </div>
                                    Instagram
                                </div>
                                {/* Facebook */}
                                <div className="flex items-center gap-3 text-2xl font-bold text-white hover:scale-110 transition-transform">
                                    <div className="w-12 h-12 bg-[#1877F2] rounded-2xl flex items-center justify-center">
                                        <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    </div>
                                    Facebook
                                </div>
                            </div>
                        </div>
                    </section>

                    {/* How It Works */}
                    <section className="py-24 px-6 max-w-6xl mx-auto">
                        <div className="text-center mb-16">
                            <h2 className="text-3xl md:text-5xl font-bold mb-4">Semudah 1-2-3</h2>
                            <p className="text-slate-400 max-w-2xl mx-auto text-lg">Tinggalkan cara lama mengunggah video satu per satu. Biarkan sistem kami bekerja untuk Anda.</p>
                        </div>
                        
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <StepCard 
                                number="1"
                                title="Hubungkan Akun"
                                desc="Login dengan aman menggunakan akun sosial media Anda secara resmi."
                            />
                            <StepCard 
                                number="2"
                                title="Upload 1 Video"
                                desc="Unggah video dan tulis deskripsi/caption. Sistem kami menyimpannya di Cloud."
                            />
                            <StepCard 
                                number="3"
                                title="Otomatis Tersebar"
                                desc="Atur jadwal tayang, lalu bersantai. Video akan terpublikasi ke semua platform otomatis."
                            />
                        </div>
                    </section>

                    {/* Key Benefits */}
                    <section className="py-24 bg-gradient-to-b from-[#0a0a0a] to-[#050505] border-t border-white/5">
                        <div className="max-w-6xl mx-auto px-6">
                            <div className="text-center mb-16">
                                <h2 className="text-3xl md:text-5xl font-bold mb-4">Mengapa Memilih VidFlow?</h2>
                                <p className="text-slate-400 max-w-2xl mx-auto text-lg">Fitur dirancang khusus untuk memecahkan masalah para Social Media Manager.</p>
                            </div>
                            
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <FeatureCard 
                                    icon={<path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />}
                                    title="Hemat 10+ Jam / Minggu"
                                    desc="Jangan buang waktu Anda mengunggah konten yang sama ke platform berbeda. Lakukan sekali, selesai."
                                />
                                <FeatureCard 
                                    icon={<path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />}
                                    title="Penyimpanan Cloud 5TB"
                                    desc="Infrastruktur kami didukung Google Drive 5TB. Tidak perlu khawatir hardisk laptop Anda penuh oleh video."
                                />
                                <FeatureCard 
                                    icon={<path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />}
                                    title="Analitik Terpusat"
                                    desc="Pantau pertumbuhan Views, Likes, dan Engagement dari semua platform langsung dari dashboard VidFlow."
                                />
                            </div>
                        </div>
                    </section>

                    {/* Pricing Section */}
                    <section id="pricing" className="py-24 px-6 max-w-7xl mx-auto border-t border-white/5">
                        <div className="text-center mb-16">
                            <h2 className="text-3xl md:text-5xl font-bold mb-4">Investasi Super Terjangkau</h2>
                            <p className="text-slate-400 max-w-2xl mx-auto text-lg">Pilih paket yang sesuai dengan kebutuhan konten Anda. Lebih hemat daripada menggaji Admin Sosmed.</p>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <PricingCard 
                                name="Free"
                                price="Rp 0"
                                desc="Untuk coba-coba"
                                features={["1 Akun per Platform", "5 Video / bulan", "Maksimal 50 MB / video", "Analytics Basic"]}
                                noFeatures={["Penjadwalan (Schedule)"]}
                                buttonText="Mulai Gratis"
                            />
                            <PricingCard 
                                name="Standard"
                                originalPrice="Rp 59rb"
                                price="Rp 29rb"
                                period="/ bln"
                                desc="Kreator Pemula"
                                features={["1 Akun per Platform", "25 Video / bulan", "Maksimal 200 MB / video", "Penjadwalan (Maks 30 Hari)", "Analytics Lengkap"]}
                                buttonText="Pilih Standard"
                                isPopular={false}
                            />
                            <PricingCard 
                                name="Pro"
                                originalPrice="Rp 179rb"
                                price="Rp 99rb"
                                period="/ bln"
                                desc="UMKM & Kreator"
                                features={["3 Akun per Platform", "80 Video / bulan", "Maksimal 500 MB / video", "Penjadwalan (Maks 90 Hari)", "Prioritas Antrean"]}
                                buttonText="Pilih Pro"
                                isPopular={true}
                            />
                            <PricingCard 
                                name="Bisnis"
                                originalPrice="Rp 499rb"
                                price="Rp 249rb"
                                period="/ bln"
                                desc="Agensi & Brand"
                                features={["Akun Unlimited", "Upload Unlimited", "Maksimal 1 GB / video", "Penjadwalan (365 Hari)", "Dedicated Support"]}
                                buttonText="Pilih Bisnis"
                                isPopular={false}
                            />                    </div>
                    </section>

                    {/* FAQ Section */}
                    <section className="py-24 bg-[#111] border-y border-white/5">
                        <div className="max-w-3xl mx-auto px-6">
                            <div className="text-center mb-12">
                                <h2 className="text-3xl font-bold mb-4">Pertanyaan yang Sering Diajukan</h2>
                            </div>
                            
                            <div className="space-y-4">
                                <FAQItem 
                                    question="Apakah aplikasi ini aman untuk akun sosial media saya?"
                                    answer="Sangat aman. Kami menggunakan sistem Login OAuth Resmi (API) langsung dari Meta, Google, dan TikTok. Kami tidak pernah meminta password Anda, dan akun Anda terhindar dari pemblokiran karena menggunakan jalur legal."
                                />
                                <FAQItem 
                                    question="Apakah saya harus langganan setahun penuh?"
                                    answer="Tidak. Anda bebas berlangganan bulanan dan membatalkannya kapan saja tanpa biaya penalti."
                                />
                                <FAQItem 
                                    question="Apakah saya butuh API Key (App ID / Secret) sendiri?"
                                    answer="Ya. Untuk menjaga keamanan, kebebasan, dan menghindari limitasi (pemblokiran) dari sistem Meta dan Google, setiap pengguna diwajibkan untuk menghubungkan App ID dan App Secret milik mereka sendiri. Kami menyediakan panduan lengkap cara mendapatkannya dengan mudah."
                                />
                                <FAQItem 
                                    question="Berapa lama video akan tersimpan di VidFlow?"
                                    answer="Video yang telah dipublikasikan akan disimpan secara aman di Cloud Storage kami untuk keperluan arsip, kecuali Anda menghapusnya secara manual melalui dashboard."
                                />
                            </div>
                        </div>
                    </section>
                </main>

                {/* Footer */}
                <footer className="relative z-10 py-12 px-6 bg-black">
                    <div className="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
                        <div className="flex items-center gap-2">
                            <img src="/vidflow-icon.png" alt="VidFlow Logo" className="w-8 h-8 object-contain rounded-lg" />
                            <span className="font-bold text-white">VidFlow</span>
                        </div>
                        <p className="text-slate-500 text-sm">
                            © {new Date().getFullYear()} VidFlow. All rights reserved. Built for Content Creators.
                        </p>
                        <div className="flex gap-4 text-sm text-slate-400">
                            <a href="/privacy" className="hover:text-white transition-colors">Privacy Policy</a>
                            <a href="/privacy" className="hover:text-white transition-colors">Terms of Service</a>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}

// Komponen Tambahan
function StepCard({ number, title, desc }) {
    return (
        <div className="bg-[#111] border border-white/10 p-8 rounded-3xl relative overflow-hidden group hover:border-indigo-500/50 transition-colors">
            <div className="text-8xl font-black text-white/[0.03] absolute -top-4 -right-4 group-hover:text-indigo-500/10 transition-colors">{number}</div>
            <div className="w-12 h-12 bg-indigo-500/20 text-indigo-400 rounded-2xl flex items-center justify-center font-bold text-xl mb-6">{number}</div>
            <h3 className="text-xl font-bold text-white mb-3">{title}</h3>
            <p className="text-slate-400 leading-relaxed">{desc}</p>
        </div>
    );
}

function FeatureCard({ icon, title, desc }) {
    return (
        <div className="bg-white/5 border border-white/10 p-8 rounded-3xl hover:bg-white/10 transition-colors">
            <div className="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white mb-6">
                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">{icon}</svg>
            </div>
            <h3 className="text-xl font-bold text-white mb-3">{title}</h3>
            <p className="text-slate-400 leading-relaxed">{desc}</p>
        </div>
    );
}

function PricingCard({ name, price, originalPrice, period, desc, features, noFeatures = [], buttonText, isPopular }) {
    return (
        <div className={`relative flex flex-col p-8 rounded-3xl ${isPopular ? 'bg-gradient-to-b from-indigo-900/40 to-black border-indigo-500 shadow-[0_0_30px_rgba(79,70,229,0.2)] border-2' : 'bg-[#111] border border-white/10 hover:border-white/20 transition-colors'}`}>
            {isPopular && (
                <div className="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-indigo-500 text-white px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest shadow-lg">
                    Best Seller
                </div>
            )}
            
            <h3 className="text-xl font-bold text-white mb-2">{name}</h3>
            <p className="text-sm text-slate-400 mb-6">{desc}</p>
            
            <div className="mb-6">
                {originalPrice && (
                    <div className="text-sm font-semibold text-slate-500 line-through mb-1">{originalPrice}</div>
                )}
                <span className="text-4xl font-extrabold text-white">{price}</span>
                {period && <span className="text-slate-500 ml-1">{period}</span>}
            </div>
            
            <Link 
                href={route('register')} 
                className={`w-full py-3 rounded-xl font-bold text-sm text-center mb-8 transition-colors ${isPopular ? 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg' : 'bg-white/10 hover:bg-white/20 text-white'}`}
            >
                {buttonText}
            </Link>
            
            <div className="flex-1">
                <ul className="space-y-4 text-sm text-slate-300">
                    {features.map((feat, i) => (
                        <li key={i} className="flex items-start gap-3">
                            <svg className="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"/></svg>
                            {feat}
                        </li>
                    ))}
                    {noFeatures.map((feat, i) => (
                        <li key={i} className="flex items-start gap-3 text-slate-500">
                            <svg className="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            {feat}
                        </li>
                    ))}
                </ul>
            </div>
        </div>
    );
}

function FAQItem({ question, answer }) {
    const [isOpen, setIsOpen] = useState(false);
    
    return (
        <div className="border border-white/10 rounded-2xl bg-black/20 overflow-hidden">
            <button 
                onClick={() => setIsOpen(!isOpen)}
                className="w-full px-6 py-4 text-left flex justify-between items-center focus:outline-none"
            >
                <span className="font-semibold text-white pr-4">{question}</span>
                <svg 
                    className={`w-5 h-5 text-slate-400 transform transition-transform duration-200 ${isOpen ? 'rotate-180' : ''}`} 
                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                >
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            
            <div 
                className={`px-6 text-slate-400 text-sm leading-relaxed overflow-hidden transition-all duration-300 ease-in-out ${isOpen ? 'max-h-40 pb-4 opacity-100' : 'max-h-0 opacity-0'}`}
            >
                {answer}
            </div>
        </div>
    );
}
