import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Dashboard({ auth, currentTrip, stats }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="بوابة السائق" />

            <div className="py-12 bg-slate-50 min-h-screen" dir="rtl">
                <div className="max-w-4xl mx-auto px-4 sm:px-6">
                    
                    {/* Welcome Header */}
                    <div className="bg-[#1e1b4b] rounded-[2.5rem] p-10 text-white mb-8 relative overflow-hidden shadow-2xl">
                        <div className="relative z-10">
                            <h1 className="text-3xl font-black italic tracking-tighter mb-2">مرحباً، كابتن {auth.user.name} 👋</h1>
                            <p className="text-indigo-200 font-bold opacity-80 uppercase text-xs tracking-widest">Driver Dashboard Portal</p>
                        </div>
                        <div className="absolute -bottom-10 -left-10 text-[10rem] opacity-5 select-none">🚛</div>
                    </div>

                    {/* Stats Grid */}
                    <div className="grid grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <div className="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex flex-col items-center justify-center text-center">
                            <p className="text-[10px] font-black text-slate-400 uppercase mb-2 italic">الرحلات المكتملة</p>
                            <p className="text-3xl font-black text-slate-900">{stats.completed_trips}</p>
                        </div>
                        <div className="bg-emerald-50 p-6 rounded-[2rem] shadow-sm border border-emerald-100 flex flex-col items-center justify-center text-center">
                            <p className="text-[10px] font-black text-emerald-400 uppercase mb-2 italic">مستحقات التريب (صافي)</p>
                            <p className="text-3xl font-black text-emerald-700">{stats.total_commissions.toLocaleString()} <span className="text-xs">SAR</span></p>
                        </div>
                        <div className="bg-orange-50 p-6 rounded-[2rem] shadow-sm border border-orange-100 flex flex-col items-center justify-center text-center col-span-2 lg:col-span-1">
                            <p className="text-[10px] font-black text-orange-400 uppercase mb-2 italic">طلبات الصيانة المعلقة</p>
                            <p className="text-3xl font-black text-orange-600">{stats.pending_maintenance}</p>
                        </div>
                    </div>

                    {/* Current Trip Card */}
                    {currentTrip ? (
                        <div className="bg-white rounded-[2.5rem] shadow-xl border border-indigo-100 overflow-hidden mb-8">
                            <div className="p-8 border-b border-indigo-50 flex items-center justify-between">
                                <h3 className="text-xl font-black italic text-indigo-900 flex items-center gap-3">
                                    <span className="relative flex h-3 w-3">
                                      <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                      <span className="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
                                    </span>
                                    الرحلة الحالية النشطة
                                </h3>
                                <Link href={route('logistics.trips.show', currentTrip.id)} className="bg-indigo-600 text-white px-6 py-2 rounded-xl text-xs font-black shadow-lg">التفاصيل ↗</Link>
                            </div>
                            <div className="p-10">
                                <div className="flex flex-col md:flex-row items-center justify-between gap-8">
                                    <div className="text-center md:text-right">
                                        <p className="text-[10px] font-black text-slate-400 uppercase mb-1">من (تحميل)</p>
                                        <p className="text-2xl font-black text-slate-900 italic">{currentTrip.origin}</p>
                                    </div>
                                    <div className="flex-1 flex items-center justify-center gap-4 text-indigo-200">
                                        <div className="h-[2px] w-full bg-indigo-100 rounded-full"></div>
                                        <span className="text-2xl animate-pulse">🚚</span>
                                        <div className="h-[2px] w-full bg-indigo-100 rounded-full"></div>
                                    </div>
                                    <div className="text-center md:text-left">
                                        <p className="text-[10px] font-black text-slate-400 uppercase mb-1">إلى (تفريغ)</p>
                                        <p className="text-2xl font-black text-slate-900 italic">{currentTrip.destination}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div className="bg-slate-50 border-2 border-dashed border-slate-200 rounded-[2.5rem] p-12 text-center mb-8">
                            <p className="text-slate-400 font-bold italic">لا يوجد رحلة نشطة حالياً. استمتع بوقتك! 😎</p>
                        </div>
                    )}

                    {/* Actions Area */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Link href={route('logistics.workshop.requests')} className="bg-orange-500 hover:bg-orange-600 text-white p-8 rounded-[2rem] shadow-xl shadow-orange-100 transition-all flex items-center justify-between group">
                            <div>
                                <h4 className="text-xl font-black italic">طلب صيانة / عطل</h4>
                                <p className="text-orange-100 text-xs font-bold opacity-80 mt-1">أبلغ عن مشكلة فنية في الشاحنة</p>
                            </div>
                            <span className="text-3xl group-hover:translate-x-2 transition-transform">🔧</span>
                        </Link>
                        <Link href={route('logistics.driver.trips')} className="bg-white border-2 border-slate-100 hover:border-slate-300 p-8 rounded-[2rem] shadow-sm transition-all flex items-center justify-between group">
                            <div>
                                <h4 className="text-xl font-black italic text-slate-800">سجل الرحلات والمستحقات</h4>
                                <p className="text-slate-400 text-xs font-bold opacity-80 mt-1">كشف حسابك لآخر 30 يوم</p>
                            </div>
                            <span className="text-3xl group-hover:translate-x-2 transition-transform">📊</span>
                        </Link>
                    </div>

                </div>
            </div>
        </AuthenticatedLayout>
    );
}
