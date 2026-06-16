import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function Show({ auth, trip, total_diesel, net_trip, flash }) {
    const [eventModal, setEventModal] = useState(false);
    const [dieselModal, setDieselModal] = useState(false);
    
    // Status update form
    const statusForm = useForm({
        status: trip.status,
        actual_arrival: trip.actual_arrival || '',
        actual_loading_start: trip.actual_loading_start || '',
        actual_loading_end: trip.actual_loading_end || '',
        actual_unloading_start: trip.actual_unloading_start || '',
        actual_unloading_end: trip.actual_unloading_end || '',
        end_km: trip.end_km || 0,
        fuel_amount: trip.fuel_amount || 0,
        fuel_cost: trip.fuel_cost || 0,
        total_trip_budget: trip.total_trip_budget || 0,
    });

    // New event form
    const eventForm = useForm({
        reason: '',
        location: '',
        start_time: new Date().toISOString().slice(0, 16),
        notes: '',
    });

    const updateStatus = (e) => {
        e.preventDefault();
        statusForm.post(route('logistics.trips.status', trip.id));
    };

    const submitEvent = (e) => {
        e.preventDefault();
        eventForm.post(route('logistics.trips.stops.add', trip.id), {
            onSuccess: () => {
                setEventModal(false);
                eventForm.reset();
            }
        });
    };

    const dieselForm = useForm({
        amount: '',
        location: '',
        notes: '',
    });

    const submitDiesel = (e) => {
        e.preventDefault();
        dieselForm.post(route('logistics.trips.diesel', trip.id), {
            onSuccess: () => {
                setDieselModal(false);
                dieselForm.reset();
            }
        });
    };

    const calculatedCommission = statusForm.data.total_trip_budget - (total_diesel || 0);

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`دورة حياة الرحلة ${trip.trip_no}`} />

            <div className="min-h-screen bg-[#f8fafc] pb-12" dir="rtl">
                {/* Odoo Style Top Bar */}
                <div className="bg-white border-b border-slate-200 sticky top-0 z-30 px-8 py-4 flex items-center justify-between shadow-sm">
                    <div className="flex items-center gap-4">
                        <Link href={route('logistics.trips.index')} className="text-slate-400 hover:text-indigo-600 transition-colors">الرحلات</Link>
                        <span className="text-slate-300">/</span>
                        <h1 className="text-lg font-bold text-slate-800">{trip.trip_no}</h1>
                    </div>
                    <div className="flex items-center gap-3">
                        <button onClick={() => setDieselModal(true)} className="px-6 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-bold shadow-sm hover:bg-slate-50 transition-all">
                            ⛽ تسجيل ديزل
                        </button>
                        <button onClick={() => setEventModal(true)} className="px-6 py-2 bg-orange-600 text-white rounded-lg text-sm font-bold shadow-md hover:bg-orange-700 transition-all">
                            🛑 تسجيل توقف
                        </button>
                    </div>
                </div>

                <div className="max-w-[1500px] mx-auto p-8">
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        
                        {/* Main Lifecycle Timeline */}
                        <div className="lg:col-span-8 space-y-8">
                            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-10">
                                <h2 className="text-xl font-black text-slate-800 mb-10 flex items-center gap-4 italic tracking-tighter">
                                    <span className="w-2 h-8 bg-indigo-600 rounded-full"></span>
                                    دورة حياة الرحلة (Trip Lifecycle)
                                </h2>

                                <div className="relative border-r-2 border-slate-100 pr-12 mr-6 space-y-12">
                                    {/* 1. التحميل */}
                                    <TimelineItem 
                                        icon="🏗️" label="مرحلة التحميل" 
                                        time={trip.actual_loading_start || trip.etd} 
                                        active={trip.status === 'loading'}
                                        completed={['transit', 'at_destination', 'completed'].includes(trip.status)}
                                    >
                                        <div className="text-xs font-bold text-slate-500">تم الاستلام من: {trip.loading_site || trip.end_customer_name}</div>
                                    </TimelineItem>

                                    {/* 2. فتح الرحلة */}
                                    <TimelineItem 
                                        icon="📝" label="فتح الرحلة وإدخال البيانات" 
                                        time={trip.created_at} 
                                        completed 
                                    >
                                        <div className="flex gap-4 mt-2">
                                            <Badge label={`الميزانية: ${trip.total_trip_budget}`} color="bg-indigo-50 text-indigo-600" />
                                            <Badge label={`الديزل الأولي: ${trip.initial_diesel_amount}`} color="bg-orange-50 text-orange-600" />
                                        </div>
                                    </TimelineItem>

                                    {/* 3. التحرك */}
                                    <TimelineItem 
                                        icon="🚚" label="التحرك (بداية الرحلة)" 
                                        time={trip.actual_loading_end} 
                                        active={trip.status === 'transit'}
                                        completed={['at_destination', 'completed'].includes(trip.status)}
                                    >
                                        <div className="text-xs font-black text-indigo-600 uppercase">الوجهة: {trip.destination} ({trip.distance_km} كم)</div>
                                    </TimelineItem>

                                    {/* 4. التوقفات (ديناميكية) */}
                                    {trip.stops?.map((stop, i) => (
                                        <TimelineItem 
                                            key={i} icon="🛑" label={`توقف: ${stop.reason}`} 
                                            time={stop.start_time} 
                                            color="border-orange-500"
                                            completed
                                        >
                                            <div className="bg-orange-50 border border-orange-100 p-4 rounded-xl mt-2">
                                                <p className="text-xs font-bold text-orange-800">الموقع: {stop.location || 'غير محدد'}</p>
                                                {stop.notes && <p className="text-[10px] text-orange-600 italic mt-1">ملاحظة: {stop.notes}</p>}
                                                <p className="text-[10px] font-black text-orange-400 mt-2">المغادرة: {stop.end_time || 'جاري التوقف...'}</p>
                                            </div>
                                        </TimelineItem>
                                    ))}

                                    {/* 5. التفريغ */}
                                    <TimelineItem 
                                        icon="📍" label="الوصول والتفريغ" 
                                        time={trip.actual_unloading_start} 
                                        active={trip.status === 'at_destination'}
                                        completed={trip.status === 'completed'}
                                    >
                                        <div className="text-xs font-bold text-slate-500">موقع التفريغ: {trip.discharge_site || trip.destination}</div>
                                    </TimelineItem>

                                    {/* 6. إقفال الرحلة */}
                                    <TimelineItem 
                                        icon="🏁" label="إقفال الرحلة (مكتملة)" 
                                        time={trip.actual_arrival} 
                                        active={trip.status === 'completed'}
                                        color="border-emerald-500"
                                    >
                                        {trip.status === 'completed' && (
                                            <div className="bg-emerald-50 border border-emerald-100 p-4 rounded-xl mt-2 flex justify-between items-center">
                                                <div>
                                                    <p className="text-xs font-black text-emerald-800 tracking-tighter italic">تم تسليم الحمولة وإقفال العهدة</p>
                                                    <p className="text-[10px] text-emerald-600 font-bold">المستحق النهائي للسائق: {net_trip} SAR</p>
                                                </div>
                                                <div className="text-2xl">🏆</div>
                                            </div>
                                        )}
                                    </TimelineItem>
                                </div>
                            </div>
                        </div>

                        {/* Sidebar Analytics */}
                        <div className="lg:col-span-4 space-y-8">
                            {/* Financial Summary Card */}
                            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                                <div className="bg-indigo-900 p-6 text-white">
                                    <p className="text-[10px] font-black uppercase tracking-widest text-indigo-300">مستحقات السائق النهائية</p>
                                    <h4 className="text-3xl font-black italic tracking-tighter mt-1">{net_trip} SAR</h4>
                                </div>
                                <div className="p-6 space-y-4">
                                    <div className="flex justify-between text-xs font-bold">
                                        <span className="text-slate-400">سعر الرحلة الإجمالي</span>
                                        <span className="text-slate-800">{trip.total_trip_budget}</span>
                                    </div>
                                    <div className="flex justify-between text-xs font-bold">
                                        <span className="text-slate-400">إجمالي الديزل المسحوب</span>
                                        <span className="text-orange-600">-{total_diesel}</span>
                                    </div>
                                    <div className="pt-4 border-t border-slate-100 flex justify-between items-center">
                                        <span className="text-[10px] font-black text-indigo-600 uppercase">الصافي للسائق</span>
                                        <span className="text-lg font-black text-indigo-900">{net_trip}</span>
                                    </div>
                                </div>
                            </div>

                            {/* Status Controller Form */}
                            <div className="bg-white rounded-2xl border border-slate-200 shadow-xl p-8">
                                <h3 className="text-sm font-black text-slate-800 mb-6 border-b pb-4 uppercase tracking-wider">تحديث حالة الرحلة</h3>
                                <form onSubmit={updateStatus} className="space-y-6">
                                    <OdooField label="الحالة الحالية">
                                        <select 
                                            className="odoo-input text-indigo-600 font-black"
                                            value={statusForm.status}
                                            onChange={e => statusForm.setData('status', e.target.value)}
                                        >
                                            <option value="planned">تخطيط (Planned)</option>
                                            <option value="loading">تحميل (Loading)</option>
                                            <option value="transit">على الطريق (Transit)</option>
                                            <option value="at_destination">وصل للموقع (At Site)</option>
                                            <option value="completed">مكتمل ومقفل (Completed)</option>
                                        </select>
                                    </OdooField>

                                    {/* Dynamic Fields based on Status */}
                                    {statusForm.status === 'loading' && (
                                        <OdooField label="بدء التحميل">
                                            <input type="datetime-local" className="odoo-input" value={statusForm.actual_loading_start} onChange={e => statusForm.setData('actual_loading_start', e.target.value)} />
                                        </OdooField>
                                    )}
                                    {statusForm.status === 'transit' && (
                                        <OdooField label="نهاية التحميل / تحرك">
                                            <input type="datetime-local" className="odoo-input" value={statusForm.actual_loading_end} onChange={e => statusForm.setData('actual_loading_end', e.target.value)} />
                                        </OdooField>
                                    )}
                                    {statusForm.status === 'completed' && (
                                        <div className="space-y-4 animate-in fade-in">
                                            <OdooField label="موعد الوصول الفعلي">
                                                <input type="datetime-local" className="odoo-input" value={statusForm.actual_arrival} onChange={e => statusForm.setData('actual_arrival', e.target.value)} />
                                            </OdooField>
                                            <OdooField label="عداد المسافة عند الوصول">
                                                <input type="number" className="odoo-input" value={statusForm.end_km} onChange={e => statusForm.setData('end_km', e.target.value)} />
                                            </OdooField>
                                            <div className="grid grid-cols-2 gap-4">
                                                <OdooField label="لترات الديزل">
                                                    <input type="number" className="odoo-input" value={statusForm.fuel_amount} onChange={e => statusForm.setData('fuel_amount', e.target.value)} />
                                                </OdooField>
                                                <OdooField label="التكلفة الفعلية">
                                                    <input type="number" step="0.01" className="odoo-input text-rose-600 font-black" value={statusForm.fuel_cost} onChange={e => statusForm.setData('fuel_cost', e.target.value)} />
                                                </OdooField>
                                            </div>
                                        </div>
                                    )}

                                    <button 
                                        type="submit" disabled={statusForm.processing}
                                        className="w-full py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg hover:bg-indigo-700 transition-all"
                                    >
                                        حفظ التحديث
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Modals for Diesel & Stops */}
            {dieselModal && <DieselModal trip={trip} onClose={() => setDieselModal(false)} />}
            {eventModal && <StopModal trip={trip} onClose={() => setEventModal(false)} />}

            <style dangerouslySetInnerHTML={{ __html: `
                .odoo-input { width: 100%; border: none; border-bottom: 1px solid #e2e8f0; padding: 0.5rem 0; font-size: 0.875rem; font-weight: 600; color: #1e293b; transition: all 0.2s; }
                .odoo-input:focus { outline: none; border-bottom-color: #4f46e5; }
            `}} />
        </AuthenticatedLayout>
    );
}

function TimelineItem({ icon, label, time, children, active, completed, color }) {
    return (
        <div className="relative">
            <div className={`absolute -right-[3.8rem] top-0 w-10 h-10 rounded-full border-4 border-white shadow-lg flex items-center justify-center text-lg z-10 ${
                active ? 'bg-indigo-600 animate-pulse' : 
                completed ? 'bg-emerald-500' : 'bg-slate-200'
            }`}>
                {completed ? '✓' : icon}
            </div>
            <div className="flex flex-col">
                <div className="flex items-center justify-between mb-1">
                    <h4 className={`text-sm font-black ${active ? 'text-indigo-600' : completed ? 'text-emerald-600' : 'text-slate-400'}`}>
                        {label}
                    </h4>
                    <span className="text-[10px] font-bold text-slate-400 italic">
                        {time ? new Date(time).toLocaleString('ar-SA') : '---'}
                    </span>
                </div>
                {children}
            </div>
        </div>
    );
}

function Badge({ label, color }) {
    return <span className={`px-2 py-0.5 rounded text-[9px] font-black uppercase ${color}`}>{label}</span>;
}

function OdooField({ label, children }) {
    return (
        <div className="flex flex-col gap-1">
            <label className="text-[11px] font-bold text-slate-500 uppercase tracking-wider">{label}</label>
            {children}
        </div>
    );
}

// Sub-components for Modals
function DieselModal({ trip, onClose }) {
    const { data, setData, post, processing } = useForm({ amount: '', location: '', notes: '' });
    const submit = (e) => { e.preventDefault(); post(route('logistics.trips.diesel', trip.id), { onSuccess: onClose }); };
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
            <div className="bg-white w-full max-w-md rounded-2xl shadow-2xl p-8 animate-in zoom-in duration-200">
                <h3 className="text-xl font-bold mb-6">تسجيل ديزل جديد</h3>
                <form onSubmit={submit} className="space-y-4">
                    <OdooField label="المبلغ">
                        <input type="number" required autoFocus className="odoo-input text-2xl font-black text-indigo-600" value={data.amount} onChange={e => setData('amount', e.target.value)} />
                    </OdooField>
                    <OdooField label="الموقع">
                        <input type="text" className="odoo-input" value={data.location} onChange={e => setData('location', e.target.value)} />
                    </OdooField>
                    <div className="flex gap-3 pt-6">
                        <button type="submit" disabled={processing} className="flex-grow py-3 bg-indigo-600 text-white rounded-xl font-bold">حفظ</button>
                        <button type="button" onClick={onClose} className="px-6 py-3 bg-slate-100 text-slate-600 rounded-xl font-bold">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    );
}

function StopModal({ trip, onClose }) {
    const { data, setData, post, processing } = useForm({ reason: '', location: '', start_time: new Date().toISOString().slice(0, 16) });
    const submit = (e) => { e.preventDefault(); post(route('logistics.trips.stops.add', trip.id), { onSuccess: onClose }); };
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
            <div className="bg-white w-full max-w-md rounded-2xl shadow-2xl p-8 animate-in zoom-in duration-200">
                <h3 className="text-xl font-bold mb-6">تسجيل توقف طارئ</h3>
                <form onSubmit={submit} className="space-y-4">
                    <OdooField label="السبب">
                        <select required className="odoo-input" value={data.reason} onChange={e => setData('reason', e.target.value)}>
                            <option value="">إختر...</option>
                            <option value="rest">استراحة سائق</option>
                            <option value="saher">توقف ساهر / مرور</option>
                            <option value="breakdown">عطل ميكانيكي</option>
                            <option value="fuel">تزود بالوقود</option>
                            <option value="other">أخرى</option>
                        </select>
                    </OdooField>
                    <OdooField label="الموقع">
                        <input type="text" className="odoo-input" value={data.location} onChange={e => setData('location', e.target.value)} />
                    </OdooField>
                    <div className="flex gap-3 pt-6">
                        <button type="submit" disabled={processing} className="flex-grow py-3 bg-orange-600 text-white rounded-xl font-bold">تسجيل التوقف</button>
                        <button type="button" onClick={onClose} className="px-6 py-3 bg-slate-100 text-slate-600 rounded-xl font-bold">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    );
}
