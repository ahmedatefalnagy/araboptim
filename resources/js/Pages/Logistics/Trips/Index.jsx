import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, trips, flash }) {
    
    const getStatusInfo = (status) => {
        return {
            planned: { label: 'مخطط', color: 'bg-slate-100 text-slate-700' },
            loading: { label: 'تحميل', color: 'bg-orange-100 text-orange-700' },
            transit: { label: 'في الطريق', color: 'bg-indigo-100 text-indigo-700' },
            at_destination: { label: 'وصل الموقع', color: 'bg-cyan-100 text-cyan-700' },
            completed: { label: 'مكتمل', color: 'bg-emerald-100 text-emerald-700' },
            cancelled: { label: 'ملغى', color: 'bg-rose-100 text-rose-700' },
        }[status] || { label: status, color: 'bg-gray-50 text-gray-500' };
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="سجل الرحلات - Odoo Style" />

            <div className="min-h-screen bg-[#f8fafc] pb-12" dir="rtl">
                {/* Odoo Style Top Bar */}
                <div className="bg-white border-b border-slate-200 sticky top-0 z-30 px-8 py-4 flex items-center justify-between shadow-sm">
                    <div className="flex items-center gap-4">
                        <h1 className="text-xl font-bold text-slate-800">سجل الرحلات التشغيلي</h1>
                    </div>
                    <div className="flex items-center gap-3">
                        <Link href={route('logistics.trips.create')} className="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-md transition-all">
                            + فتح رحلة جديدة
                        </Link>
                    </div>
                </div>

                <div className="max-w-[1500px] mx-auto p-8">
                    {flash?.success && (
                        <div className="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl text-sm font-bold">
                            {flash.success}
                        </div>
                    )}

                    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <table className="w-full text-right text-sm">
                            <thead>
                                <tr className="bg-slate-50 border-b border-slate-100">
                                    <th className="px-6 py-4 font-black text-slate-400 uppercase text-[10px]">الرحلة</th>
                                    <th className="px-6 py-4 font-black text-slate-400 uppercase text-[10px]">المسار / العميل</th>
                                    <th className="px-6 py-4 font-black text-slate-400 uppercase text-[10px]">الشاحنة / السائق</th>
                                    <th className="px-6 py-4 font-black text-slate-400 uppercase text-[10px] text-center">الديزل المسحوب</th>
                                    <th className="px-6 py-4 font-black text-slate-400 uppercase text-[10px] text-left">صافي السائق</th>
                                    <th className="px-6 py-4 font-black text-slate-400 uppercase text-[10px] text-center">الحالة</th>
                                    <th className="px-6 py-4 font-black text-slate-400 uppercase text-[10px] text-center">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-50 font-bold">
                                {trips.map(trip => (
                                    <tr key={trip.id} className="hover:bg-slate-50/50 transition-colors">
                                        <td className="px-6 py-4">
                                            <div className="flex flex-col">
                                                <span className="text-slate-900">{trip.trip_no}</span>
                                                <span className="text-[10px] text-slate-400 font-medium font-mono">{trip.waybill_no || 'بدون بوليصة'}</span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex flex-col">
                                                <span className="text-slate-800">{trip.origin} ⟷ {trip.destination}</span>
                                                <span className="text-[10px] text-indigo-500">{trip.broker?.name}</span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex flex-col">
                                                <span className="text-slate-800">🚛 {trip.vehicle?.plate_no}</span>
                                                <span className="text-[10px] text-slate-500 font-bold uppercase">{trip.driver?.name}</span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-center">
                                            <span className="text-orange-600 font-mono tracking-tighter">
                                                {trip.diesels?.reduce((sum, d) => sum + parseFloat(d.amount), 0).toLocaleString()}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-left">
                                            <span className="text-emerald-600 font-black">
                                                {(trip.total_trip_budget - trip.diesels?.reduce((sum, d) => sum + parseFloat(d.amount), 0)).toLocaleString()} SAR
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-center">
                                            <span className={`px-3 py-1 rounded-full text-[10px] font-black ${getStatusInfo(trip.status).color}`}>
                                                {getStatusInfo(trip.status).label}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-center">
                                            <Link 
                                                href={route('logistics.trips.show', trip.id)}
                                                className="px-4 py-1.5 bg-white border border-slate-200 text-slate-600 rounded-lg text-[11px] font-bold hover:bg-slate-50 transition-all"
                                            >
                                                متابعة
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                                {trips.length === 0 && (
                                    <tr><td colSpan="7" className="py-20 text-center text-slate-300 italic">لا توجد رحلات مسجلة...</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
