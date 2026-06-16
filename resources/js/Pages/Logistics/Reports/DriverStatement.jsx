import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function DriverStatement({ auth, drivers, reportData, filters }) {
    const [employeeId, setEmployeeId] = useState(filters.employee_id || '');
    const [month, setMonth] = useState(filters.month || '');

    const runReport = () => {
        router.get(route('logistics.reports.driver-statement'), { employee_id: employeeId, month });
    };

    const handlePrint = () => {
        window.print();
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="كشف حساب السائق" />

            <div className="py-12 bg-gray-50 min-h-screen" dir="rtl">
                <style>{`
                    @media print {
                        .no-print { display: none !important; }
                        .print-only { display: block !important; }
                        body { background: white !important; }
                        .document-paper { 
                            box-shadow: none !important; 
                            border: none !important; 
                            padding: 0 !important;
                            margin: 0 !important;
                        }
                    }
                `}</style>
                
                <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    
                    {/* Header & Filters (No-Print) */}
                    <div className="mb-8 no-print flex flex-col md:flex-row justify-between items-center bg-white p-8 rounded-3xl shadow-sm border border-gray-100 gap-6">
                        <div>
                            <h1 className="text-3xl font-black text-slate-800 italic">كشف حساب السائق (الراتب + التريب)</h1>
                            <p className="mt-1 text-gray-500 font-bold">متابعة المستحقات والرحلات والحوالات المالية</p>
                        </div>
                        <div className="flex flex-wrap items-center gap-4">
                            <select 
                                className="rounded-2xl border-gray-100 bg-gray-50 font-black focus:ring-indigo-500 min-w-[200px]"
                                value={employeeId}
                                onChange={e => setEmployeeId(e.target.value)}
                            >
                                <option value="">-- اختر السائق --</option>
                                {drivers.map(d => (
                                    <option key={d.id} value={d.id}>{d.employee_no} - {d.name}</option>
                                ))}
                            </select>
                            <input 
                                type="month"
                                className="rounded-2xl border-gray-100 bg-gray-50 font-black focus:ring-indigo-500"
                                value={month}
                                onChange={e => setMonth(e.target.value)}
                            />
                            <button 
                                onClick={runReport}
                                className="bg-indigo-900 text-white px-8 py-3 rounded-2xl font-black hover:bg-black transition-all shadow-xl shadow-indigo-100"
                            >
                                عرض التقرير
                            </button>
                        </div>
                    </div>

                    {reportData ? (
                        <div className="document-paper bg-white rounded-[2.5rem] shadow-2xl p-12 border border-slate-100 relative overflow-hidden animate-in fade-in duration-500">
                            
                            {/* Watermark/Background Decoration */}
                            <div className="absolute -top-20 -left-20 text-[200px] font-black text-gray-100/30 -rotate-12 select-none z-0">STATEMENT</div>
                            
                            {/* Document Header */}
                            <div className="relative z-10 flex justify-between items-start border-b-4 border-slate-900 pb-10 mb-12">
                                <div className="space-y-2">
                                    <h2 className="text-4xl font-black text-slate-900 tracking-tighter uppercase italic py-2 bg-slate-900 text-white px-6 rounded-xl inline-block mb-4">كشف تفصيلي</h2>
                                    <p className="text-2xl font-black text-slate-800">{reportData.employee.name}</p>
                                    <p className="text-lg font-bold text-slate-500">{reportData.employee.job_title} | #{reportData.employee.employee_no}</p>
                                    <p className="text-sm font-black text-indigo-600 bg-indigo-50 inline-block px-4 py-1 rounded-full">{month} / فترة التقرير</p>
                                </div>
                                <div className="text-left space-y-2">
                                    <img src="/logo.png" alt="Company Logo" className="h-16 ml-auto mb-4 opacity-70 grayscale" />
                                    <p className="font-black text-slate-900 italic">مؤسسة عرب أوبتيما للخدمات اللوجستية</p>
                                    <button onClick={handlePrint} className="no-print bg-slate-100 px-4 py-2 rounded-xl text-xs font-black uppercase text-slate-500 hover:bg-slate-200">Print / Export PDF</button>
                                </div>
                            </div>

                            {/* Financial Summary Cards */}
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12 relative z-10">
                                <div className="bg-slate-50 p-6 rounded-[2rem] border-2 border-slate-100">
                                    <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 italic">الراتب الأساسي</p>
                                    <p className="text-2xl font-black font-mono text-slate-800">{parseFloat(reportData.basic_salary).toFixed(2)}</p>
                                </div>
                                <div className="bg-blue-50 p-6 rounded-[2rem] border-2 border-blue-100">
                                    <p className="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-2 italic">إجمالي عمولة الرحلات (Tirip)</p>
                                    <p className="text-2xl font-black font-mono text-blue-800">+{parseFloat(reportData.total_commission).toFixed(2)}</p>
                                </div>
                                <div className="bg-red-50 p-6 rounded-[2rem] border-2 border-red-100">
                                    <p className="text-[10px] font-black text-red-400 uppercase tracking-widest mb-2 italic">خصم السلف والحوالات</p>
                                    <p className="text-2xl font-black font-mono text-red-800">-{parseFloat(reportData.total_advances + reportData.deductions).toFixed(2)}</p>
                                </div>
                                <div className="bg-emerald-900 text-white p-6 rounded-[2rem] shadow-xl shadow-emerald-100 border-2 border-emerald-800 group hover:scale-[1.02] transition-transform">
                                    <p className="text-[10px] font-black text-emerald-300 uppercase tracking-widest mb-2 italic">الصافي المستحق للموظف</p>
                                    <p className="text-3xl font-black font-mono">{parseFloat(reportData.net_total).toFixed(2)}</p>
                                    <span className="text-[10px] font-bold text-emerald-200 block mt-1 tracking-widest uppercase">SAR TOTAL DUET</span>
                                </div>
                            </div>

                            {/* Trips Detail Table */}
                            <div className="relative z-10 space-y-6 mb-12">
                                <h3 className="text-xl font-black text-slate-900 flex items-center gap-3 italic">
                                    <span className="w-8 h-1 bg-indigo-600 rounded-full"></span>
                                    تفاصيل الرحلات والعمولات (Tirips)
                                </h3>
                                <div className="overflow-hidden border border-slate-100 rounded-2xl bg-white shadow-sm">
                                    <table className="w-full text-right">
                                        <thead className="bg-slate-900 text-white">
                                            <tr>
                                                <th className="px-6 py-4 text-xs font-black uppercase">الرحلة</th>
                                                <th className="px-6 py-4 text-xs font-black uppercase">التاريخ</th>
                                                <th className="px-6 py-4 text-xs font-black uppercase">المسار (من/إلى)</th>
                                                <th className="px-6 py-4 text-xs font-black uppercase">الشاحنة</th>
                                                <th className="px-6 py-4 text-xs font-black uppercase text-left italic">العمولة (Tirip)</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-50">
                                            {reportData.trips.length > 0 ? reportData.trips.map(trip => (
                                                <tr key={trip.id} className="hover:bg-indigo-50/30 transition-colors">
                                                    <td className="px-6 py-4 font-black font-mono text-indigo-900">#{trip.trip_no}</td>
                                                    <td className="px-6 py-4 text-xs font-bold text-gray-400">{trip.actual_arrival}</td>
                                                    <td className="px-6 py-4">
                                                        <span className="font-black text-slate-800 text-sm">{trip.origin}</span>
                                                        <span className="mx-2 text-indigo-300">➜</span>
                                                        <span className="font-black text-slate-800 text-sm">{trip.destination}</span>
                                                    </td>
                                                    <td className="px-6 py-4 font-black font-mono text-slate-600">{trip.vehicle?.plate_no}</td>
                                                    <td className="px-6 py-4 font-black font-mono text-blue-700 text-left underline">+{parseFloat(trip.driver_commission).toFixed(2)}</td>
                                                </tr>
                                            )) : (
                                                <tr><td colSpan="5" className="px-6 py-8 text-center text-slate-400 font-bold italic">لا توجد رحلات مسجلة لهذا الشهر</td></tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {/* Accounting Warning / Notes */}
                            <div className="grid grid-cols-2 gap-12 mt-20 relative z-10">
                                <div className="space-y-4">
                                    <p className="text-[10px] font-black text-slate-400 underline">الموظف / Employee Sign</p>
                                    <div className="h-16 bg-slate-50/50 border-b-2 border-dashed border-slate-200 rounded-t-2xl"></div>
                                </div>
                                <div className="space-y-4">
                                    <p className="text-[10px] font-black text-slate-400 underline">إدارة الحسابات / Accounting Approval</p>
                                    <div className="h-16 bg-slate-50/50 border-b-2 border-dashed border-slate-200 rounded-t-2xl"></div>
                                </div>
                            </div>

                        </div>
                    ) : (
                        <div className="bg-white p-20 rounded-[3rem] text-center border-2 border-dashed border-slate-200">
                             <div className="text-8xl mb-6 grayscale opacity-20">📊</div>
                             <h3 className="text-2xl font-black text-slate-300 uppercase tracking-tighter italic">اختر السائق والفترة الزمنية لعرض كشف الحساب</h3>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
