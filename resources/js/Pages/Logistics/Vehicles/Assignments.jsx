import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Assignments({ auth, vehicles, drivers, flash }) {
    
    const handleAssignmentChange = (vehicleId, driverId) => {
        router.post(route('logistics.vehicles.updateAssignment', vehicleId), {
            driver_id: driverId
        }, {
            preserveScroll: true
        });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="تخصيص الأسطول" />

            <div className="py-12 bg-gray-50 min-h-screen" dir="rtl">
                <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    
                    <div className="mb-8">
                        <h1 className="text-3xl font-black text-slate-900 tracking-tighter italic">شاشة إسناد السائقين للشاحنات</h1>
                        <p className="mt-1 text-gray-500 font-bold italic">تحديد المسؤولية: كل شاحنة من سائقها؟</p>
                    </div>

                    {flash?.success && <div className="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-6 py-4 rounded-2xl font-black shadow-sm">{flash.success}</div>}

                    <div className="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden">
                        <table className="w-full text-right text-sm">
                            <thead className="bg-[#1e1b4b] text-white">
                                <tr>
                                    <th className="px-8 py-6 font-black uppercase tracking-widest italic">الشاحنة (Tirila)</th>
                                    <th className="px-8 py-6 font-black uppercase tracking-widest italic text-center">السائق المخصص حالياً</th>
                                    <th className="px-8 py-6 font-black uppercase tracking-widest italic text-center">تغيير / تعيين السائق</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-50">
                                {vehicles.map(vehicle => (
                                    <tr key={vehicle.id} className="hover:bg-indigo-50/30 transition-colors">
                                        <td className="px-8 py-6">
                                            <div className="flex items-center gap-4">
                                                <div className="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-xl">🚛</div>
                                                <div>
                                                    <p className="text-xl font-black font-mono tracking-widest text-slate-900 leading-none">{vehicle.plate_no}</p>
                                                    <p className="text-[10px] font-bold text-slate-400 mt-1 uppercase">{vehicle.model}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-8 py-6 text-center">
                                            {vehicle.driver ? (
                                                <div className="inline-flex flex-col items-center">
                                                    <span className="text-sm font-black text-indigo-700">{vehicle.driver.name}</span>
                                                    <span className="text-[10px] font-bold text-indigo-300 uppercase tracking-tighter italic">ID: {vehicle.driver.employee_no}</span>
                                                </div>
                                            ) : (
                                                <span className="text-xs font-black text-slate-300 italic uppercase">-- لم يتم الربط بعد --</span>
                                            )}
                                        </td>
                                        <td className="px-8 py-6 text-center">
                                            <select 
                                                className="rounded-xl border-slate-100 bg-slate-50 font-black text-xs focus:ring-indigo-500 focus:border-indigo-500 w-full max-w-xs"
                                                value={vehicle.driver_id || ''}
                                                onChange={e => handleAssignmentChange(vehicle.id, e.target.value)}
                                            >
                                                <option value="">-- فك الارتباط (بدون سائق) --</option>
                                                {drivers.map(d => (
                                                    <option key={d.id} value={d.id}>{d.name} ({d.employee_no})</option>
                                                ))}
                                            </select>
                                        </td>
                                    </tr>
                                ))}
                                {vehicles.length === 0 && (
                                    <tr><td colSpan="3" className="py-20 text-center text-slate-300 font-black italic">لا توجد مركبات مسجلة في الأسطول...</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    
                    <div className="mt-10 p-6 bg-indigo-50 rounded-[2rem] border border-indigo-100 italic">
                        <div className="flex items-center gap-4">
                            <div className="text-2xl opacity-50">💡</div>
                            <p className="text-xs font-bold text-indigo-900/60 leading-relaxed">
                                أي سائق يتم ربطه هنا سيظهر اسمه تلقائياً وتُملأ بيانات شاحنته عند فتح "رحلة جديدة".
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </AuthenticatedLayout>
    );
}
