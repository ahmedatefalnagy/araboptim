import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function Index({ auth, stats, recent_orders }) {
    const fmt = (num) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR' }).format(num || 0);

    const getStatusLabel = (status) => {
        const map = {
            'draft': { label: 'مسودة', color: 'bg-slate-100 text-slate-600' },
            'pending_parts': { label: 'انتظار قطع', color: 'bg-orange-100 text-orange-600' },
            'in_progress': { label: 'قيد العمل', color: 'bg-indigo-100 text-indigo-600' },
            'completed': { label: 'مكتمل', color: 'bg-emerald-100 text-emerald-600' },
            'cancelled': { label: 'ملغي', color: 'bg-rose-100 text-rose-600' },
        };
        return map[status] || { label: status, color: 'bg-slate-100 text-slate-600' };
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="إدارة الورشة والصيانة" />

            <div className="min-h-screen bg-[#f8fafc] pb-12" dir="rtl">
                {/* Top Bar */}
                <div className="bg-white border-b border-slate-200 sticky top-0 z-30 px-8 py-4 flex items-center justify-between shadow-sm">
                    <div className="flex items-center gap-6">
                        <h1 className="text-xl font-bold text-slate-800 flex items-center gap-2">
                            <span className="text-2xl">🛠️</span> الورشة المركزية
                        </h1>
                        <div className="flex items-center gap-2 bg-slate-100 rounded-lg p-1">
                            <button className="px-4 py-1.5 bg-white shadow-sm rounded-md text-sm font-bold text-indigo-600">الأوامر الحالية</button>
                            <Link href={route('logistics.workshop.orders')} className="px-4 py-1.5 hover:bg-white/50 rounded-md text-sm font-medium text-slate-600 transition-all">سجل الصيانات</Link>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        <Link href={route('logistics.workshop.orders')} className="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-md transition-all">
                            + فتح أمر صيانة
                        </Link>
                    </div>
                </div>

                <div className="max-w-[1600px] mx-auto px-8 pt-8">
                    {/* Key Metrics */}
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <StatCard label="طلبات معلقة" value={stats?.pending_orders || 0} icon="⏳" color="text-orange-600" />
                        <StatCard label="شاحنات في العمل" value={stats?.in_progress || 0} icon="⚙️" color="text-indigo-600" />
                        <StatCard label="تنبيهات الزيت (AI)" value={stats?.oil_alerts || 0} icon="🛢️" color="text-rose-600" />
                        <StatCard label="تنبيهات الكفرات" value={stats?.tire_alerts || 0} icon="🛞" color="text-rose-600" />
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        {/* Maintenance Orders Table */}
                        <div className="lg:col-span-12">
                            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                                <div className="p-6 border-b border-slate-100 flex items-center justify-between">
                                    <h2 className="text-lg font-bold text-slate-800">آخر أوامر الصيانة</h2>
                                    <div className="flex items-center gap-2">
                                        <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest">تصفية حسب الحالة</span>
                                        <select className="bg-slate-50 border-slate-200 rounded-xl text-xs px-4 py-1.5 focus:ring-2 focus:ring-indigo-500">
                                            <option>الكل</option>
                                            <option>قيد العمل</option>
                                            <option>بانتظار القطع</option>
                                        </select>
                                    </div>
                                </div>

                                <div className="overflow-x-auto">
                                    <table className="w-full text-right border-collapse">
                                        <thead>
                                            <tr className="bg-slate-50/50">
                                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">رقم الأمر</th>
                                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">الشاحنة / السائق</th>
                                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">النوع / الوصف</th>
                                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">الحالة</th>
                                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">التكلفة المتوقعة</th>
                                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">التاريخ</th>
                                                <th className="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">الإجراء</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {recent_orders?.length > 0 ? recent_orders.map((order) => (
                                                <tr key={order.id} className="hover:bg-slate-50/50 transition-colors group">
                                                    <td className="px-6 py-5 border-b border-slate-50">
                                                        <span className="text-sm font-black text-indigo-600">{order.order_no}</span>
                                                    </td>
                                                    <td className="px-6 py-5 border-b border-slate-50">
                                                        <div className="flex flex-col">
                                                            <span className="text-sm font-bold text-slate-900">{order.vehicle?.plate_no}</span>
                                                            <span className="text-[10px] text-slate-400 font-medium">{order.driver?.name}</span>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-5 border-b border-slate-50">
                                                        <div className="flex flex-col gap-1">
                                                            <span className={`w-fit px-2 py-0.5 rounded text-[8px] font-black uppercase ${
                                                                order.type === 'emergency' ? 'bg-rose-500 text-white' : 
                                                                order.type === 'preventive' ? 'bg-indigo-500 text-white' : 'bg-slate-200 text-slate-700'
                                                            }`}>
                                                                {order.type === 'emergency' ? 'طارئ 🚨' : order.type === 'preventive' ? 'دوري 📅' : 'عادي 🔧'}
                                                            </span>
                                                            <span className="text-xs text-slate-600 truncate max-w-[200px]">{order.issue_description}</span>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-5 border-b border-slate-50">
                                                        <span className={`px-3 py-1 rounded-full text-[10px] font-bold ${getStatusLabel(order.status).color}`}>
                                                            {getStatusLabel(order.status).label}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-5 border-b border-slate-50">
                                                        <span className="text-sm font-black text-slate-800">{fmt(order.total_parts_cost + order.labor_cost)}</span>
                                                    </td>
                                                    <td className="px-6 py-5 border-b border-slate-50">
                                                        <span className="text-xs text-slate-500 font-medium">{new Date(order.created_at).toLocaleDateString('ar-SA')}</span>
                                                    </td>
                                                    <td className="px-6 py-5 border-b border-slate-50">
                                                        <button className="p-2 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-indigo-600 transition-colors">
                                                            🔍
                                                        </button>
                                                    </td>
                                                </tr>
                                            )) : (
                                                <tr>
                                                    <td colSpan="7" className="px-6 py-10 text-center text-slate-400 italic text-sm">
                                                        لا توجد أوامر صيانة نشطة حالياً
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function StatCard({ label, value, icon, color }) {
    return (
        <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-5 hover:shadow-md transition-all">
            <span className="text-3xl p-3 bg-slate-50 rounded-2xl">{icon}</span>
            <div>
                <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{label}</p>
                <p className={`text-3xl font-black ${color} tracking-tighter`}>{value}</p>
            </div>
        </div>
    );
}
