import { Head, useForm, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function Requests({ auth, requests, vehicles, flash }) {
    const [showModal, setShowModal] = useState(false);

    const { data, setData, post, processing, reset } = useForm({
        vehicle_id: '',
        category: 'mechanical',
        issue_description: '',
        estimated_cost: 0,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('logistics.workshop.requests.store'), {
            onSuccess: () => {
                setShowModal(false);
                reset();
            }
        });
    };

    const updateStatus = (id, status) => {
        router.post(route('logistics.workshop.requests.updateStatus', id), {
            status: status
        }, { preserveScroll: true });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="طلبات الصيانة والورشة" />

            <div className="py-12 bg-gray-50 min-h-screen" dir="rtl">
                <div className="max-w-7xl mx-auto px-4 sm:px-6">
                    
                    <div className="mb-10 flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-black text-slate-900 tracking-tighter italic">مركز طلبات الصيانة (Workshop Hub)</h1>
                            <p className="mt-1 text-slate-500 font-bold">إدارة البلاغات الفنية، اعتماد الإصلاحات، ومتابعة التكاليف</p>
                        </div>
                        <button 
                            onClick={() => setShowModal(true)}
                            className="bg-orange-500 text-white px-8 py-4 rounded-2xl font-black shadow-xl shadow-orange-100 hover:bg-orange-600 transition-all"
                        >
                            ➕ تقديم طلب صيانة جديد
                        </button>
                    </div>

                    <div className="space-y-6">
                        {requests.map(req => (
                            <div key={req.id} className="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden flex flex-col md:flex-row">
                                <div className={`p-8 md:w-64 flex flex-col items-center justify-center text-center border-l ${getStatusBorder(req.status)}`}>
                                    <span className="text-[10px] font-black uppercase text-slate-400 mb-2">رقم الطلب #{req.id}</span>
                                    <div className="w-20 h-20 bg-slate-50 rounded-2xl flex items-center justify-center text-3xl mb-4">🚛</div>
                                    <h4 className="text-xl font-black text-slate-900 leading-tight">{req.vehicle?.plate_no}</h4>
                                    <p className="text-xs font-bold text-slate-400 mt-1 italic">{req.driver?.name || 'مكتب العمليات'}</p>
                                </div>
                                
                                <div className="p-8 flex-1">
                                    <div className="flex justify-between items-start mb-6">
                                        <div>
                                            <span className="px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-[10px] font-black uppercase tracking-widest italic">{getCategoryLabel(req.category)}</span>
                                            <h3 className="text-lg font-black text-slate-800 mt-3">{req.issue_description}</h3>
                                        </div>
                                        <div className="text-left">
                                            <span className={`px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest ${getStatusColor(req.status)}`}>
                                                {getStatusLabel(req.status)}
                                            </span>
                                            {req.estimated_cost > 0 && <p className="mt-2 text-lg font-black text-slate-900">{req.estimated_cost} <span className="text-[10px] text-slate-400">SAR</span></p>}
                                        </div>
                                    </div>
                                    
                                    <div className="flex items-center gap-4 pt-6 border-t border-slate-50 mt-auto">
                                        {req.status === 'pending' && (
                                            <>
                                                <button onClick={() => updateStatus(req.id, 'approved')} className="text-xs font-black bg-emerald-500 text-white px-6 py-3 rounded-xl hover:bg-emerald-600 transition-all">اعتماد الطلب ✅</button>
                                                <button onClick={() => updateStatus(req.id, 'rejected')} className="text-xs font-black bg-slate-100 text-slate-400 px-6 py-3 rounded-xl hover:bg-slate-200 transition-all">رفض</button>
                                            </>
                                        )}
                                        {req.status === 'approved' && (
                                            <button onClick={() => updateStatus(req.id, 'in_progress')} className="text-xs font-black bg-indigo-600 text-white px-6 py-3 rounded-xl hover:bg-indigo-700 transition-all">بدء العمل بالورشة 🔧</button>
                                        )}
                                        {req.status === 'in_progress' && (
                                            <button onClick={() => updateStatus(req.id, 'completed')} className="text-xs font-black bg-emerald-600 text-white px-6 py-3 rounded-xl hover:bg-emerald-700 transition-all">إغلاق وتسليم المركبة 🏁</button>
                                        )}
                                        <div className="mr-auto text-[10px] font-bold text-slate-300 italic">تم النشر في: {new Date(req.created_at).toLocaleString('en-US')}</div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Addition Modal */}
            {showModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                    <div className="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in duration-200">
                        <div className="p-8">
                            <h3 className="text-2xl font-black text-slate-900 mb-6 italic tracking-tight border-b pb-4 text-center">تقديم تقرير عطل فني</h3>
                            <form onSubmit={submit} className="space-y-6">
                                <div>
                                    <label className="block text-[10px] font-black text-slate-400 uppercase mb-2">رقم الشاحنة</label>
                                    <select required className="w-full rounded-2xl border-slate-100 bg-slate-50 font-black" value={data.vehicle_id} onChange={e => setData('vehicle_id', e.target.value)}>
                                        <option value="">اختر الشاحنة المعطلة</option>
                                        {vehicles.map(v => <option key={v.id} value={v.id}>{v.plate_no}</option>)}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-[10px] font-black text-slate-400 uppercase mb-2">تصنيف العطل</label>
                                    <select required className="w-full rounded-2xl border-slate-100 bg-slate-50 font-black" value={data.category} onChange={e => setData('category', e.target.value)}>
                                        <option value="mechanical">ميكانيكا (General Mechanical)</option>
                                        <option value="oil_filter">تغيير زيت وفلاتر (Oil Change)</option>
                                        <option value="tires">صيانة كفرات (Tires)</option>
                                        <option value="electrical">كهرباء (Electrical)</option>
                                        <option value="others">أخرى</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-[10px] font-black text-slate-400 uppercase mb-2">وصف العطل / الخدمة المطلوبة</label>
                                    <textarea required rows="4" className="w-full rounded-2xl border-slate-100 bg-slate-50 font-bold" placeholder="اشرح المشكلة بالتفصيل..." value={data.issue_description} onChange={e => setData('issue_description', e.target.value)}></textarea>
                                </div>
                                <div className="flex gap-4 pt-6">
                                    <button type="button" onClick={() => setShowModal(false)} className="flex-1 bg-slate-100 text-slate-400 font-black py-4 rounded-2xl">إلغاء</button>
                                    <button type="submit" disabled={processing} className="flex-[2] bg-orange-500 text-white font-black py-4 rounded-2xl shadow-xl shadow-orange-100">إرسال التقرير للورشة</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}

        </AuthenticatedLayout>
    );
}

const getCategoryLabel = (cat) => {
    const labels = {
        oil_filter: 'زيت وفلاتر',
        tires: 'صيانة كفرات',
        mechanical: 'ميكانيكا',
        electrical: 'كهرباء',
        others: 'أخرى'
    };
    return labels[cat] || cat;
}

const getStatusLabel = (status) => {
    const labels = {
        pending: 'انتظار الموافقة ⏳',
        approved: 'معتمد للإصلاح ✅',
        in_progress: 'تحت التنفيذ 🔧',
        completed: 'تم الإصلاح 🏁',
        rejected: 'مرفوض ❌'
    };
    return labels[status];
}

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-orange-50 text-orange-600',
        approved: 'bg-emerald-50 text-emerald-600',
        in_progress: 'bg-blue-50 text-blue-600',
        completed: 'bg-indigo-50 text-indigo-600',
        rejected: 'bg-slate-100 text-slate-400'
    };
    return colors[status];
}

const getStatusBorder = (status) => {
    const borders = {
        pending: 'border-orange-500',
        approved: 'border-emerald-500',
        in_progress: 'border-blue-500',
        completed: 'border-indigo-600',
        rejected: 'border-slate-300'
    };
    return borders[status] || 'border-slate-100';
}
