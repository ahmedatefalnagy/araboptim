import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function Index({ auth, vehicles, drivers, flash }) {
    const [isFormOpen, setIsFormOpen] = useState(false);
    const [editTarget, setEditTarget] = useState(null);
    const [viewMode, setViewMode] = useState('kanban');
    
    const { data, setData, post, processing, reset, errors, delete: destroy } = useForm({
        plate_no: '',
        model: '',
        type: 'head',
        driver_id: '',
        odometer: 0,
        status: 'available',
        oil_change_interval_km: 10000,
        tire_change_interval_km: 50000,
    });

    const openCreate = () => {
        reset();
        setEditTarget(null);
        setIsFormOpen(true);
    };

    const openEdit = (v) => {
        setData({
            plate_no: v.plate_no,
            model: v.model || '',
            type: v.type || 'head',
            driver_id: v.driver_id || '',
            odometer: v.odometer || 0,
            status: v.status || 'available',
            oil_change_interval_km: v.oil_change_interval_km || 10000,
            tire_change_interval_km: v.tire_change_interval_km || 50000,
        });
        setEditTarget(v.id);
        setIsFormOpen(true);
    };

    const submit = (e) => {
        e.preventDefault();
        const url = editTarget ? route('logistics.vehicles.update', editTarget) : route('logistics.vehicles.store');
        post(url, {
            onSuccess: () => {
                setIsFormOpen(false);
                reset();
            }
        });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="إدارة الشاحنات - مركز العمليات" />

            <div className="min-h-screen bg-[#f1f5f9] pb-12" dir="rtl">
                {/* Dashboard Control Bar */}
                <div className="bg-white border-b border-slate-200 sticky top-0 z-30 px-8 py-3 flex items-center justify-between shadow-sm">
                    <div className="flex items-center gap-6">
                        <div className="bg-blue-600 p-2 rounded-lg text-white shadow-lg shadow-blue-100">
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <h1 className="text-xl font-bold text-slate-800 leading-none">إدارة الأسطول</h1>
                            <p className="text-[11px] text-slate-400 font-bold mt-1 uppercase tracking-tighter">Fleet Management Control Center</p>
                        </div>
                        <button onClick={openCreate} className="mr-6 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-xl shadow-blue-100 transition-all flex items-center gap-2">
                            <span>+</span> تسجيل شاحنة جديدة
                        </button>
                    </div>

                    <div className="flex items-center gap-2 bg-slate-100 p-1.5 rounded-xl border border-slate-200">
                        <button onClick={() => setViewMode('kanban')} className={`px-4 py-1.5 rounded-lg text-xs font-bold transition-all ${viewMode === 'kanban' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500'}`}>عرض البطاقات</button>
                        <button onClick={() => setViewMode('list')} className={`px-4 py-1.5 rounded-lg text-xs font-bold transition-all ${viewMode === 'list' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500'}`}>عرض الجدول</button>
                    </div>
                </div>

                <div className="max-w-[1600px] mx-auto px-8 pt-8">
                    {viewMode === 'kanban' ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                            <ColumnHeader title="جاهزة للتشغيل" count={vehicles.filter(v => v.status === 'available').length} color="blue" />
                            <ColumnHeader title="في رحلة نشطة" count={vehicles.filter(v => v.status === 'in_trip').length} color="indigo" />
                            <ColumnHeader title="تحت الصيانة" count={vehicles.filter(v => v.status === 'maintenance').length} color="orange" />
                            <ColumnHeader title="معطلة حالياً" count={vehicles.filter(v => v.status === 'breakdown').length} color="rose" />
                            
                            {vehicles.filter(v => v.status === 'available').map(v => <VCard key={v.id} v={v} onEdit={openEdit} />)}
                            {vehicles.filter(v => v.status === 'in_trip').map(v => <VCard key={v.id} v={v} onEdit={openEdit} />)}
                            {vehicles.filter(v => v.status === 'maintenance').map(v => <VCard key={v.id} v={v} onEdit={openEdit} />)}
                            {vehicles.filter(v => v.status === 'breakdown').map(v => <VCard key={v.id} v={v} onEdit={openEdit} />)}
                        </div>
                    ) : (
                        <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                            <table className="w-full text-right text-sm">
                                <thead>
                                    <tr className="bg-slate-50 border-b border-slate-100 font-bold text-slate-500">
                                        <th className="px-6 py-4">رقم اللوحة</th>
                                        <th className="px-6 py-4">الموديل</th>
                                        <th className="px-6 py-4 text-center">الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {vehicles.map(v => (
                                        <tr key={v.id} onClick={() => openEdit(v)} className="hover:bg-blue-50 cursor-pointer border-b border-slate-50">
                                            <td className="px-6 py-4 font-bold">{v.plate_no}</td>
                                            <td className="px-6 py-4 font-medium text-slate-600">{v.model}</td>
                                            <td className="px-6 py-4 text-center">
                                                <span className={`px-3 py-1 rounded-full text-[10px] font-black uppercase ${v.status === 'available' ? 'bg-blue-50 text-blue-600' : 'bg-slate-100'}`}>{v.status}</span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>

                {/* PRACTICAL UX MODAL - MAXIMUM SCANNABILITY */}
                {isFormOpen && (
                    <div className="fixed inset-0 z-[100] flex items-center justify-center p-6">
                        <div className="absolute inset-0 bg-slate-900/60 backdrop-blur-md animate-in fade-in" onClick={() => setIsFormOpen(false)}></div>
                        <div className="relative w-full max-w-5xl bg-white rounded-3xl shadow-[0_40px_100px_rgba(0,0,0,0.4)] overflow-hidden animate-in zoom-in-95 duration-200 flex flex-col max-h-[95vh] border border-white/20">
                            
                            {/* Modal Header - Fixed */}
                            <div className="bg-[#f8fafc] border-b border-slate-200 px-10 py-6 flex items-center justify-between shrink-0">
                                <div className="flex items-center gap-4">
                                    <div className="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white text-2xl shadow-xl shadow-blue-100">🚛</div>
                                    <div>
                                        <h2 className="text-xl font-black text-slate-800">{editTarget ? 'تعديل بيانات المركبة' : 'تسجيل مركبة في الأسطول'}</h2>
                                        <p className="text-xs font-bold text-slate-400 mt-0.5">يرجى تعبئة كافة الحقول الفنية والتشغيلية بدقة</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3">
                                    <button onClick={() => setIsFormOpen(false)} className="px-6 py-2.5 bg-white border border-slate-200 text-slate-500 rounded-xl text-sm font-black hover:bg-slate-50 transition-all">تجاهل</button>
                                    <button onClick={submit} disabled={processing} className="px-8 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-black shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all active:scale-95">
                                        {processing ? 'جاري الحفظ...' : (editTarget ? 'تحديث البيانات' : 'حفظ الشاحنة')}
                                    </button>
                                </div>
                            </div>

                            {/* Modal Content - Scrollable Grid */}
                            <div className="flex-1 overflow-y-auto p-10 bg-slate-50/30">
                                <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-3 gap-8">
                                    
                                    {/* Primary Info Card */}
                                    <div className="md:col-span-2 space-y-8">
                                        <FormGroup title="التعريف العام والهوية">
                                            <div className="grid grid-cols-2 gap-6">
                                                <UXField label="رقم اللوحة / Plate No" required icon="🆔">
                                                    <input type="text" autoFocus required className="ux-input text-2xl font-black uppercase text-blue-600" value={data.plate_no} onChange={e => setData('plate_no', e.target.value)} placeholder="1234 ABC" />
                                                </UXField>
                                                <UXField label="نوع المركبة" required icon="⚙️">
                                                    <select className="ux-input font-bold" value={data.type} onChange={e => setData('type', e.target.value)}>
                                                        <option value="head">رأس تريلا (Prime Mover)</option>
                                                        <option value="trailer">مقطورة (Trailer)</option>
                                                        <option value="flatbed">سطحة (Flatbed)</option>
                                                        <option value="refrigerated">ثلاجة (Refrigerated)</option>
                                                    </select>
                                                </UXField>
                                                <UXField label="الموديل / الماركة" icon="🏭" className="col-span-2">
                                                    <input type="text" className="ux-input font-bold" value={data.model} onChange={e => setData('model', e.target.value)} placeholder="مثلاً: Mercedes-Benz Actros 2025" />
                                                </UXField>
                                            </div>
                                        </FormGroup>

                                        <FormGroup title="التكليف والتشغيل الحالي">
                                            <div className="grid grid-cols-2 gap-6">
                                                <UXField label="السائق المعين" icon="👤">
                                                    <select className="ux-input text-blue-700 font-black" value={data.driver_id} onChange={e => setData('driver_id', e.target.value)}>
                                                        <option value="">بدون سائق حالياً</option>
                                                        {drivers.map(d => (
                                                            <option key={d.id} value={d.id}>{d.name} ({d.employee_no})</option>
                                                        ))}
                                                    </select>
                                                </UXField>
                                                <UXField label="الحالة التشغيلية" icon="🚦">
                                                    <select className={`ux-input font-black ${data.status === 'available' ? 'text-emerald-600' : 'text-slate-800'}`} value={data.status} onChange={e => setData('status', e.target.value)}>
                                                        <option value="available">جاهزة للعمل فوراً (Available)</option>
                                                        <option value="in_trip">في رحلة حالياً (On Trip)</option>
                                                        <option value="maintenance">تحت الصيانة (Maintenance)</option>
                                                        <option value="breakdown">خارج الخدمة (Down)</option>
                                                    </select>
                                                </UXField>
                                            </div>
                                        </FormGroup>
                                    </div>

                                    {/* Sidebar: Technical & AI Insights */}
                                    <div className="space-y-8">
                                        <FormGroup title="القياسات الفنية (AI)" accent>
                                            <div className="space-y-6">
                                                <UXField label="عداد المسافة (KM)" icon="📈">
                                                    <input type="number" className="ux-input text-xl font-black text-blue-600" value={data.odometer} onChange={e => setData('odometer', e.target.value)} />
                                                </UXField>
                                                <div className="h-[1px] bg-slate-200"></div>
                                                <UXField label="تغيير الزيت كل (KM)" icon="🛢️">
                                                    <input type="number" className="ux-input" value={data.oil_change_interval_km} onChange={e => setData('oil_change_interval_km', e.target.value)} />
                                                </UXField>
                                                <UXField label="تغيير الإطارات كل (KM)" icon="⭕">
                                                    <input type="number" className="ux-input" value={data.tire_change_interval_km} onChange={e => setData('tire_change_interval_km', e.target.value)} />
                                                </UXField>
                                                
                                                <div className="mt-4 p-4 bg-blue-50 rounded-2xl border border-blue-100 flex items-start gap-3">
                                                    <span className="text-xl">🤖</span>
                                                    <p className="text-[10px] font-bold text-blue-700 leading-normal">يقوم النظام بحساب استهلاك الشاحنة للديزل وتوقع أعطال الصيانة بناءً على هذه البيانات.</p>
                                                </div>
                                            </div>
                                        </FormGroup>
                                        
                                        <div className="bg-slate-900 p-8 rounded-[2rem] text-white relative overflow-hidden group shadow-2xl">
                                            <div className="absolute -top-10 -right-10 w-32 h-32 bg-blue-500 rounded-full blur-3xl opacity-20 group-hover:opacity-40 transition-opacity"></div>
                                            <h4 className="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-2">إحصائيات فورية</h4>
                                            <p className="text-2xl font-black tracking-tighter">جاهزية 100%</p>
                                            <p className="text-[9px] font-bold text-slate-400 mt-2">آخر تحديث: الآن</p>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                )}
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .ux-input { width: 100%; border: 1px solid #e2e8f0; border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.95rem; font-weight: 700; color: #1e293b; transition: all 0.2s; background: #fff; }
                .ux-input:focus { outline: none; border-color: #2563eb; ring: 4px; ring-color: rgba(37, 99, 235, 0.1); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.05); }
                select.ux-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: left 1rem center; background-size: 1rem; }
            `}} />
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

function FormGroup({ title, children, accent }) {
    return (
        <div className={`bg-white p-8 rounded-[2rem] border ${accent ? 'border-blue-100' : 'border-slate-100'} shadow-sm relative`}>
            <h3 className="text-sm font-black text-slate-800 mb-8 flex items-center gap-3">
                <span className="w-1.5 h-1.5 bg-blue-600 rounded-full shadow-lg shadow-blue-400"></span>
                {title}
            </h3>
            {children}
        </div>
    );
}

function VCard({ v, onEdit }) {
    return (
        <div onClick={() => onEdit(v)} className="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all cursor-pointer group hover:-translate-y-1">
            <div className="flex justify-between items-start mb-6">
                <div className="bg-slate-900 text-white px-4 py-1.5 rounded-xl font-mono text-lg font-black tracking-widest shadow-lg">{v.plate_no}</div>
                <div className="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-xl group-hover:bg-blue-600 group-hover:text-white transition-all">🚛</div>
            </div>
            <div className="flex justify-between items-center text-[10px] font-black text-slate-400 uppercase mb-2 border-t border-slate-50 pt-3">
                <span>{v.driver?.name || 'غير مخصص'}</span>
                <span className="text-blue-600">{v.odometer?.toLocaleString()} KM</span>
            </div>
        </div>
    );
}

function ColumnHeader({ title, count, color }) {
    return (
        <div className="flex items-center justify-between mb-2 px-2">
            <h3 className="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-3">
                <span className={`w-2 h-2 rounded-full bg-${color}-500 shadow-lg shadow-${color}-200`}></span>
                {title}
            </h3>
            <span className="text-[10px] font-black text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">{count}</span>
        </div>
    );
}
