import { Head, useForm, usePage, router, Link } from '@inertiajs/react';
import BackButton from '@/Components/BackButton';
import { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

const statusColors = {
    open: 'bg-orange-100 text-orange-800',
    partially_settled: 'bg-blue-100 text-blue-800',
    settled: 'bg-green-100 text-green-800'
};

const formatNumber = (num) => {
        const numStr = (num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const arabicToEnglish = {'٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9'};
        return numStr.replace(/[٠-٩]/g, d => arabicToEnglish[d]);
    };

export default function Advances({ auth, advances, employees, paymentAccounts, flash, filters = {}, fiscal_start_month = 1 }) {
    const [showForm, setShowForm] = useState(false);
    // Unified modal state
    const [activeModal, setActiveModal] = useState(null);
    const [filterStatus, setFilterStatus] = useState(filters.status || 'all');
    const [filterType, setFilterType] = useState(filters.type || 'all');
    const [filterEmployee, setFilterEmployee] = useState(filters.employee_id || '');
    const [dateFrom, setDateFrom] = useState(filters.date_from || '');
    const [dateTo, setDateTo] = useState(filters.date_to || '');
    const [sortOrder, setSortOrder] = useState(filters.sort_order || 'desc');
    const { default_date } = usePage().props;

    const { data, setData, post, processing, reset, errors } = useForm({
        employee_id: '', type: 'advance',
        reference_no: 'SLF-' + Math.floor(Math.random() * 90000 + 10000),
        date: default_date, amount: '', payment_account_id: '', purpose: '', notes: '',
        debit_description: '', credit_description: '',
        attachment: null, bypass_restriction: false
    });

    const { expenseData, setExpenseData, post: postExpense, processing: expenseProcessing, reset: resetExpense } = useForm({
        advance_id: '', invoice_no: '', expense_date: default_date,
        description: '', amount: '', is_taxable: false, tax_rate: 15, expense_account_id: '', type: 'expense', notes: ''
    });

    const { settleData, setSettleData, post: postSettle, processing: settleProcessing, reset: resetSettle } = useForm({
        action: 'settle', notes: ''
    });

    const applyFilters = () => {
        const params = new URLSearchParams();
        if (filterStatus && filterStatus !== 'all') params.set('status', filterStatus);
        if (filterType && filterType !== 'all') params.set('type', filterType);
        if (filterEmployee) params.set('employee_id', filterEmployee);
        if (dateFrom) params.set('date_from', dateFrom);
        if (dateTo) params.set('date_to', dateTo);
        if (sortOrder) params.set('sort_order', sortOrder);
        window.location = route('hr.advances') + (params.toString() ? '?' + params.toString() : '');
    };

    const resetFilters = () => {
        setFilterStatus('all');
        setFilterType('all');
        setFilterEmployee('');
        setDateFrom('');
        setSortOrder('desc');
        setDateTo('');
        window.location = route('hr.advances');
    };

    const toggleSort = () => {
        const newOrder = sortOrder === 'desc' ? 'asc' : 'desc';
        setSortOrder(newOrder);
        
        // Immediate apply for sort
        const params = new URLSearchParams(window.location.search);
        params.set('sort_order', newOrder);
        window.location = route('hr.advances') + '?' + params.toString();
    };

    

    const submit = (e) => {
        e.preventDefault();
        post(route('hr.advances.store'), { onSuccess: () => { reset(); setShowForm(false); } });
    };

    // Removed duplicate handleOpenExpense block

    // Removed duplicate handleOpenSettle block

    const handleOpenExpense = (advance) => {
        try {
            console.log('Open expense for:', advance);
            setActiveModal({ type: 'expense', advance });
            setExpenseData({
                advance_id: advance.id,
                invoice_no: '',
                expense_date: default_date,
                description: '',
                amount: '',
                is_taxable: false,
                tax_rate: 15,
                expense_account_id: '',
                type: 'expense',
                notes: ''
            });
        } catch (err) {
            console.error('Error opening expense modal:', err);
            alert('Error opening expense form');
        }
    };

    const handleOpenSettle = (advance) => {
        try {
            console.log('Open settle for:', advance);
            setActiveModal({ type: 'settle', advance });
            setSettleData({ action: 'settle', notes: '' });
        } catch (err) {
            console.error('Error opening settle modal:', err);
            alert('Error opening settle form');
        }
    };

    const handleCloseExpense = () => {
        setActiveModal(null);
    };

    const submitExpense = (e) => {
        e.preventDefault();
        postExpense(route('hr.advances.expense'), {
            onSuccess: () => {
                handleCloseExpense();
                window.location.reload();
            }
        });
    };

    const handleCloseSettle = () => {
        setActiveModal(null);
    };

    const submitSettle = (e) => {
        e.preventDefault();
        postSettle(route('hr.advances.settle', activeModal?.advance?.id), {
            onSuccess: () => {
                handleCloseSettle();
                window.location.reload();
            }
        });
    };

    const normalizeDigits = (str) => {
        if (typeof str !== 'string') return str;
        const persian = {'۰':'0','۱':'1','۲':'2','۳':'3','۴':'4','۵':'5','۶':'6','۷':'7','۸':'8','۹':'9'};
        const arabicIndic = {'٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9'};
        let s = str;
        s = s.replace(/[۰-۹]/g, d => persian[d] ?? d);
        s = s.replace(/[٠-٩]/g, d => arabicIndic[d] ?? d);
        return s;
    };

    const getExpenseAccounts = () => {
        return (paymentAccounts || []).filter(a => a.code?.startsWith('5') || a.code?.startsWith('4')) || paymentAccounts?.slice(0, 20) || [];
    };

    const stats = {
        all: advances.length,
        open: advances.filter(a => a.computed_status === 'open').length,
        settled: advances.filter(a => a.computed_status === 'settled').length,
    };

    const totals = advances.reduce((acc, adv) => ({
        total: acc.total + (parseFloat(adv.amount) || 0),
        spent: acc.spent + (parseFloat(adv.deducted_amount) || 0),
        remaining: acc.remaining + (parseFloat(adv.remaining) || 0)
    }), { total: 0, spent: 0, remaining: 0 });

    const typeLabel = (type) => ({ advance: 'سلفة', custody: 'عهدة', bonus: 'مكافأة' }[type] || type);
    const statusLabel = (status) => ({ open: 'مفتوحة', partially_settled: 'مسواة جزئياً', settled: 'مسواة' }[status] || status);

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="السلف والعهد" />
            <div className="py-8 bg-gray-50 min-h-screen" dir="rtl">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center mb-6">
                        <div className="flex-1">
                            <h1 className="text-2xl font-bold text-gray-900">السلف والعهد</h1>
                            <p className="text-sm text-gray-500 mt-1">ادارة عهد الموظفين</p>
                        </div>
                        <div className="flex items-center gap-4">
                            <button onClick={() => setShowForm(!showForm)} className="btn-primary px-6 py-2.5 shadow-xl shadow-blue-900/10">
                                + تسجيل عهدة / سلفة / مكافأة
                            </button>
                            <BackButton />
                        </div>
                    </div>

                    {flash?.success && <div className="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{flash.success}</div>}
                    {flash?.error && <div className="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">{flash.error}</div>}

                    <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
                        <div className="flex flex-wrap gap-3 mb-3">
                            <button onClick={() => setFilterStatus('all')} className={`px-4 py-2 rounded-lg text-sm font-bold ${filterStatus === 'all' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600'}`}>
                                الكل ({stats.all})
                            </button>
                            <button onClick={() => setFilterStatus('open')} className={`px-4 py-2 rounded-lg text-sm font-bold ${filterStatus === 'open' ? 'bg-orange-500 text-white' : 'bg-orange-100 text-orange-700'}`}>
                                مفتوحة ({stats.open})
                            </button>
                            <button onClick={() => setFilterStatus('settled')} className={`px-4 py-2 rounded-lg text-sm font-bold ${filterStatus === 'settled' ? 'bg-green-500 text-white' : 'bg-green-100 text-green-700'}`}>
                                مسواة ({stats.settled})
                            </button>
                        </div>

                    <div className="grid grid-cols-2 md:grid-cols-4 gap-3 border-t pt-3">
                            <select value={filterType} onChange={e => setFilterType(e.target.value)} className="rounded-lg border-gray-300 text-sm">
                                <option value="all">كل الانواع</option>
                                <option value="advance">سلفة</option>
                                <option value="custody">عهدة</option>
                                <option value="bonus">مكافأة</option>
                            </select>
                            <select value={filterEmployee} onChange={e => setFilterEmployee(e.target.value)} className="rounded-lg border-gray-300 text-sm">
                                <option value="">كل الموظفين</option>
                                {employees?.map(e => <option key={e.id} value={e.id}>{e.name}</option>)}
                            </select>
                            
                            <input type="date" value={dateFrom} onChange={e => setDateFrom(e.target.value)} className="rounded-lg border-gray-300 text-sm" placeholder="من تاريخ" />
                            <input type="date" value={dateTo} onChange={e => setDateTo(e.target.value)} className="rounded-lg border-gray-300 text-sm" placeholder="الى تاريخ" />
                            <div className="flex gap-2">
                                <button onClick={applyFilters} className="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-bold">بحث</button>
                                <button onClick={resetFilters} className="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold">اعادة</button>
                            </div>
                        </div>
                    </div>

                    {showForm && (
                        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-4">
                            <h3 className="text-lg font-bold mb-4">تسجيل عهدة / سلفة / مكافأة</h3>
                            {errors?.employee_id && (
                                <div className="mb-4 p-3 bg-red-100 border border-red-200 text-red-800 rounded-lg text-sm font-bold">
                                    {errors.employee_id}
                                </div>
                            )}
                            <form onSubmit={submit}>
                                <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <label className="block text-xs font-bold text-gray-600 mb-1">الموظف *</label>
                                        <select className="w-full rounded-lg border-gray-300 text-sm" value={data.employee_id} onChange={e => setData('employee_id', e.target.value)} required>
                                            <option value="">اختر الموظف</option>
                                            {employees?.map(e => <option key={e.id} value={e.id}>[{e.employee_no}] {e.name}</option>)}
                                        </select>
                                    </div>
                                    <div>
                                        <label className="block text-xs font-bold text-gray-600 mb-1">النوع *</label>
                                        <select className="w-full rounded-lg border-gray-300 text-sm" value={data.type} onChange={e => setData('type', e.target.value)}>
                                            <option value="advance">سلفة نقدية</option>
                                            <option value="custody">عهدة</option>
                                            <option value="bonus">مكافأة</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className="block text-xs font-bold text-gray-600 mb-1">المبلغ (SAR) *</label>
                                        <input type="number" className="w-full rounded-lg border-gray-300 text-sm font-bold text-blue-600" value={data.amount} onChange={e => setData('amount', normalizeDigits(e.target.value))} required />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-bold text-gray-600 mb-1">حساب الصرف *</label>
                                        <select className="w-full rounded-lg border-gray-300 text-sm" value={data.payment_account_id} onChange={e => setData('payment_account_id', e.target.value)} required>
                                            <option value="">اختر</option>
                                            {paymentAccounts?.map(acc => <option key={acc.id} value={acc.id}>[{acc.code}] {acc.name}</option>)}
                                        </select>
                                    </div>
                                </div>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <label className="block text-xs font-bold text-gray-600 mb-1">رقم المرجع</label>
                                        <input className="w-full rounded-lg border-gray-300 text-sm" value={data.reference_no} onChange={e => setData('reference_no', e.target.value)} />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-bold text-gray-600 mb-1">التاريخ</label>
                                        <input type="date" className="w-full rounded-lg border-gray-300 text-sm" value={data.date} onChange={e => setData('date', e.target.value)} required />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-bold text-gray-600 mb-1">سند العهدة الموقع والمختوم (مرفق)</label>
                                        <input type="file" accept=".pdf,image/*" className="w-full text-xs" onChange={e => setData('attachment', e.target.files[0])} />
                                    </div>
                                </div>
                                <div className="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label className="block text-xs font-bold text-gray-600 mb-1">وصف المدين (اختياري)</label>
                                        <input className="w-full rounded-lg border-gray-300 text-sm" value={data.debit_description} onChange={e => setData('debit_description', e.target.value)} placeholder="وصف الطرف المدين" />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-bold text-gray-600 mb-1">وصف الدائن (اختياري)</label>
                                        <input className="w-full rounded-lg border-gray-300 text-sm" value={data.credit_description} onChange={e => setData('credit_description', e.target.value)} placeholder="وصف الطرف الدائن" />
                                    </div>
                                </div>
                                {auth.user?.role === 'admin' && data.type === 'custody' && (
                                    <div className="flex items-center gap-2 mb-4">
                                        <input type="checkbox" id="bypass_restriction" checked={data.bypass_restriction} onChange={e => setData('bypass_restriction', e.target.checked)} className="rounded border-gray-300 text-blue-600" />
                                        <label htmlFor="bypass_restriction" className="text-xs font-bold text-gray-700">تجاوز قيود عدم تصفية العهد القديمة للموظف (استثناء معتمد من الإدارة)</label>
                                    </div>
                                )}
                                <div className="flex gap-3">
                                    <button type="submit" disabled={processing} className="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700">حفظ</button>
                                    <button type="button" onClick={() => setShowForm(false)} className="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-bold">الغاء</button>
                                </div>
                            </form>
                        </div>
                    )}

                    <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
                        <div className="grid grid-cols-3 gap-4 p-4 bg-gray-50 border-b">
                            <div className="text-center">
                                <div className="text-xs text-gray-500">اجمالي العهد</div>
                                <div className="text-lg font-bold text-gray-800">{formatNumber(totals.total)}</div>
                            </div>
                            <div className="text-center">
                                <div className="text-xs text-gray-500">المصروف</div>
                                <div className="text-lg font-bold text-red-600">{formatNumber(totals.spent)}</div>
                            </div>
                            <div className="text-center">
                                <div className="text-xs text-gray-500">المتبقي</div>
                                <div className="text-lg font-bold text-green-600">{formatNumber(totals.remaining)}</div>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <table className="min-w-full">
                            <thead className="bg-gray-100">
                                <tr>
                                    <th 
                                        className="px-4 py-3 text-right text-xs font-bold text-gray-500 cursor-pointer hover:bg-gray-200 group transition-colors"
                                        onClick={toggleSort}
                                    >
                                        <div className="flex items-center gap-1">
                                            التاريخ
                                            {sortOrder === 'desc' ? (
                                                <svg className="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" /></svg>
                                            ) : (
                                                <svg className="w-3 h-3 text-blue-600 rotate-180" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" /></svg>
                                            )}
                                        </div>
                                    </th>
                                    <th className="px-4 py-3 text-right text-xs font-bold text-gray-500">الموظف</th>
                                    <th className="px-4 py-3 text-right text-xs font-bold text-gray-500">النوع</th>
                                    <th className="px-4 py-3 text-right text-xs font-bold text-gray-500">المبلغ</th>
                                    <th className="px-4 py-3 text-right text-xs font-bold text-gray-500">المصروف</th>
                                    <th className="px-4 py-3 text-right text-xs font-bold text-gray-500">المتبقي</th>
                                    <th className="px-4 py-3 text-right text-xs font-bold text-gray-500">الحالة</th>
                                    <th className="px-4 py-3 text-right text-xs font-bold text-gray-500">تحكم</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {advances.length === 0 ? (
                                    <tr><td colSpan="8" className="px-4 py-8 text-center text-gray-400">لا توجد عهد</td></tr>
                                ) : advances.map(adv => (
                                    <tr key={adv.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 text-xs text-gray-600 font-bold">
                                            {adv.date?.substring(0, 10)}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="font-bold text-gray-900 flex items-center gap-2">
                                                <span>{adv.employee?.name || '-'}</span>
                                                {adv.attachment_path && (
                                                    <a 
                                                        href={`/storage/${adv.attachment_path}`} 
                                                        target="_blank" 
                                                        rel="noopener noreferrer" 
                                                        className="text-purple-600 hover:text-purple-800 transition-colors inline-flex items-center gap-0.5 text-xs bg-purple-50 px-1.5 py-0.5 rounded border border-purple-100"
                                                        title="تحميل سند العهدة الموقع والمرفوع"
                                                    >
                                                        📎 سند موقع
                                                    </a>
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3 text-sm">{typeLabel(adv.type)}</td>
                                        <td className="px-4 py-3 font-bold text-blue-700">{formatNumber(adv.amount)}</td>
                                        <td className="px-4 py-3 font-bold text-red-600">{formatNumber(adv.deducted_amount)}</td>
                                        <td className="px-4 py-3 font-bold text-green-700">{formatNumber(adv.remaining)}</td>
                                        <td className="px-4 py-3">
                                            <span className={`px-2 py-1 rounded-full text-xs font-bold ${statusColors[adv.computed_status] || 'bg-gray-100'}`}>
                                                {statusLabel(adv.computed_status)}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex flex-wrap gap-1">
                                                {adv.type === 'custody' && (
                                                    <a
                                                        href={'/hr/advances/' + adv.id + '/settlement' + window.location.search}
                                                        className="bg-emerald-50 text-emerald-700 border border-emerald-200 px-1.5 py-0.5 rounded text-[10px] font-bold hover:bg-emerald-100 transition-all">
                                                        {adv.computed_status === 'settled' ? 'تعديل' : 'تصفية'}
                                                    </a>
                                                )}

                                                {adv.type === 'advance' && (
                                                    <button 
                                                        onClick={() => {
                                                            const msg = adv.status === 'settled' 
                                                                ? 'هذه السلفة مخصومة بالفعل. تحويلها لعهدة سيلغي قيد الخصم. استمرار؟'
                                                                : 'تحويل هذه السلفة إلى عهدة؟';
                                                            if(confirm(msg)) {
                                                                router.post('/hr/advances/' + adv.id + '/convert-to-custody', {}, {
                                                                    onSuccess: () => window.location.reload(),
                                                                    onError: () => window.location.reload()
                                                                });
                                                            }
                                                        }}
                                                        className="bg-blue-100 text-blue-700 px-2 py-1 rounded-lg text-[11px] font-bold hover:bg-blue-200 transition-all min-w-[60px]"
                                                    >
                                                        🔄 عهدة
                                                    </button>
                                                )}

                                                {adv.type === 'advance' && adv.computed_status !== 'settled' && (
                                                    <button 
                                                        onClick={() => {
                                                            if(confirm('تأكيد خصم المتبقي من الراتب الآن؟')) {
                                                                router.post('/hr/advances/' + adv.id + '/deduct-from-salary', {}, {
                                                                    onSuccess: () => window.location.reload(),
                                                                    onError: () => window.location.reload()
                                                                });
                                                            }
                                                        }}
                                                        className="bg-orange-100 text-orange-700 px-2 py-1 rounded-lg text-[11px] font-bold hover:bg-orange-200 transition-all min-w-[60px]"
                                                    >
                                                        💸 خصم
                                                    </button>
                                                )}

                                                <button 
                                                    onClick={() => {
                                                        const msg = adv.status === 'settled' 
                                                            ? 'حذف هذه السلفة سيلغي أثر الخصم المالي أيضاً. متأكد؟'
                                                            : 'حذف هذه العملية نهائياً؟';
                                                        if(confirm(msg)) {
                                                            router.delete('/hr/advances/' + adv.id, {
                                                                onSuccess: () => window.location.reload(),
                                                                onError: () => window.location.reload()
                                                            });
                                                        }
                                                    }}
                                                    className="bg-red-100 text-red-700 px-2 py-1 rounded-lg text-[11px] font-bold hover:bg-red-200 transition-all min-w-[60px]"
                                                >
                                                    🗑️ حذف
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {activeModal?.type === 'expense' && activeModal?.advance && (
                        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-[9999]">
                            <div className="bg-white rounded-xl p-6 w-full max-w-lg">
                                <h3 className="text-lg font-bold mb-4">Expense Modal</h3>
                                <p className="text-sm text-gray-600 mb-4">Advance: {activeModal?.advance?.id ?? '-'}</p>
                                <form onSubmit={submitExpense}>
                                    <div className="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label className="block text-xs font-bold text-gray-600 mb-1">Invoice No</label>
                                            <input className="w-full rounded-lg border-gray-300 text-sm" value={expenseData.invoice_no} onChange={e => setExpenseData('invoice_no', e.target.value)} />
                                        </div>
                                        <div>
                                            <label className="block text-xs font-bold text-gray-600 mb-1">Date *</label>
                                            <input type="date" className="w-full rounded-lg border-gray-300 text-sm" value={expenseData.expense_date} onChange={e => setExpenseData('expense_date', e.target.value)} required />
                                        </div>
                                        <div>
                                            <label className="block text-xs font-bold text-gray-600 mb-1">Amount *</label>
                                        <input type="number" className="w-full rounded-lg border-gray-300 text-sm font-bold" value={expenseData.amount} onChange={e => setExpenseData('amount', normalizeDigits(e.target.value))} required />
                                        </div>
                                        <div>
                                            <label className="block text-xs font-bold text-gray-600 mb-1">Type</label>
                                            <select className="w-full rounded-lg border-gray-300 text-sm" value={expenseData.type} onChange={e => setExpenseData('type', e.target.value)}>
                                                <option value="expense">Expense</option>
                                                <option value="purchase">Purchase</option>
                                                <option value="voucher">Voucher</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label className="block text-xs font-bold text-gray-600 mb-1">Description *</label>
                                            <input className="w-full rounded-lg border-gray-300 text-sm" value={expenseData.description} onChange={e => setExpenseData('description', e.target.value)} required />
                                        </div>
                                        <div>
                                            <label className="block text-xs font-bold text-gray-600 mb-1">Account</label>
                                            <select className="w-full rounded-lg border-gray-300 text-sm" value={expenseData.expense_account_id} onChange={e => setExpenseData('expense_account_id', e.target.value)}>
                                                <option value="">Select</option>
                                                {getExpenseAccounts().map(a => <option key={a.id} value={a.id}>[{a.code}] {a.name}</option>)}
                                            </select>
                                        </div>
                                    </div>
                                    <div className="flex gap-3">
                                        <button type="submit" disabled={expenseProcessing} className="bg-blue-500 text-white px-6 py-2 rounded-lg font-bold">Submit</button>
                                        <button type="button" onClick={() => setActiveModal(null)} className="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-bold">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    )}
                    {activeModal?.type === 'settle' && activeModal?.advance && (
                        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-[9999]">
                            <div className="bg-white rounded-xl p-6 w-full max-w-md">
                                <h3 className="text-lg font-bold mb-4">Settle: {activeModal?.advance?.employee?.name}</h3>
                                <p className="text-sm text-gray-600 mb-4">Remaining: {formatNumber(activeModal?.advance?.remaining)}</p>
                                <form onSubmit={submitSettle}>
                                    <div className="mb-4">
                                        <label className="block text-sm font-bold text-gray-700 mb-2">Action</label>
                                        <div className="flex gap-4">
                                            <label className="flex items-center gap-2">
                                                <input type="radio" name="action" value="settle" checked={settleData.action === 'settle'} onChange={e => setSettleData('action', e.target.value)} />
                                                <span>Settle</span>
                                            </label>
                                            <label className="flex items-center gap-2">
                                                <input type="radio" name="action" value="close" checked={settleData.action === 'close'} onChange={e => setSettleData('action', e.target.value)} />
                                                <span>Close</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div className="mb-4">
                                        <label className="block text-sm font-bold text-gray-700 mb-1">Notes</label>
                                        <textarea className="w-full rounded-lg border-gray-300 text-sm" value={settleData.notes} onChange={e => setSettleData('notes', e.target.value)} rows="3"></textarea>
                                    </div>
                                    <div className="flex gap-3">
                                        <button type="submit" disabled={settleProcessing} className="bg-green-600 text-white px-6 py-2 rounded-lg font-bold">Confirm</button>
                                        <button type="button" onClick={() => setActiveModal(null)} className="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-bold">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
