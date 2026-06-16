import { Head, useForm, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState, useEffect } from 'react';

export default function MonthlyBilling({ auth, trips, brokers, vehicles, paymentAccounts, filters }) {
    const [selectedTrips, setSelectedTrips] = useState([]);
    
    // Filter form state
    const [filterState, setFilterState] = useState({
        broker_id: filters.broker_id || '',
        vehicle_id: filters.vehicle_id || '',
        month: filters.month || new Date().getMonth() + 1,
        year: filters.year || new Date().getFullYear(),
    });

    // Invoicing form state
    const invoiceForm = useForm({
        trip_ids: [],
        broker_id: filters.broker_id || '',
        invoice_date: new Date().toISOString().split('T')[0],
        payment_mode: 'credit',
        payment_account_id: '',
        notes: '',
    });

    // Handle filter search
    const handleSearch = (e) => {
        e.preventDefault();
        if (!filterState.broker_id || !filterState.vehicle_id || !filterState.month || !filterState.year) {
            alert('يرجى تحديد جميع خيارات التصفية أولاً (العميل، الشاحنة، الشهر، السنة)');
            return;
        }
        router.get(route('logistics.trips.monthly-billing'), filterState, {
            preserveState: true,
            onSuccess: () => {
                setSelectedTrips([]);
            }
        });
    };

    // Update form broker_id whenever filter broker changes
    useEffect(() => {
        invoiceForm.setData('broker_id', filterState.broker_id);
    }, [filterState.broker_id]);

    // Handle select/deselect all
    const handleSelectAll = (checked) => {
        if (checked) {
            const allIds = trips.map(t => t.id);
            setSelectedTrips(allIds);
            invoiceForm.setData('trip_ids', allIds);
        } else {
            setSelectedTrips([]);
            invoiceForm.setData('trip_ids', []);
        }
    };

    // Handle select individual
    const handleSelectTrip = (id, checked) => {
        let updated = [];
        if (checked) {
            updated = [...selectedTrips, id];
        } else {
            updated = selectedTrips.filter(item => item !== id);
        }
        setSelectedTrips(updated);
        invoiceForm.setData('trip_ids', updated);
    };

    // Auto-select all trips when loaded
    useEffect(() => {
        if (trips && trips.length > 0) {
            const allIds = trips.map(t => t.id);
            setSelectedTrips(allIds);
            invoiceForm.setData('trip_ids', allIds);
        } else {
            setSelectedTrips([]);
            invoiceForm.setData('trip_ids', []);
        }
    }, [trips]);

    // Calculate totals based on selection
    const selectedTripsData = trips.filter(t => selectedTrips.includes(t.id));
    const subtotal = selectedTripsData.reduce((acc, t) => acc + parseFloat(t.broker_price || 0), 0);
    const vat = subtotal * 0.15;
    const totalAmount = subtotal + vat;

    // Filter payment accounts based on mode - only by code prefix
    const filteredPaymentAccounts = paymentAccounts.filter(acc => {
        const code = String(acc.code || '');
        if (invoiceForm.data.payment_mode === 'cash') {
            return code.startsWith('1101') || code.startsWith('111') || (acc.name && acc.name.includes('صندوق'));
        } else if (invoiceForm.data.payment_mode === 'bank') {
            return code.startsWith('1102') || code.startsWith('112') || (acc.name && acc.name.includes('بنك'));
        }
        return true;
    });

    const submitInvoice = (e) => {
        e.preventDefault();
        if (selectedTrips.length === 0) {
            alert('يرجى تحديد رحلة واحدة على الأقل لإصدار الفاتورة لها.');
            return;
        }
        invoiceForm.post(route('logistics.trips.generate-monthly-invoice'), {
            onSuccess: () => {
                setSelectedTrips([]);
            }
        });
    };

    const months = [
        { value: 1, label: 'يناير' }, { value: 2, label: 'فبراير' }, { value: 3, label: 'مارس' },
        { value: 4, label: 'أبريل' }, { value: 5, label: 'مايو' }, { value: 6, label: 'يونيو' },
        { value: 7, label: 'يوليو' }, { value: 8, label: 'أغسطس' }, { value: 9, label: 'سبتمبر' },
        { value: 10, label: 'أكتوبر' }, { value: 11, label: 'نوفمبر' }, { value: 12, label: 'ديسمبر' },
    ];

    const currentYear = new Date().getFullYear();
    const years = Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="الفوترة الشهرية المجمعة" />

            <div className="min-h-screen bg-[#f8fafc] pb-12" dir="rtl">
                {/* Top Bar - Matching Routes style */}
                <div className="bg-white border-b border-slate-200 sticky top-0 z-30 px-8 py-4 flex items-center justify-between shadow-sm">
                    <div className="flex items-center gap-4">
                        <div className="bg-indigo-600 p-2 rounded-xl text-white shadow-md">
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <div>
                            <h1 className="text-lg font-black text-slate-800 leading-none">الفوترة الشهرية المجمعة</h1>
                            <p className="text-[10px] text-slate-400 font-bold mt-1 uppercase tracking-wider">Monthly Consolidated Billing</p>
                        </div>
                    </div>
                </div>

                <div className="max-w-[1400px] mx-auto p-8 space-y-6">
                    
                    {/* Filter Card - Inline like Routes form */}
                    <div className="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 className="text-sm font-bold text-slate-800 mb-6 border-b pb-2">
                            تصفية وتحديد الرحلات المنتهية
                        </h3>
                        <form onSubmit={handleSearch}>
                            <div className="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
                                <div>
                                    <label className="text-xs font-bold text-slate-600 mb-1.5 block">العميل / الشركة الوسيطة *</label>
                                    <select required className="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" value={filterState.broker_id} onChange={e => setFilterState({ ...filterState, broker_id: e.target.value })}>
                                        <option value="">إختر العميل...</option>
                                        {brokers.map(b => (
                                            <option key={b.id} value={b.id}>{b.name}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="text-xs font-bold text-slate-600 mb-1.5 block">الشاحنة (Plate No) *</label>
                                    <select required className="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" value={filterState.vehicle_id} onChange={e => setFilterState({ ...filterState, vehicle_id: e.target.value })}>
                                        <option value="">إختر الشاحنة...</option>
                                        {vehicles.map(v => (
                                            <option key={v.id} value={v.id}>{v.plate_no}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="text-xs font-bold text-slate-600 mb-1.5 block">الشهر *</label>
                                    <select required className="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" value={filterState.month} onChange={e => setFilterState({ ...filterState, month: e.target.value })}>
                                        {months.map(m => (
                                            <option key={m.value} value={m.value}>{m.label}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="text-xs font-bold text-slate-600 mb-1.5 block">السنة *</label>
                                    <select required className="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" value={filterState.year} onChange={e => setFilterState({ ...filterState, year: e.target.value })}>
                                        {years.map(y => (
                                            <option key={y} value={y}>{y}</option>
                                        ))}
                                    </select>
                                </div>
                                <div className="flex items-end">
                                    <button type="submit" className="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-md transition-all flex items-center justify-center gap-2">
                                        🔍 بحث
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {/* Main Content: 3-column layout */}
                    {trips && (
                        <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                            {/* Trips Table - 8 columns */}
                            <div className="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                                <div className="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                                    <h3 className="text-sm font-bold text-slate-800">
                                        سجل الرحلات المطابقة
                                    </h3>
                                    {trips.length > 0 && (
                                        <span className="bg-indigo-50 text-indigo-700 text-[10px] font-black px-3 py-1 rounded-lg border border-indigo-100">
                                            {trips.length} رحلة منتهية
                                        </span>
                                    )}
                                </div>

                                {trips.length === 0 ? (
                                    <div className="p-12 text-center text-slate-400">
                                        <div className="text-3xl mb-3">🚚</div>
                                        <p className="text-sm font-bold">لا توجد رحلات منتهية غير مفوترة مطابقة.</p>
                                    </div>
                                ) : (
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-right text-sm border-collapse">
                                            <thead className="bg-slate-50 border-b border-slate-100">
                                                <tr className="text-slate-400 font-black text-[10px] uppercase tracking-widest">
                                                    <th className="px-6 py-3 w-10 text-center">
                                                        <input 
                                                            type="checkbox" 
                                                            className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                                            checked={selectedTrips.length === trips.length}
                                                            onChange={e => handleSelectAll(e.target.checked)}
                                                        />
                                                    </th>
                                                    <th className="px-6 py-3 text-center w-8">م</th>
                                                    <th className="px-6 py-3">الرحلة</th>
                                                    <th className="px-6 py-3">التاريخ</th>
                                                    <th className="px-6 py-3">المسار</th>
                                                    <th className="px-6 py-3">المورد</th>
                                                    <th className="px-6 py-3 text-left">السعر (SAR)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {trips.map((trip, idx) => (
                                                    <tr key={trip.id} className="border-b border-slate-50 hover:bg-slate-50/50 transition-colors font-semibold text-slate-700">
                                                        <td className="px-6 py-3 text-center">
                                                            <input 
                                                                type="checkbox"
                                                                className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                                                checked={selectedTrips.includes(trip.id)}
                                                                onChange={e => handleSelectTrip(trip.id, e.target.checked)}
                                                            />
                                                        </td>
                                                        <td className="px-6 py-3 text-center text-slate-400 text-xs">{idx + 1}</td>
                                                        <td className="px-6 py-3 font-bold text-indigo-600 text-xs">{trip.trip_no}</td>
                                                        <td className="px-6 py-3 text-[11px] text-slate-500">{new Date(trip.created_at).toLocaleDateString('ar-SA')}</td>
                                                        <td className="px-6 py-3 text-[11px]">{trip.origin} ➔ {trip.destination}</td>
                                                        <td className="px-6 py-3 text-[11px] text-slate-500">{trip.end_customer_name || 'N/A'}</td>
                                                        <td className="px-6 py-3 text-left font-black text-slate-800 text-xs">{fmt(trip.broker_price)}</td>
                                                    </tr>
                                                ))}
                                                <tr className="bg-indigo-50/30 font-black text-indigo-900 border-t border-indigo-100">
                                                    <td colSpan="5" className="px-6 py-4 text-right text-xs">إجمالي المحدد:</td>
                                                    <td className="px-6 py-4 text-center text-xs text-indigo-600">{selectedTrips.length} رحلة</td>
                                                    <td className="px-6 py-4 text-left text-sm font-black text-indigo-700">{fmt(subtotal)}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </div>

                            {/* Right Side: Totals + Invoice Form - 4 columns */}
                            <div className="lg:col-span-4 space-y-6">
                                {/* Totals Summary */}
                                <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                                    <div className="bg-slate-900 px-6 py-5 text-white text-center shadow-inner">
                                        <p className="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">المستحق دفعه / Amount Payable</p>
                                        <h4 className="text-3xl font-black tracking-tight text-emerald-400">{fmt(totalAmount)}</h4>
                                    </div>
                                    <div className="p-5 space-y-3">
                                        <div className="flex justify-between text-sm font-bold">
                                            <span className="text-slate-500">المجموع الفرعي</span>
                                            <span className="text-slate-800">{fmt(subtotal)}</span>
                                        </div>
                                        <div className="flex justify-between text-sm font-bold">
                                            <span className="text-slate-500">ضريبة (15%)</span>
                                            <span className="text-rose-600">+{fmt(vat)}</span>
                                        </div>
                                        <div className="pt-3 border-t border-slate-100 flex justify-between items-center mt-2">
                                            <span className="text-xs font-black text-slate-700 uppercase">الصافي الإجمالي</span>
                                            <span className="text-xl font-black text-slate-800">{fmt(totalAmount)}</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Invoice Parameters Card */}
                                <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                                    <h3 className="text-sm font-bold text-slate-800 mb-6 border-b pb-2">
                                        خيارات إصدار الفاتورة
                                    </h3>
                                    <form onSubmit={submitInvoice} className="space-y-4">
                                        <div>
                                            <label className="text-xs font-bold text-slate-600 mb-1.5 block">تاريخ إصدار الفاتورة *</label>
                                            <input 
                                                type="date" 
                                                required
                                                className="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                                value={invoiceForm.data.invoice_date} 
                                                onChange={e => invoiceForm.setData('invoice_date', e.target.value)} 
                                            />
                                        </div>

                                        <div>
                                            <label className="text-xs font-bold text-slate-600 mb-1.5 block">طريقة الدفع *</label>
                                            <select 
                                                className="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                value={invoiceForm.data.payment_mode}
                                                onChange={e => {
                                                    invoiceForm.setData('payment_mode', e.target.value);
                                                    invoiceForm.setData('payment_account_id', '');
                                                }}
                                            >
                                                <option value="credit">آجل (Credit)</option>
                                                <option value="cash">نقدي (Cash)</option>
                                                <option value="bank">تحويل بنكي (Bank)</option>
                                            </select>
                                        </div>

                                        {/* Conditional: Cash → Show only Safebox, Bank → Show only Banks */}
                                        {invoiceForm.data.payment_mode === 'cash' && (
                                            <div>
                                                <label className="text-xs font-bold text-slate-600 mb-1.5 block">الصندوق (Cash Box) *</label>
                                                <select 
                                                    required
                                                    className="w-full rounded-xl border-slate-200 text-sm font-bold text-emerald-600 focus:ring-emerald-500 focus:border-emerald-500"
                                                    value={invoiceForm.data.payment_account_id}
                                                    onChange={e => invoiceForm.setData('payment_account_id', e.target.value)}
                                                >
                                                    <option value="">إختر الصندوق...</option>
                                                    {filteredPaymentAccounts.map(acc => (
                                                        <option key={acc.id} value={acc.id}>{acc.name} ({acc.code})</option>
                                                    ))}
                                                </select>
                                            </div>
                                        )}

                                        {invoiceForm.data.payment_mode === 'bank' && (
                                            <div>
                                                <label className="text-xs font-bold text-slate-600 mb-1.5 block">الحساب البنكي (Bank Account) *</label>
                                                <select 
                                                    required
                                                    className="w-full rounded-xl border-slate-200 text-sm font-bold text-blue-600 focus:ring-blue-500 focus:border-blue-500"
                                                    value={invoiceForm.data.payment_account_id}
                                                    onChange={e => invoiceForm.setData('payment_account_id', e.target.value)}
                                                >
                                                    <option value="">إختر البنك...</option>
                                                    {filteredPaymentAccounts.map(acc => (
                                                        <option key={acc.id} value={acc.id}>{acc.name} ({acc.code})</option>
                                                    ))}
                                                </select>
                                            </div>
                                        )}

                                        <div>
                                            <label className="text-xs font-bold text-slate-600 mb-1.5 block">ملاحظات / شروط</label>
                                            <textarea 
                                                placeholder="الدفع خلال 15 يوماً..."
                                                className="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 h-20 resize-none" 
                                                value={invoiceForm.data.notes} 
                                                onChange={e => invoiceForm.setData('notes', e.target.value)} 
                                            />
                                        </div>

                                        {invoiceForm.errors.message && (
                                            <div className="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold text-rose-700">
                                                {invoiceForm.errors.message}
                                            </div>
                                        )}

                                        <button
                                            type="submit"
                                            disabled={invoiceForm.processing || selectedTrips.length === 0}
                                            className="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-md transition-all disabled:opacity-50 disabled:pointer-events-none mt-2"
                                        >
                                            {invoiceForm.processing ? 'جاري الإصدار...' : 'إصدار الفاتورة المجمعة'}
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

const fmt = (num) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR' }).format(num || 0);
