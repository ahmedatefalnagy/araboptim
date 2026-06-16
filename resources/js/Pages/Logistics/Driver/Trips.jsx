import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Trips({ auth, trips }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="سجل الرحلات" />

            <div className="py-12 bg-gray-50 min-h-screen" dir="rtl">
                <div className="max-w-4xl mx-auto px-4 sm:px-6">
                    
                    <div className="mb-10 flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-black text-slate-900 tracking-tighter italic">سجل الرحلات والمستحقات</h1>
                            <p className="mt-1 text-slate-500 font-bold">كشف تفصيلي بمستحقاتك عن كل رحلة</p>
                        </div>
                        <Link href={route('logistics.driver.dashboard')} className="text-sm font-black text-indigo-500 underline uppercase italic">← العودة للرئيسية</Link>
                    </div>

                    <div className="space-y-4">
                        {trips.data.map(trip => (
                            <div key={trip.id} className="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center justify-between group hover:shadow-lg transition-all">
                                <div className="flex items-center gap-6">
                                    <div className="w-14 h-14 bg-slate-50 rounded-2xl flex flex-col items-center justify-center">
                                        <span className="text-[10px] font-black text-slate-300">#</span>
                                        <span className="text-lg font-black text-slate-900 leading-none">{trip.trip_no}</span>
                                    </div>
                                    <div>
                                        <div className="flex items-center gap-3 text-xs font-black text-slate-400 mb-1 italic">
                                            <span>{trip.origin}</span>
                                            <span>➜</span>
                                            <span>{trip.destination}</span>
                                        </div>
                                        <p className="text-sm font-bold text-slate-600">{new Date(trip.created_at).toLocaleDateString('en-US')}</p>
                                    </div>
                                </div>
                                <div className="text-left">
                                    <p className="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1 italic">صافي التريب</p>
                                    <p className="text-xl font-black text-slate-900">{trip.driver_commission?.toLocaleString()} <span className="text-[10px] text-slate-400">SAR</span></p>
                                </div>
                            </div>
                        ))}

                        {trips.data.length === 0 && (
                            <div className="py-20 text-center text-slate-300 font-black italic">لا يوجد سجل رحلات محفوظ حتى الآن.</div>
                        )}
                    </div>

                    {/* Simple Pagination info */}
                    <div className="mt-8 text-center text-xs font-bold text-slate-400">
                        عرض {trips.data.length} من أصل {trips.total} رحلة
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
