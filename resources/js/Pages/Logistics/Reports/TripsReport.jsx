import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

const statusLabels = {
    planned: 'مخطط',
    loading: 'تحميل',
    transit: 'في الطريق',
    completed: 'مكتملة',
    cancelled: 'ملغاة'
};

const statusColors = {
    planned: 'bg-slate-100 text-slate-600',
    loading: 'bg-blue-100 text-blue-600',
    transit: 'bg-orange-100 text-orange-600',
    completed: 'bg-emerald-100 text-emerald-600',
    cancelled: 'bg-red-100 text-red-600'
};

export default function TripsReport({ auth, trips, drivers, brokers, filters }) {
    const [params, setParams] = useState(filters);

    const handleFilter = () => {
        router.get(route('logistics.reports.trips'), params, { preserveState: true });
    };

    const totalBudget = trips.reduce((sum, t) => sum + parseFloat(t.total_trip_budget || 0), 0);
    const totalFuel = trips.reduce((sum, t) => sum + parseFloat(t.fuel_cost || 0), 0);
    const totalCommission = trips.reduce((sum, t) => sum + parseFloat(t.driver_commission || 0), 0);

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="تقرير الرحلات الشامل" />

            <div className="py-12 bg-gray-50 min-h-screen" dir="rtl">
                <div className="max-w-7xl mx-auto px-4 sm:px-6">
                    
                    <div className="mb-8 flex justify-between items-center">
                        <div>
                            <h1 className="text-3xl font-black text-slate-900 italic tracking-tighter">تقرير تحليل الرحلات التشغيلي</h1>
                            <p className="text-gray-500 font-bold">ملخص الأداء المالي والزمني للأسطول</p>
                        </div>
                        <button onClick={() => window.print()} className="bg-white border-2 border-slate-200 px-6 py-3 rounded-2xl font-black text-slate-600 hover:bg-slate-50 transition-all">🖨️ طباعة التقرير</button>
                    </div>

                    {/* Stats Blocks */}
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div className="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 italic">
                            <p className="text-[10px] font-black text-slate-400 uppercase mb-2">إجمالي الرحلات</p>
                            <p className="text-3xl font-black text-slate-900">{trips.length}</p>
                        </div>
                        <div className="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 italic">
                            <p className="text-[10px] font-black text-slate-400 uppercase mb-2">إجمالي الميزانيات</p>
                            <p className="text-3xl font-black text-indigo-600">{totalBudget.toLocaleString()} <span className="text-xs">SAR</span></p>
                        </div>
                        <div className="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 italic">
                            <p className="text-[10px] font-black text-slate-400 uppercase mb-2">تكلفة الديزل الإجمالية</p>
                            <p className="text-3xl font-black text-orange-600">{totalFuel.toLocaleString()} <span className="text-xs">SAR</span></p>
                        </div>
                        <div className="bg-emerald-50 p-6 rounded-[2rem] shadow-sm border border-emerald-100 italic">
                            <p className="text-[10px] font-black text-emerald-400 uppercase mb-2">إجمالي عمولات السائقين</p>
                            <p className="text-3xl font-black text-emerald-700">{totalCommission.toLocaleString()} <span className="text-xs">SAR</span></p>
                        </div>
                    </div>

                    {/* Filters */}
                    <div className="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 mb-8">
                        <div className="grid grid-cols-2 md:grid-cols-5 gap-4 items-end">
                            <div>
                                <label className="block text-[10px] font-black text-slate-400 mb-2">الحالة</label>
                                <select className="w-full rounded-xl border-slate-100 bg-slate-50 font-bold" value={params.status || ''} onChange={e => setParams({...params, status: e.target.value})}>
                                    <option value="">الكل</option>
                                    <option value="planned">مخطط</option>
                                    <option value="transit">في الطريق</option>
                                    <option value="completed">مكتملة</option>
                                </select>
                            </div>
                            <div>
                                <label className="block text-[10px] font-black text-slate-400 mb-2">السائق</label>
                                <select className="w-full rounded-xl border-slate-100 bg-slate-50 font-bold" value={params.driver_id || ''} onChange={e => setParams({...params, driver_id: e.target.value})}>
                                    <option value="">الكل</option>
                                    {drivers.map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-[10px] font-black text-slate-400 mb-2">من تاريخ</label>
                                <input type="date" className="w-full rounded-xl border-slate-100 bg-slate-50 font-bold" value={params.from_date || ''} onChange={e => setParams({...params, from_date: e.target.value})} />
                            </div>
                            <div>
                                <label className="block text-[10px] font-black text-slate-400 mb-2">إلى تاريخ</label>
                                <input type="date" className="w-full rounded-xl border-slate-100 bg-slate-50 font-bold" value={params.to_date || ''} onChange={e => setParams({...params, to_date: e.target.value})} />
                            </div>
                            <button onClick={handleFilter} className="bg-indigo-900 text-white font-black py-3 rounded-xl hover:bg-black transition-all">تحديث البحث 🔍</button>
                        </div>
                    </div>

                    {/* Table */}
                    <div className="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
                        <table className="w-full text-right text-sm">
                            <thead className="bg-slate-50 border-b">
                                <tr>
                                    <th className="px-6 py-4 font-black text-slate-400 uppercase italic">رقم الرحلة / الوثيقة</th>
                                    <th className="px-6 py-4 font-black text-slate-400 uppercase italic">السائق / الشاحنة</th>
                                    <th className="px-6 py-4 font-black text-slate-400 uppercase italic">المسار</th>
                                    <th className="px-6 py-4 font-black text-slate-400 uppercase italic text-center">الميزانية</th>
                                    <th className="px-6 py-4 font-black text-slate-400 uppercase italic text-center">الديزل الفعلي</th>
                                    <th className="px-6 py-4 font-black text-slate-400 uppercase italic text-center">الترب (الصافي)</th>
                                    <th className="px-6 py-4 font-black text-slate-400 uppercase italic text-center">الحالة</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-50 font-bold">
                                {trips.length > 0 ? trips.map(trip => (
                                    <tr key={trip.id} className="hover:bg-slate-50 transition-colors">
                                        <td className="px-6 py-4">
                                            <div className="flex flex-col">
                                                <span className="text-slate-900">#{trip.trip_no}</span>
                                                <span className="text-[10px] text-slate-400 italic">Waybill: {trip.waybill_no || '--'}</span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex flex-col">
                                                <span className="text-indigo-600">{trip.driver?.name}</span>
                                                <span className="text-[10px] text-slate-400 font-mono tracking-widest">{trip.vehicle?.plate_no}</span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-2 text-xs">
                                                <span>{trip.origin}</span>
                                                <span className="text-slate-300">➜</span>
                                                <span>{trip.destination}</span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-center text-slate-900 font-mono italic">{trip.total_trip_budget?.toLocaleString()}</td>
                                        <td className="px-6 py-4 text-center text-orange-600 font-mono italic">{trip.fuel_cost?.toLocaleString()}</td>
                                        <td className="px-6 py-4 text-center text-emerald-600 font-mono italic bg-emerald-50/30">{trip.driver_commission?.toLocaleString()}</td>
                                        <td className="px-6 py-4 text-center">
                                            <span className={`px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest ${statusColors[trip.status]}`}>
                                                {statusLabels[trip.status]}
                                            </span>
                                        </td>
                                    </tr>
                                )) : (
                                    <tr><td colSpan="7" className="py-20 text-center text-slate-300 font-black italic">لا توجد بيانات مطابقة للبحث...</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
