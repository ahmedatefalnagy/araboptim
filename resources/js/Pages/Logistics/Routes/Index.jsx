import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function Index({ auth, routes, flash }) {
    const [isFormOpen, setIsFormOpen] = useState(false);
    const [editTarget, setEditTarget] = useState(null);
    
    const { data, setData, post, processing, reset, errors, transform, delete: destroy } = useForm({
        name: '',
        origin: '',
        destination: '',
        distance_km: '',
        standard_diesel_budget: '',
        standard_driver_commission: '',
        is_active: true,
    });

    const openCreate = () => {
        reset();
        setEditTarget(null);
        setIsFormOpen(true);
    };

    const openEdit = (r) => {
        setData({
            name: r.name,
            origin: r.origin,
            destination: r.destination,
            distance_km: r.distance_km || '',
            standard_diesel_budget: r.standard_diesel_budget || '',
            standard_driver_commission: r.standard_driver_commission || '',
            is_active: r.is_active,
        });
        setEditTarget(r.id);
        setIsFormOpen(true);
    };

    const submit = (e) => {
        e.preventDefault();
        const url = editTarget ? route('logistics.routes.update', editTarget) : route('logistics.routes.store');
        transform((data) => ({
            ...data,
            standard_budget: (parseFloat(data.standard_diesel_budget) || 0) + (parseFloat(data.standard_driver_commission) || 0),
        }));
        const action = editTarget ? put : post;
        action(url, {
            onSuccess: () => {
                setIsFormOpen(false);
                reset();
            }
        });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="إدارة المسارات - مركز العمليات" />

            <div className="min-h-screen bg-[#f8fafc] pb-12" dir="rtl">
                {/* Control Panel */}
                <div className="bg-white border-b border-slate-200 sticky top-0 z-30 px-8 py-4 flex items-center justify-between shadow-sm">
                    <div className="flex items-center gap-4">
                        <div className="bg-indigo-600 p-2 rounded-xl text-white shadow-md">
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V6a2 2 0 011.553-1.943l7-1a2 2 0 011.894.478L18 8.243l4.447-2.224A2 2 0 0121 8.243v9.382a2 2 0 01-1.553 1.943l-7 1a2 2 0 01-1.894-.478L9 20z" /></svg>
                        </div>
                        <div>
                            <h1 className="text-lg font-black text-slate-800 leading-none">تكويد المسارات</h1>
                            <p className="text-[10px] text-slate-400 font-bold mt-1 uppercase tracking-wider">Route Planning & Standardization</p>
                        </div>
                    </div>
                    <button onClick={openCreate} className="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-md transition-all">
                        + تعريف مسار جديد
                    </button>
                </div>

                <div className="max-w-[1400px] mx-auto p-8 space-y-6">
                    {flash?.success && (
                        <div className="bg-emerald-50 border border-emerald-100 p-4 rounded-xl text-emerald-800 font-bold text-sm">
                            {flash.success}
                        </div>
                    )}

                    {isFormOpen && (
                        <div className="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                            <h3 className="text-sm font-bold text-slate-800 mb-6 border-b pb-2">
                                {editTarget ? 'تعديل بيانات المسار' : 'تعريف مسار تشغيلي جديد'}
                            </h3>
                            <form onSubmit={submit}>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                    <div className="md:col-span-3">
                                        <label className="text-xs font-bold text-slate-600 mb-1.5 block">اسم المسار *</label>
                                        <input type="text" autoFocus required className="w-full rounded-xl border-slate-200 text-sm font-bold text-indigo-700 focus:ring-indigo-500 focus:border-indigo-500" value={data.name} onChange={e => setData('name', e.target.value)} placeholder="مثلاً: الرياض - جدة (تريلا 40 طن)" />
                                    </div>
                                    <div>
                                        <label className="text-xs font-bold text-slate-600 mb-1.5 block">نقطة التحميل (Origin) *</label>
                                        <input type="text" required className="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" value={data.origin} onChange={e => setData('origin', e.target.value)} placeholder="الرياض" />
                                    </div>
                                    <div>
                                        <label className="text-xs font-bold text-slate-600 mb-1.5 block">نقطة التفريغ (Destination) *</label>
                                        <input type="text" required className="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" value={data.destination} onChange={e => setData('destination', e.target.value)} placeholder="جدة" />
                                    </div>
                                    <div>
                                        <label className="text-xs font-bold text-slate-600 mb-1.5 block">المسافة المقطوعة (KM)</label>
                                        <input type="number" className="w-full rounded-xl border-slate-200 text-sm font-mono focus:ring-indigo-500 focus:border-indigo-500" value={data.distance_km} onChange={e => setData('distance_km', e.target.value)} placeholder="مثال: 950" />
                                    </div>
                                    <div>
                                        <label className="text-xs font-bold text-orange-600 mb-1.5 block">ميزانية الديزل القياسية (SAR)</label>
                                        <input type="number" step="0.01" className="w-full rounded-xl border-slate-200 text-sm font-mono text-orange-600 focus:ring-indigo-500 focus:border-indigo-500" value={data.standard_diesel_budget} onChange={e => setData('standard_diesel_budget', e.target.value)} placeholder="0.00" />
                                    </div>
                                    <div>
                                        <label className="text-xs font-bold text-emerald-600 mb-1.5 block">صافي السائق المعتمد (SAR)</label>
                                        <input type="number" step="0.01" className="w-full rounded-xl border-slate-200 text-sm font-mono text-emerald-600 focus:ring-indigo-500 focus:border-indigo-500" value={data.standard_driver_commission} onChange={e => setData('standard_driver_commission', e.target.value)} placeholder="0.00" />
                                    </div>
                                    <div className="md:col-span-3 bg-slate-900 rounded-xl p-4 text-white flex justify-between items-center shadow-inner">
                                        <div>
                                            <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">إجمالي التكلفة التقديرية (ديزل + صافي السائق)</p>
                                            <p className="text-2xl font-black text-emerald-400">
                                                {((parseFloat(data.standard_diesel_budget) || 0) + (parseFloat(data.standard_driver_commission) || 0)).toFixed(2)} <span className="text-xs font-normal text-slate-300">ر.س</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div className="flex gap-3 justify-end border-t pt-4">
                                    <button type="button" onClick={() => { setIsFormOpen(false); reset(); }} className="px-5 py-2 bg-slate-100 text-slate-600 rounded-lg font-bold text-sm hover:bg-slate-200 transition-all">إلغاء</button>
                                    <button type="submit" disabled={processing} className="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 disabled:opacity-50 text-sm shadow-md transition-all">
                                        {processing ? 'جاري الحفظ...' : 'حفظ المسار'}
                                    </button>
                                </div>
                                {Object.keys(errors).length > 0 && (
                                    <div className="mt-4 bg-rose-50 border border-rose-200 rounded-xl p-4">
                                        <ul className="text-xs text-rose-700 font-bold space-y-1 list-disc list-inside">
                                            {Object.values(errors).map((err, idx) => (
                                                <li key={idx}>{err}</li>
                                            ))}
                                        </ul>
                                    </div>
                                )}
                            </form>
                        </div>
                    )}

                    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <table className="w-full text-right text-sm">
                            <thead className="bg-slate-50 border-b border-slate-100">
                                <tr className="text-slate-400 font-black text-[10px] uppercase tracking-widest">
                                    <th className="px-8 py-5">المسار (Origin ↔ Dest)</th>
                                    <th className="px-8 py-5">المسافة القياسية</th>
                                    <th className="px-8 py-5">ميزانية الديزل</th>
                                    <th className="px-8 py-5">صافي السائق</th>
                                    <th className="px-8 py-5 text-center">الحالة</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-50 font-bold">
                                {routes.map(r => (
                                    <tr key={r.id} onClick={() => openEdit(r)} className="hover:bg-slate-50 cursor-pointer transition-colors group">
                                        <td className="px-8 py-5">
                                            <div className="flex items-center gap-3">
                                                <div className="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600">🛣️</div>
                                                <div>
                                                    <p className="text-slate-900">{r.name}</p>
                                                    <p className="text-[10px] text-slate-400 font-medium italic">{r.origin} ↔ {r.destination}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-8 py-5 font-mono text-slate-600 italic">{r.distance_km} KM</td>
                                        <td className="px-8 py-5 text-orange-600 font-black tracking-tighter">{r.standard_diesel_budget?.toLocaleString()}</td>
                                        <td className="px-8 py-5 text-emerald-600 font-black tracking-tighter">{r.standard_driver_commission?.toLocaleString()} SAR</td>
                                        <td className="px-8 py-5 text-center">
                                            <span className={`px-3 py-1 rounded-full text-[10px] font-black border ${r.is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600'}`}>
                                                {r.is_active ? 'نشط' : 'متوقف'}
                                            </span>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function UXField({ label, children, required, icon, className = "" }) {
    return (
        <div className={`flex flex-col gap-2 ${className}`}>
            <label className="text-[11px] font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                <span className="opacity-70">{icon}</span>
                {label} {required && <span className="text-rose-500">*</span>}
            </label>
            {children}
        </div>
    );
}

function FormGroup({ title, children }) {
    return (
        <div className="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm relative">
            <h3 className="text-sm font-black text-slate-800 mb-8 flex items-center gap-3">
                <span className="w-1.5 h-1.5 bg-indigo-500 rounded-full shadow-lg shadow-indigo-400"></span>
                {title}
            </h3>
            {children}
        </div>
    );
}
