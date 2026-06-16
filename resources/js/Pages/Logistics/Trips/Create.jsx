import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useEffect, useState, useRef } from 'react';
import axios from 'axios';

export default function Create({ auth, vehicles, drivers, brokers: initialBrokers, suppliers: initialSuppliers, routes }) {
    
    const [brokersList, setBrokersList] = useState(initialBrokers || []);
    const [suppliersList, setSuppliersList] = useState(initialSuppliers || []);
    
    const [showAddMainCompany, setShowAddMainCompany] = useState(false);
    const [newMainCompany, setNewMainCompany] = useState({ name: '', phone: '', tax_number: '' });
    const [savingMainCompany, setSavingMainCompany] = useState(false);
    const [mainCompanyError, setMainCompanyError] = useState('');

    const [showAddSubClient, setShowAddSubClient] = useState(false);
    const [newSubClient, setNewSubClient] = useState({ name: '', phone: '', tax_number: '' });
    const [savingSubClient, setSavingSubClient] = useState(false);
    const [subClientError, setSubClientError] = useState('');

    // New state for main company and sub‑clients
    const [subClients, setSubClients] = useState([]); // [{contact_id, name, price}]

    const { data, setData, post, processing, errors, transform } = useForm({
        vehicle_id: '',
        driver_id: '',
        route_id: '',
        waybill_no: '',
        broker_id: '',
        main_company_id: '', // added main company
        end_customer_name: '',
        cargo_type: '',
        weight: '',
        container_no: '',
        origin: '',
        destination: '',
        loading_site: '',
        discharge_site: '',
        total_trip_budget: 0,
        initial_diesel_amount: 0,
        broker_price: 0,
        etd: '',
        eta: '',
        eta_unloading: '',
        start_km: 0,
        sub_clients: [], // array of {contact_id, price}
    });

    // Auto-select vehicle if driver is assigned to one
    useEffect(() => {
        if (data.driver_id) {
            const assignedVehicle = vehicles.find(v => v.driver_id == data.driver_id);
            if (assignedVehicle) {
                setData('vehicle_id', assignedVehicle.id);
            }
        }
    }, [data.driver_id]);

    useEffect(() => {
        setSubClients([]);
    }, [data.broker_id]);

    const handleRouteSelect = (routeId) => {
        setData('route_id', routeId);
        if (routeId) {
            const selectedRoute = routes.find(r => r.id == routeId);
            if (selectedRoute) {
                setData(prev => ({
                    ...prev,
                    route_id: routeId,
                    origin: selectedRoute.origin,
                    destination: selectedRoute.destination,
                    total_trip_budget: selectedRoute.standard_budget,
                    initial_diesel_amount: selectedRoute.standard_diesel_budget || 0,
                    driver_commission: selectedRoute.standard_driver_commission || 0,
                }));
            }
        }
    };

    const handleAddMainCompany = async () => {
        if (!newMainCompany.name.trim()) {
            setMainCompanyError('اسم الشركة الأساسية مطلوب');
            return;
        }
        setSavingMainCompany(true);
        setMainCompanyError('');
        try {
            const response = await axios.post(route('logistics.trips.quick-store-maincompany'), newMainCompany);
            const created = response.data;
            const newObj = { id: created.id, name: created.name, is_main_company: true, is_sub_client: false };
            setBrokersList(prev => [...prev, newObj]);
            setData('broker_id', created.id);
            setShowAddMainCompany(false);
            setNewMainCompany({ name: '', phone: '', tax_number: '' });
        } catch (err) {
            if (err.response?.data?.errors?.name) {
                setMainCompanyError(err.response.data.errors.name[0]);
            } else {
                setMainCompanyError('حدث خطأ أثناء الحفظ');
            }
        } finally {
            setSavingMainCompany(false);
        }
    };

    const handleAddSubClient = async () => {
        if (!newSubClient.name.trim()) {
            setSubClientError('اسم العميل الفرعي مطلوب');
            return;
        }
        setSavingSubClient(true);
        setSubClientError('');
        try {
            const response = await axios.post(route('logistics.trips.quick-store-subclient'), {
                ...newSubClient,
                main_company_id: data.broker_id
            });
            const created = response.data;
            const newObj = { id: created.id, name: created.name, is_main_company: false, is_sub_client: true, main_company_id: created.main_company_id };
            setBrokersList(prev => [...prev, newObj]);
            
            // Add to selected sub clients
            setSubClients(prev => [...prev, { contact_id: created.id, name: created.name, price: 0 }]);
            
            setShowAddSubClient(false);
            setNewSubClient({ name: '', phone: '', tax_number: '' });
        } catch (err) {
            if (err.response?.data?.errors?.name) {
                setSubClientError(err.response.data.errors.name[0]);
            } else {
                setSubClientError('حدث خطأ أثناء الحفظ');
            }
        } finally {
            setSavingSubClient(false);
        }
    };

    // Handlers for sub‑clients
    const handleSubClientSelect = (e) => {
        const selected = Array.from(e.target.selectedOptions).map(o => o.value);
        const newList = selected.map(id => {
            const existing = subClients.find(sc => sc.contact_id === id);
            const name = brokersList.find(b => b.id == id)?.name || '';
            return existing ? existing : { contact_id: id, name, price: 0 };
        });
        setSubClients(newList);
    };

    const handleSubClientPriceChange = (contactId, val) => {
        setSubClients(prev => prev.map(sc => sc.contact_id == contactId ? { ...sc, price: parseFloat(val) || 0 } : sc));
    };

    const removeSubClient = (contactId) => {
        setSubClients(prev => prev.filter(sc => sc.contact_id != contactId));
    };
    const submit = (e) => {
        e.preventDefault();
        transform((data) => ({
            ...data,
            sub_clients: subClients.map(sc => ({ contact_id: sc.contact_id, price: sc.price })),
        }));
        post(route('logistics.trips.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="إنشاء رحلة جديدة" />

            <div className="min-h-screen bg-[#f8fafc] pb-6" dir="rtl">
                {/* Top Bar */}
                <div className="bg-white border-b border-slate-200 sticky top-0 z-30 px-8 py-3 flex items-center justify-between shadow-sm">
                    <div className="flex items-center gap-4">
                        <div className="bg-indigo-600 p-2 rounded-xl text-white shadow-md">
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" /></svg>
                        </div>
                        <div>
                            <div className="flex items-center gap-3">
                                <Link href={route('logistics.trips.index')} className="text-slate-400 hover:text-indigo-600 transition-colors text-sm font-bold">الرحلات</Link>
                                <span className="text-slate-300">/</span>
                                <h1 className="text-lg font-black text-slate-800 leading-none">إنشاء رحلة جديدة</h1>
                            </div>
                            <p className="text-[10px] text-slate-400 font-bold mt-0.5 uppercase tracking-wider">New Trip Registration</p>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        {/* Status Pills */}
                        <div className="hidden md:flex items-center gap-1 ml-6">
                            <StatusPill label="مسودة" active />
                            <StatusArrow />
                            <StatusPill label="قيد التحميل" />
                            <StatusArrow />
                            <StatusPill label="على الطريق" />
                            <StatusArrow />
                            <StatusPill label="مكتمل" />
                        </div>
                        <button 
                            onClick={submit} disabled={processing}
                            className="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-md transition-all"
                        >
                            {processing ? 'جاري الحفظ...' : 'حفظ وتشغيل'}
                        </button>
                        <Link href={route('logistics.trips.index')} className="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-sm font-bold transition-all">
                            إلغاء
                        </Link>
                    </div>
                </div>

                <div className="max-w-[1600px] mx-auto px-8 pt-6">
                    <form onSubmit={submit}>
                        {/* === MAIN GRID: 3 Columns === */}
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">

                            {/* Column 1: الموارد والمسار + تفاصيل الشحنة */}
                            <div className="space-y-6">
                                {/* Card: الموارد والمسار */}
                                <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                                    <SectionTitle icon="🚛" title="الموارد والمسار" />
                                    <div className="space-y-3">
                                        <Field label="السائق المسئول" required>
                                            <select required className="trip-select" value={data.driver_id} onChange={e => setData('driver_id', e.target.value)}>
                                                <option value="">إختر السائق...</option>
                                                {drivers.map(d => (
                                                    <option key={d.id} value={d.id}>{d.name} ({d.employee_no})</option>
                                                ))}
                                            </select>
                                        </Field>

                                        <Field label="الشاحنة / المقطورة">
                                            <div className="px-3 py-2 bg-slate-50 border border-slate-100 rounded-lg text-sm font-bold text-slate-700 flex items-center justify-between">
                                                <span>{vehicles.find(v => v.id == data.vehicle_id)?.plate_no || 'يتم التحديد آلياً'}</span>
                                                <span className="text-base">🚛</span>
                                            </div>
                                        </Field>

                                        <Field label="المسار المحدد">
                                            <select className="trip-select" value={data.route_id} onChange={e => handleRouteSelect(e.target.value)}>
                                                <option value="">إدخال يدوي...</option>
                                                {routes.map(r => (
                                                    <option key={r.id} value={r.id}>{r.name} ({r.distance_km} كم)</option>
                                                ))}
                                            </select>
                                        </Field>

                                        <div className="grid grid-cols-2 gap-3">
                                            <Field label="من (التحميل)" required>
                                                <input type="text" className="trip-input" value={data.origin} onChange={e => setData('origin', e.target.value)} />
                                            </Field>
                                            <Field label="إلى (التفريغ)" required>
                                                <input type="text" className="trip-input" value={data.destination} onChange={e => setData('destination', e.target.value)} />
                                            </Field>
                                        </div>
                                    </div>
                                </div>

                                {/* Card: تفاصيل الشحنة */}
                                <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                                    <SectionTitle icon="📦" title="تفاصيل الشحنة" />
                                    <div className="space-y-3">
                                        <Field label="نوع الحمولة">
                                            <input type="text" placeholder="حديد، سيراميك..." className="trip-input" value={data.cargo_type} onChange={e => setData('cargo_type', e.target.value)} />
                                        </Field>
                                        <div className="grid grid-cols-2 gap-3">
                                            <Field label="الوزن (طن)">
                                                <input type="number" className="trip-input" value={data.weight} onChange={e => setData('weight', e.target.value)} />
                                            </Field>
                                            <Field label="رقم الحاوية">
                                                <input type="text" className="trip-input" value={data.container_no} onChange={e => setData('container_no', e.target.value)} />
                                            </Field>
                                        </div>
                                        <Field label="رقم بوليصة الشحن">
                                            <input type="text" className="trip-input" value={data.waybill_no} onChange={e => setData('waybill_no', e.target.value)} />
                                        </Field>
                                    </div>
                                </div>
                            </div>

                            {/* Column 2: البيانات المالية */}
                            <div className="space-y-6">
                                <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                                    <SectionTitle icon="💰" title="البيانات المالية" />
                                    <div className="space-y-3">
                                        <Field label="إجمالي ميزانية الرحلة (شامل الديزل)" required>
                                            <div className="relative">
                                                <input type="number" step="0.01" className="trip-input text-indigo-600 font-black" value={data.total_trip_budget} onChange={e => setData('total_trip_budget', e.target.value)} />
                                                <span className="absolute left-2 top-1/2 -translate-y-1/2 text-[9px] font-black text-slate-400">SAR</span>
                                            </div>
                                        </Field>
                                        <Field label="عهدة الديزل الأولية">
                                            <div className="relative">
                                                <input type="number" step="0.01" className="trip-input text-orange-600 font-black" value={data.initial_diesel_amount} onChange={e => setData('initial_diesel_amount', e.target.value)} />
                                                <span className="absolute left-2 top-1/2 -translate-y-1/2 text-[9px] font-black text-slate-400">SAR</span>
                                            </div>
                                        </Field>
                                        <Field label="سعر البيع للعميل (أجرة الرحلة)">
                                            <div className="relative">
                                                <input type="number" step="0.01" className="trip-input text-emerald-600 font-black" value={data.broker_price} onChange={e => setData('broker_price', e.target.value)} />
                                                <span className="absolute left-2 top-1/2 -translate-y-1/2 text-[9px] font-black text-slate-400">SAR</span>
                                            </div>
                                        </Field>
                                    </div>

                                    {/* Net Summary Dark Card */}
                                    <div className="mt-4 bg-slate-900 rounded-xl p-4 text-white flex justify-between items-center">
                                        <div>
                                            <p className="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">صافي السائق المتوقع</p>
                                            <p className="text-xl font-black text-emerald-400">{fmt(data.total_trip_budget - data.initial_diesel_amount)}</p>
                                        </div>
                                        <div className="text-right">
                                            <p className="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">الربح المتوقع</p>
                                            <p className="text-xl font-black text-amber-400">{fmt(data.broker_price - data.total_trip_budget)}</p>
                                        </div>
                                    </div>
                                </div>

                                {/* Card: الجدول الزمني */}
                                <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                                    <SectionTitle icon="📅" title="الجدول الزمني" />
                                    <div className="space-y-3">
                                        <div className="grid grid-cols-2 gap-3">
                                            <Field label="موعد التحميل">
                                                <input type="datetime-local" className="trip-input text-xs" value={data.etd} onChange={e => setData('etd', e.target.value)} />
                                            </Field>
                                            <Field label="موعد الوصول">
                                                <input type="datetime-local" className="trip-input text-xs" value={data.eta} onChange={e => setData('eta', e.target.value)} />
                                            </Field>
                                        </div>
                                        <div className="grid grid-cols-2 gap-3">
                                            <Field label="موعد التفريغ">
                                                <input type="datetime-local" className="trip-input text-xs" value={data.eta_unloading} onChange={e => setData('eta_unloading', e.target.value)} />
                                            </Field>
                                            <Field label="عداد KM عند البدء">
                                                <input type="number" className="trip-input" value={data.start_km} onChange={e => setData('start_km', e.target.value)} />
                                            </Field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Column 3: أطراف الرحلة */}
                            <div className="space-y-6">
                                <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                                    <SectionTitle icon="🏢" title="أطراف الرحلة" />
                                    <div className="space-y-3">
                                        <Field label="الشركة الأساسية / العميل الرئيسي" required>
                                            <div className="flex gap-2 items-center">
                                                <select required className="trip-select flex-1" value={data.broker_id} onChange={e => setData('broker_id', e.target.value)}>
                                                    <option value="">إختر الشركة الأساسية...</option>
                                                    {brokersList.filter(b => b.is_main_company).map(b => (
                                                        <option key={b.id} value={b.id}>{b.name}</option>
                                                    ))}
                                                </select>
                                                <button type="button" className="shrink-0 w-8 h-8 bg-indigo-600 text-white rounded-lg font-bold text-sm flex items-center justify-center hover:bg-indigo-700 transition-all" onClick={() => setShowAddMainCompany(true)} title="إضافة شركة أساسية">+</button>
                                            </div>
                                        </Field>

                                        <Field label="العملاء الفرعيين (مواقع الاستلام)">
                                            <div className="space-y-2">
                                                <div className="flex gap-2 items-center">
                                                    <select className="trip-select flex-1" onChange={(e) => {
                                                        if (e.target.value) {
                                                            const id = e.target.value;
                                                            if (!subClients.find(sc => sc.contact_id == id)) {
                                                                const name = brokersList.find(b => b.id == id)?.name || '';
                                                                setSubClients(prev => [...prev, { contact_id: id, name, price: 0 }]);
                                                            }
                                                            e.target.value = '';
                                                        }
                                                    }}>
                                                        <option value="">إضافة عميل فرعي...</option>
                                                        {brokersList.filter(b => b.is_sub_client && b.main_company_id == data.broker_id && !subClients.find(sc => sc.contact_id == b.id)).map(b => (
                                                            <option key={b.id} value={b.id}>{b.name}</option>
                                                        ))}
                                                    </select>
                                                    <button type="button" className="shrink-0 w-8 h-8 bg-indigo-600 text-white rounded-lg font-bold text-sm flex items-center justify-center hover:bg-indigo-700 transition-all" onClick={() => {
                                                        if (!data.broker_id) {
                                                            alert('يرجى اختيار الشركة الأساسية / العميل الرئيسي أولاً.');
                                                            return;
                                                        }
                                                        setShowAddSubClient(true);
                                                    }} title="إضافة عميل فرعي">+</button>
                                                </div>
                                                
                                                {/* Selected sub-clients list */}
                                                {subClients.length > 0 && (
                                                    <div className="bg-slate-50 rounded-xl border border-slate-100 divide-y divide-slate-100 overflow-hidden">
                                                        {subClients.map((sc) => (
                                                            <div key={sc.contact_id} className="flex items-center gap-2 px-3 py-2">
                                                                <span className="text-indigo-600 text-xs">🏪</span>
                                                                <span className="flex-1 text-xs font-bold text-slate-700 truncate">{sc.name}</span>
                                                                <div className="relative w-24">
                                                                    <input type="number" step="0.01" className="trip-input text-xs text-center font-bold" placeholder="السعر" value={sc.price} onChange={e => handleSubClientPriceChange(sc.contact_id, e.target.value)} />
                                                                </div>
                                                                <button type="button" className="text-rose-400 hover:text-rose-600 text-sm transition-colors" onClick={() => removeSubClient(sc.contact_id)}>✕</button>
                                                            </div>
                                                        ))}
                                                    </div>
                                                )}
                                            </div>
                                        </Field>
                                    </div>
                                </div>

                                {/* Errors Display */}
                                {Object.keys(errors).length > 0 && (
                                    <div className="bg-rose-50 border border-rose-200 rounded-xl p-4">
                                        <p className="text-xs font-bold text-rose-700 mb-2">⚠️ يرجى تصحيح الأخطاء التالية:</p>
                                        <ul className="text-[11px] text-rose-600 space-y-1 list-disc list-inside">
                                            {Object.entries(errors).map(([key, val]) => (
                                                <li key={key}>{val}</li>
                                            ))}
                                        </ul>
                                    </div>
                                )}
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {/* Add Main Company Modal */}
            {showAddMainCompany && (
                <QuickAddModal
                    title="إضافة شركة أساسية جديدة"
                    error={mainCompanyError}
                    saving={savingMainCompany}
                    values={newMainCompany}
                    onChange={setNewMainCompany}
                    onSave={handleAddMainCompany}
                    onClose={() => setShowAddMainCompany(false)}
                    namePlaceholder="مثال: شركة نقليات كبرى"
                    saveLabel="حفظ الشركة"
                />
            )}

            {/* Add Sub Client Modal */}
            {showAddSubClient && (
                <QuickAddModal
                    title="إضافة عميل فرعي جديد"
                    error={subClientError}
                    saving={savingSubClient}
                    values={newSubClient}
                    onChange={setNewSubClient}
                    onSave={handleAddSubClient}
                    onClose={() => setShowAddSubClient(false)}
                    namePlaceholder="مثال: مؤسسة المستورد الفرعي"
                    saveLabel="حفظ العميل"
                />
            )}

            <style dangerouslySetInnerHTML={{ __html: `
                .trip-input {
                    width: 100%;
                    border: 1px solid #e2e8f0;
                    border-radius: 0.6rem;
                    padding: 0.45rem 0.75rem;
                    font-size: 0.8rem;
                    font-weight: 600;
                    color: #1e293b;
                    background-color: #fff;
                    transition: border-color 0.2s, box-shadow 0.2s;
                }
                .trip-input:focus {
                    outline: none;
                    border-color: #4f46e5;
                    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
                }
                .trip-select {
                    width: 100%;
                    border: 1px solid #e2e8f0;
                    border-radius: 0.6rem;
                    padding: 0.45rem 0.75rem;
                    font-size: 0.8rem;
                    font-weight: 600;
                    color: #1e293b;
                    background-color: #fff;
                    transition: border-color 0.2s, box-shadow 0.2s;
                    appearance: none;
                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
                    background-repeat: no-repeat;
                    background-position: left 0.5rem center;
                    background-size: 1rem;
                }
                .trip-select:focus {
                    outline: none;
                    border-color: #4f46e5;
                    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
                }
            `}} />
        </AuthenticatedLayout>
    );
}

/* === Reusable Sub-Components === */

function QuickAddModal({ title, error, saving, values, onChange, onSave, onClose, namePlaceholder, saveLabel }) {
    return (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" onClick={onClose}>
            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden" dir="rtl" onClick={e => e.stopPropagation()}>
                <div className="bg-gradient-to-l from-indigo-600 to-indigo-500 px-6 py-4 flex items-center justify-between">
                    <h3 className="text-white font-bold text-sm flex items-center gap-2">
                        <span className="text-lg">➕</span>
                        {title}
                    </h3>
                    <button onClick={onClose} className="text-white/70 hover:text-white text-lg font-bold transition-colors">✕</button>
                </div>
                <div className="p-5 space-y-4">
                    {error && (
                        <div className="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2">
                            <span>⚠️</span> {error}
                        </div>
                    )}
                    <div>
                        <label className="text-xs font-bold text-slate-600 block mb-1">الاسم <span className="text-rose-500">*</span></label>
                        <input type="text" className="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" value={values.name} onChange={e => onChange({ ...values, name: e.target.value })} placeholder={namePlaceholder} autoFocus />
                    </div>
                    <div className="grid grid-cols-2 gap-3">
                        <div>
                            <label className="text-xs font-bold text-slate-600 block mb-1">رقم الجوال</label>
                            <input type="text" className="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" value={values.phone} onChange={e => onChange({ ...values, phone: e.target.value })} placeholder="05xxxxxxxx" />
                        </div>
                        <div>
                            <label className="text-xs font-bold text-slate-600 block mb-1">الرقم الضريبي</label>
                            <input type="text" className="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" value={values.tax_number} onChange={e => onChange({ ...values, tax_number: e.target.value })} placeholder="3xxxxxxxxxx00003" />
                        </div>
                    </div>
                </div>
                <div className="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center gap-3 justify-end">
                    <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">إلغاء</button>
                    <button type="button" onClick={onSave} disabled={saving} className="px-5 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md transition-all disabled:opacity-50 flex items-center gap-2">
                        {saving ? (
                            <><span className="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span> جاري الحفظ...</>
                        ) : (
                            <><span>💾</span> {saveLabel}</>
                        )}
                    </button>
                </div>
            </div>
        </div>
    );
}

function SectionTitle({ icon, title }) {
    return (
        <h3 className="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
            <span className="w-1.5 h-4 bg-indigo-600 rounded-full"></span>
            <span className="text-base">{icon}</span>
            {title}
        </h3>
    );
}

function Field({ label, children, required }) {
    return (
        <div className="flex flex-col gap-1">
            <label className="text-[11px] font-bold text-slate-500">
                {label} {required && <span className="text-rose-500">*</span>}
            </label>
            {children}
        </div>
    );
}

function StatusPill({ label, active }) {
    return (
        <div className={`px-3 py-0.5 text-[10px] font-bold rounded-md transition-all ${active ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 bg-slate-50'}`}>
            {label}
        </div>
    );
}

function StatusArrow() {
    return <span className="text-[10px] text-slate-300">❯</span>;
}

const fmt = (num) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR' }).format(num || 0);
