import { Head, useForm, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function Tires({ auth, tires, vehicles, flash }) {
    const [showModal, setShowModal] = useState(false);

    const { data, setData, post, processing, reset, errors } = useForm({
        vehicle_id: '',
        position: '',
        unit_type: 'head',
        serial_no: '',
        brand: '',
        purchase_date: '',
        warranty_months: '',
        expected_life_km: '',
        installation_km: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('logistics.workshop.tires.store'), {
            onSuccess: () => {
                setShowModal(false);
                reset();
            }
        });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="إدارة كفرات الأسطول" />

            <div className="py-12 bg-gray-50 min-h-screen" dir="rtl">
                <div className="max-w-7xl mx-auto px-4 sm:px-6">
                    
                    <div className="mb-10 flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-black text-slate-900 tracking-tighter italic">إدارة وتتبع الكفرات (Tires)</h1>
                            <p className="mt-1 text-slate-500 font-bold">تتبع السريال، الضمان، ومواقع التركيب (الرأس / السطحة)</p>
                        </div>
                        <button 
                            onClick={() => setShowModal(true)}
                            className="bg-indigo-900 text-white px-8 py-4 rounded-2xl font-black shadow-xl shadow-indigo-100 hover:bg-black transition-all flex items-center gap-3"
                        >
                            <span className="text-xl">➕</span> تسجيل كفر جديد
                        </button>
                    </div>

                    {flash?.success && <div className="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-6 py-4 rounded-2xl font-black shadow-sm">{flash.success}</div>}

                    <div className="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden">
                        <table className="w-full text-right text-sm">
                            <thead className="bg-[#1e1b4b] text-white">
                                <tr>
                                    <th className="px-8 py-6 font-black uppercase text-[10px] tracking-widest italic">الشاحنة</th>
                                    <th className="px-8 py-6 font-black uppercase text-[10px] tracking-widest italic">الموقع (Position)</th>
                                    <th className="px-8 py-6 font-black uppercase text-[10px] tracking-widest italic">رقم السريال / الماركة</th>
                                    <th className="px-8 py-6 font-black uppercase text-[10px] tracking-widest italic">تاريخ الشراء / العداد</th>
                                    <th className="px-8 py-6 font-black uppercase text-[10px] tracking-widest italic text-center">الضمان</th>
                                    <th className="px-8 py-6 font-black uppercase text-[10px] tracking-widest italic text-center">الحالة</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-50">
                                {tires.map(tire => (
                                    <tr key={tire.id} className="hover:bg-indigo-50/30 transition-colors">
                                        <td className="px-8 py-6 font-black text-slate-900">{tire.vehicle?.plate_no}</td>
                                        <td className="px-8 py-6">
                                            <span className={`px-2 py-1 rounded text-[10px] font-black uppercase ml-2 ${tire.unit_type === 'head' ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600'}`}>
                                                {tire.unit_type === 'head' ? 'الرأس' : 'السطحة'}
                                            </span>
                                            <span className="font-bold text-slate-600">{tire.position}</span>
                                        </td>
                                        <td className="px-8 py-6">
                                            <div className="flex flex-col">
                                                <span className="font-mono text-slate-800 font-bold">{tire.serial_no}</span>
                                                <span className="text-[10px] text-slate-400 font-black">{tire.brand || '--'}</span>
                                            </div>
                                        </td>
                                        <td className="px-8 py-6">
                                            <div className="flex flex-col">
                                                <span className="text-slate-600">{tire.purchase_date || '--'}</span>
                                                <span className="text-[10px] text-indigo-500 font-bold tracking-widest italic">{tire.installation_km?.toLocaleString()} KM</span>
                                            </div>
                                        </td>
                                        <td className="px-8 py-6 text-center font-bold text-slate-500">
                                            {tire.warranty_months ? `${tire.warranty_months} شهر` : '--'}
                                        </td>
                                        <td className="px-8 py-6 text-center">
                                            <span className="text-emerald-500 font-black italic">Active ✅</span>
                                        </td>
                                    </tr>
                                ))}
                                {tires.length === 0 && (
                                    <tr><td colSpan="6" className="py-20 text-center text-slate-300 font-black italic">لم يتم تسجيل أي كفرات بعد...</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* Addition Modal */}
            {showModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                    <div className="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-in zoom-in duration-200">
                        <div className="p-8">
                            <h3 className="text-2xl font-black text-slate-900 mb-6 italic tracking-tight border-b pb-4">تسجيل كفر جديد في العهدة</h3>
                            <form onSubmit={submit} className="space-y-6">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-[10px] font-black text-slate-400 uppercase mb-2">رقم الشاحنة</label>
                                        <select required className="w-full rounded-2xl border-slate-100 bg-slate-50 font-black" value={data.vehicle_id} onChange={e => setData('vehicle_id', e.target.value)}>
                                            <option value="">اختر الشاحنة</option>
                                            {vehicles.map(v => <option key={v.id} value={v.id}>{v.plate_no}</option>)}
                                        </select>
                                    </div>
                                    <div>
                                        <label className="block text-[10px] font-black text-slate-400 uppercase mb-2">نوع الوحدة</label>
                                        <select required className="w-full rounded-2xl border-slate-100 bg-slate-50 font-black" value={data.unit_type} onChange={e => setData('unit_type', e.target.value)}>
                                            <option value="head">الرأس (Head)</option>
                                            <option value="trailer">السطحة (Trailer)</option>
                                        </select>
                                    </div>
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-[10px] font-black text-slate-400 uppercase mb-2">موقع التركيب</label>
                                        <input type="text" required placeholder="مثال: يمين - محور 1 خارجي" className="w-full rounded-2xl border-slate-100 bg-slate-50 font-black" value={data.position} onChange={e => setData('position', e.target.value)} />
                                    </div>
                                    <div>
                                        <label className="block text-[10px] font-black text-slate-400 uppercase mb-2">رقم السريال (Serial No)</label>
                                        <input type="text" required className="w-full rounded-2xl border-slate-100 bg-slate-50 font-mono font-black placeholder:font-sans" placeholder="Serial Number" value={data.serial_no} onChange={e => setData('serial_no', e.target.value)} />
                                    </div>
                                </div>
                                <div className="grid grid-cols-3 gap-4">
                                    <div>
                                        <label className="block text-[10px] font-black text-slate-400 uppercase mb-2">الماركة</label>
                                        <input type="text" className="w-full rounded-2xl border-slate-100 bg-slate-50 font-black" value={data.brand} onChange={e => setData('brand', e.target.value)} />
                                    </div>
                                    <div>
                                        <label className="block text-[10px] font-black text-slate-400 uppercase mb-2">تاريخ الشراء</label>
                                        <input type="date" className="w-full rounded-2xl border-slate-100 bg-slate-50 font-black text-xs" value={data.purchase_date} onChange={e => setData('purchase_date', e.target.value)} />
                                    </div>
                                    <div>
                                        <label className="block text-[10px] font-black text-slate-400 uppercase mb-2">الضمان (شهر)</label>
                                        <input type="number" className="w-full rounded-2xl border-slate-100 bg-slate-50 font-black" value={data.warranty_months} onChange={e => setData('warranty_months', e.target.value)} />
                                    </div>
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-[10px] font-black text-slate-400 uppercase mb-2">العداد عند التركيب (KM)</label>
                                        <input type="number" className="w-full rounded-2xl border-slate-100 bg-slate-50 font-black" value={data.installation_km} onChange={e => setData('installation_km', e.target.value)} />
                                    </div>
                                    <div>
                                        <label className="block text-[10px] font-black text-slate-400 uppercase mb-2">العمر الافتراضي (KM)</label>
                                        <input type="number" className="w-full rounded-2xl border-slate-100 bg-slate-50 font-black" value={data.expected_life_km} onChange={e => setData('expected_life_km', e.target.value)} />
                                    </div>
                                </div>
                                <div className="flex gap-4 pt-6">
                                    <button type="button" onClick={() => setShowModal(false)} className="flex-1 bg-slate-100 text-slate-400 font-black py-4 rounded-2xl">إلغاء</button>
                                    <button type="submit" disabled={processing} className="flex-[2] bg-indigo-900 text-white font-black py-4 rounded-2xl shadow-xl shadow-indigo-100">تثبيت البيانات في النظام</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}

        </AuthenticatedLayout>
    );
}
