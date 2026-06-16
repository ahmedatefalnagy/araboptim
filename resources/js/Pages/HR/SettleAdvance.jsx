import { Head, useForm, Link, router, usePage } from '@inertiajs/react';
import BackButton from '@/Components/BackButton';
import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Modal from '@/Components/Modal';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';

const fmt = (n) => (n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

export default function SettleAdvance({ auth, advance, settlements, expenseAccounts, cashAccounts, inputTaxAccountId, advanceAccountId, flash, vendors = [], unpaidInvoices = [] }) {
    const remaining = parseFloat(advance.amount) - parseFloat(advance.deducted_amount || 0);

    const { default_date } = usePage().props;

    const emptyLine = () => ({
        type: 'expense', invoice_no: '', invoice_date: default_date,
        vendor_name: '', description: '', amount: '', is_taxable: false, tax_rate: 15,
        expense_account_id: '', notes: ''
    });

    const [lines, setLines] = useState([emptyLine()]);

    const [editingSettlementId, setEditingSettlementId] = useState(null);

    const { data, setData, post, put, processing, errors } = useForm({
        settlement_date: default_date,
        notes: '',
        refund_account_id: '',
        refund_type: 'bank_cash',
        lines: []
    });

    const [showContactModal, setShowContactModal] = useState(false);
    const { data: contactData, setData: setContactData, post: postContact, processing: contactProcessing, errors: contactErrors, reset: resetContact } = useForm({
        name: '',
        type: 'supplier',
        phone: '',
        tax_number: '',
        notes: 'مورد مضاف من تصفية العهدة'
    });

    const submitContact = (e) => {
        e.preventDefault();
        postContact(route('contacts.store'), {
            onSuccess: () => {
                setShowContactModal(false);
                resetContact();
            }
        });
    };

    const updateLine = (idx, field, val) => {
        const updated = [...lines];
        updated[idx] = { ...updated[idx], [field]: val };
        if (field === 'is_taxable' && !val) updated[idx].tax_rate = 0;
        if (field === 'is_taxable' && val) updated[idx].tax_rate = 15;
        setLines(updated);
    };

    const addLine = () => setLines([...lines, emptyLine()]);
    const removeLine = (idx) => { if (lines.length > 1) setLines(lines.filter((_, i) => i !== idx)); };

    const [showInvoiceDropdown, setShowInvoiceDropdown] = useState(false);
    const [activeVendorIdx, setActiveVendorIdx] = useState(null);
    const [activeTypeIdx, setActiveTypeIdx] = useState(null);
    const [activeAccIdx, setActiveAccIdx] = useState(null);
    const [vendorSearch, setVendorSearch] = useState('');
    const [invoiceSearch, setInvoiceSearch] = useState('');
    const [accSearch, setAccSearch] = useState('');

    const addInvoiceLine = (invoiceId) => {
        if (!invoiceId) return;
        const inv = unpaidInvoices.find(i => i.id == invoiceId);
        if (!inv) return;
        
        const existingLines = lines.length === 1 && !lines[0].amount && !lines[0].description ? [] : lines;
        
        setLines([...existingLines, {
            type: 'purchase',
            invoice_no: inv.invoice_no,
            invoice_date: default_date,
            vendor_name: inv.contact?.name || '',
            description: `سداد فاتورة مشتريات آجلة رقم ${inv.invoice_no}`,
            amount: inv.total_amount,
            is_taxable: false,
            tax_rate: 0,
            expense_account_id: inv.contact?.account_id || '',
            notes: ''
        }]);
    };

    const calcLine = (l) => {
        const amt = parseFloat(l.amount) || 0;
        const tax = l.is_taxable ? amt * ((parseFloat(l.tax_rate) || 0) / 100) : 0;
        return { amount: amt, tax: Math.round(tax * 100) / 100, total: Math.round((amt + tax) * 100) / 100 };
    };

    const totals = lines.reduce((acc, l) => {
        const c = calcLine(l);
        return { expenses: acc.expenses + c.amount, tax: acc.tax + c.tax, total: acc.total + c.total };
    }, { expenses: 0, tax: 0, total: 0 });

    const diff = totals.total - remaining;
    const refundAmount = diff < 0 ? Math.abs(diff) : 0;
    const additionalAmount = diff > 0 ? diff : 0;

    const submit = (e) => {
        e.preventDefault();
        const payload = {
            settlement_date: data.settlement_date,
            notes: data.notes,
            refund_account_id: data.refund_account_id,
            refund_type: data.refund_type,
            lines: lines.map(l => ({
                ...l,
                amount: parseFloat(l.amount) || 0,
                is_taxable: !!l.is_taxable,
                tax_rate: l.is_taxable ? (parseFloat(l.tax_rate) || 15) : 0,
            }))
        };
        if (editingSettlementId) {
            router.put(route('hr.advances.settlement.update', editingSettlementId), payload, {
                preserveScroll: true,
                onSuccess: () => {
                    setEditingSettlementId(null);
                    setLines([emptyLine()]);
                    setData({
                        settlement_date: default_date,
                        notes: '',
                        refund_account_id: '',
                        refund_type: 'bank_cash',
                        lines: []
                    });
                }
            });
        } else {
            router.post(route('hr.advances.settlement.process', advance.id), payload, {
                preserveScroll: true,
            });
        }
    };

    const statusBadge = (s) => {
        const map = { draft: ['مسودة', 'bg-yellow-100 text-yellow-800'], approved: ['معتمدة', 'bg-green-100 text-green-800'] };
        const [label, cls] = map[s] || [s, 'bg-gray-100'];
        return <span className={`px-2 py-1 rounded-full text-xs font-bold ${cls}`}>{label}</span>;
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`تصفية عهدة - ${advance.employee?.name}`} />
            <div className="py-6 bg-gray-50 min-h-screen" dir="rtl">
                <div className="max-w-6xl mx-auto px-4 sm:px-6">
                    <datalist id="vendors-list">
                        {vendors?.map(v => <option key={v.id} value={v.name} />)}
                    </datalist>

                    <Modal show={showContactModal} onClose={() => setShowContactModal(false)} maxWidth="md">
                        <form onSubmit={submitContact} className="p-6" dir="rtl">
                            <h2 className="text-lg font-bold text-gray-900 mb-4">إضافة مورد جديد</h2>
                            <div className="space-y-4">
                                <div>
                                    <InputLabel value="اسم المورد *" />
                                    <TextInput 
                                        className="mt-1 block w-full" 
                                        value={contactData.name} 
                                        onChange={e => setContactData('name', e.target.value)} 
                                        required 
                                    />
                                    <InputError message={contactErrors.name} className="mt-2" />
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel value="رقم الهاتف" />
                                        <TextInput 
                                            className="mt-1 block w-full" 
                                            value={contactData.phone} 
                                            onChange={e => setContactData('phone', e.target.value)} 
                                        />
                                    </div>
                                    <div>
                                        <InputLabel value="الرقم الضريبي" />
                                        <TextInput 
                                            className="mt-1 block w-full" 
                                            value={contactData.tax_number} 
                                            onChange={e => setContactData('tax_number', e.target.value)} 
                                        />
                                    </div>
                                </div>
                            </div>
                            <div className="mt-6 flex justify-end gap-3">
                                <SecondaryButton onClick={() => setShowContactModal(false)}>إلغاء</SecondaryButton>
                                <PrimaryButton disabled={contactProcessing}>حفظ المورد</PrimaryButton>
                            </div>
                        </form>
                    </Modal>

                    {/* Header */}
                    <div className="flex items-center justify-between mb-6">
                        <div className="flex-1">
                            <h1 className="text-2xl font-extrabold text-gray-900">تصفية عهدة</h1>
                            <p className="text-sm text-gray-500 mt-1">تسجيل فواتير المصروفات والمشتريات وتصفية العهدة محاسبياً</p>
                        </div>
                        <div className="flex items-center gap-4">
                            <BackButton />
                        </div>
                    </div>

                    {flash?.success && <div className="mb-4 p-4 bg-green-100 text-green-800 rounded-lg font-bold">{flash.success}</div>}
                    {flash?.error && <div className="mb-4 p-4 bg-red-100 text-red-800 rounded-lg font-bold">{flash.error}</div>}
                    {errors?.message && <div className="mb-4 p-4 bg-red-100 text-red-800 rounded-lg font-bold">{errors.message}</div>}

                    {/* Advance Info Card */}
                    <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                        <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
                            <div>
                                <div className="text-xs text-gray-500 mb-1">الموظف</div>
                                <div className="font-bold text-gray-900">{advance.employee?.name}</div>
                                <div className="text-xs text-gray-400">{advance.reference_no}</div>
                            </div>
                            <div>
                                <div className="text-xs text-gray-500 mb-1">النوع</div>
                                <div className="font-bold">{advance.type === 'custody' ? 'عهدة' : advance.type === 'advance' ? 'سلفة' : 'مكافأة'}</div>
                            </div>
                            <div>
                                <div className="text-xs text-gray-500 mb-1">مبلغ العهدة</div>
                                <div className="font-bold text-blue-700 text-lg">{fmt(advance.amount)}</div>
                            </div>
                            <div>
                                <div className="text-xs text-gray-500 mb-1">المصروف سابقاً</div>
                                <div className="font-bold text-red-600">{fmt(advance.deducted_amount)}</div>
                            </div>
                            <div>
                                <div className="text-xs text-gray-500 mb-1">المتبقي للتصفية</div>
                                <div className="font-bold text-green-700 text-lg">{fmt(remaining)}</div>
                            </div>
                            <div className="border-r border-gray-100 pr-4">
                                <div className="text-xs text-gray-500 mb-1">حساب الصرف (المصدر)</div>
                                <div className="font-bold text-emerald-800">{advance.payment_account ? `${advance.payment_account.code} - ${advance.payment_account.name}` : 'غير محدد'}</div>
                            </div>
                        </div>
                    </div>

                    {/* Settlement Form */}
                    <form onSubmit={submit}>
                        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                            <h2 className="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                بنود التصفية (فواتير المصروفات والمشتريات)
                            </h2>

                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5 pb-4 border-b">
                                <div>
                                    <label className="block text-xs font-bold text-gray-600 mb-1">تاريخ التصفية *</label>
                                    <input type="date" className="w-full rounded-lg border-gray-300 text-sm" value={data.settlement_date} onChange={e => setData('settlement_date', e.target.value)} required />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-600 mb-1">معالجة المبلغ المتبقي (إن وجد)</label>
                                    <select className="w-full rounded-lg border-gray-300 text-sm" value={data.refund_type} onChange={e => setData('refund_type', e.target.value)}>
                                        <option value="bank_cash">إيداع نقدي / بنكي (إرجاع للشركة)</option>
                                        <option value="rollover">ترحيل إلى عهدة جديدة تلقائياً</option>
                                    </select>
                                </div>
                                {data.refund_type === 'bank_cash' ? (
                                    <div>
                                        <label className="block text-xs font-bold text-gray-600 mb-1">حساب النقد (للفرق / المرتجع) *</label>
                                        <select className="w-full rounded-lg border-gray-300 text-sm" value={data.refund_account_id} onChange={e => setData('refund_account_id', e.target.value)} required={data.refund_type === 'bank_cash'}>
                                            <option value="">اختر حساب</option>
                                            {cashAccounts?.map(a => <option key={a.id} value={a.id}>[{a.code}] {a.name}</option>)}
                                        </select>
                                    </div>
                                ) : (
                                    <div>
                                        <label className="block text-xs font-bold text-gray-400 mb-1">حساب النقد (معطل للترحيل)</label>
                                        <input className="w-full rounded-lg border-gray-200 text-sm bg-gray-50 text-gray-400" value="سيتم إنشاء عهدة جديدة للمتبقي" disabled />
                                    </div>
                                )}
                                <div>
                                    <label className="block text-xs font-bold text-gray-600 mb-1">ملاحظات</label>
                                    <input className="w-full rounded-lg border-gray-300 text-sm" value={data.notes} onChange={e => setData('notes', e.target.value)} placeholder="ملاحظات عامة" />
                                </div>
                            </div>

                            {/* Lines Table */}
                            <div className="overflow-x-visible">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="bg-gray-50 text-gray-600">
                                            <th className="px-2 py-2 text-right text-xs font-bold w-8">#</th>
                                            <th className="px-2 py-2 text-right text-xs font-bold">النوع</th>
                                            <th className="px-2 py-2 text-right text-xs font-bold">رقم الفاتورة</th>
                                            <th className="px-2 py-2 text-right text-xs font-bold">التاريخ</th>
                                            <th className="px-2 py-2 text-right text-xs font-bold">المورد</th>
                                            <th className="px-2 py-2 text-right text-xs font-bold">البيان</th>
                                            <th className="px-2 py-2 text-right text-xs font-bold">المبلغ</th>
                                            <th className="px-2 py-2 text-center text-xs font-bold">ضريبة؟</th>
                                            <th className="px-2 py-2 text-right text-xs font-bold">%</th>
                                            <th className="px-2 py-2 text-right text-xs font-bold">الضريبة</th>
                                            <th className="px-2 py-2 text-right text-xs font-bold">الإجمالي</th>
                                            <th className="px-2 py-2 text-right text-xs font-bold">حساب المصروف</th>
                                            <th className="px-2 py-2 text-center text-xs font-bold w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {lines.map((line, idx) => {
                                            const c = calcLine(line);
                                            return (
                                                <tr key={idx} className="border-b border-gray-100 hover:bg-blue-50/30">
                                                    <td className="px-2 py-2 text-gray-400 font-bold">{idx + 1}</td>
                                                    <td className="px-1 py-1">
                                                        <div 
                                                            className="relative group outline-none" 
                                                            tabIndex="0" 
                                                            onBlur={(e) => {
                                                                if (!e.currentTarget.contains(e.relatedTarget)) {
                                                                    setTimeout(() => setActiveTypeIdx(null), 250);
                                                                }
                                                            }}
                                                        >
                                                            <div 
                                                                className="w-full rounded-md border border-gray-300 text-xs py-1.5 px-2 bg-white cursor-pointer hover:border-blue-400 transition-all flex justify-between items-center"
                                                                onClick={() => setActiveTypeIdx(activeTypeIdx === idx ? null : idx)}
                                                            >
                                                                <span>{line.type === 'expense' ? 'مصروف' : 'مشتريات'}</span>
                                                                <svg className="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" /></svg>
                                                            </div>
                                                            {activeTypeIdx === idx && (
                                                                <div className="absolute z-[110] left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-xl py-1 min-w-[100px]">
                                                                    <div 
                                                                        onClick={() => { updateLine(idx, 'type', 'expense'); setActiveTypeIdx(null); }}
                                                                        className="px-4 py-2 text-xs hover:bg-blue-50 cursor-pointer"
                                                                    >مصروف</div>
                                                                    <div 
                                                                        onClick={() => { updateLine(idx, 'type', 'purchase'); setActiveTypeIdx(null); }}
                                                                        className="px-4 py-2 text-xs hover:bg-blue-50 cursor-pointer"
                                                                    >مشتريات</div>
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="px-1 py-1">
                                                        <input className="w-full rounded border-gray-300 text-xs py-1.5" value={line.invoice_no} onChange={e => updateLine(idx, 'invoice_no', e.target.value)} placeholder="رقم الفاتورة" />
                                                    </td>
                                                    <td className="px-1 py-1">
                                                        <input type="date" className="w-full rounded border-gray-300 text-xs py-1.5" value={line.invoice_date} onChange={e => updateLine(idx, 'invoice_date', e.target.value)} required />
                                                    </td>
                                                    <td className="px-1 py-1">
                                                        <div className="relative">
                                                            <input 
                                                                className="w-full rounded-md border-gray-300 text-xs py-1.5 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm" 
                                                                value={line.vendor_name} 
                                                                onFocus={() => { setActiveVendorIdx(idx); setVendorSearch(line.vendor_name); }}
                                                                onBlur={() => setTimeout(() => setActiveVendorIdx(null), 250)}
                                                                onChange={e => {
                                                                    updateLine(idx, 'vendor_name', e.target.value);
                                                                    setVendorSearch(e.target.value);
                                                                }} 
                                                                placeholder="🔍 ابحث عن مورد..." 
                                                            />
                                                            {activeVendorIdx === idx && (
                                                                <div className="absolute z-[110] left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-2xl max-h-56 overflow-y-auto min-w-[220px] py-1 animate-in fade-in slide-in-from-top-1">
                                                                    <div className="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50 border-b border-gray-100 mb-1">الموردين المسجلين</div>
                                                                    {vendors
                                                                        .filter(v => v.name.toLowerCase().includes(vendorSearch.toLowerCase()))
                                                                        .map(v => (
                                                                            <div 
                                                                                key={v.id} 
                                                                                onClick={() => {
                                                                                    updateLine(idx, 'vendor_name', v.name);
                                                                                    setActiveVendorIdx(null);
                                                                                }}
                                                                                className="px-4 py-2 text-xs hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors flex items-center gap-2 border-b border-gray-50 last:border-0"
                                                                            >
                                                                                <div className="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px] font-bold">
                                                                                    {v.name.charAt(0)}
                                                                                </div>
                                                                                {v.name}
                                                                            </div>
                                                                        ))}
                                                                    {vendors.filter(v => v.name.toLowerCase().includes(vendorSearch.toLowerCase())).length === 0 && (
                                                                        <div className="px-4 py-3 text-xs text-gray-400 italic text-center">لا يوجد نتائج.. يمكنك كتابة الاسم يدوياً</div>
                                                                    )}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="px-1 py-1">
                                                        <input className="w-full rounded border-gray-300 text-xs py-1.5" value={line.description} onChange={e => updateLine(idx, 'description', e.target.value)} placeholder="وصف البند" required />
                                                    </td>
                                                    <td className="px-1 py-1">
                                                        <input type="number" step="0.01" className="w-20 rounded border-gray-300 text-xs py-1.5 font-bold text-blue-700" value={line.amount} onChange={e => updateLine(idx, 'amount', e.target.value)} placeholder="0.00" required />
                                                    </td>
                                                    <td className="px-1 py-1 text-center">
                                                        <input type="checkbox" className="rounded border-gray-300 text-blue-600 w-4 h-4" checked={line.is_taxable} onChange={e => updateLine(idx, 'is_taxable', e.target.checked)} />
                                                    </td>
                                                    <td className="px-1 py-1">
                                                        {line.is_taxable ? (
                                                            <input type="number" className="w-14 rounded border-gray-300 text-xs py-1.5" value={line.tax_rate} onChange={e => updateLine(idx, 'tax_rate', e.target.value)} />
                                                        ) : <span className="text-gray-400 text-xs">-</span>}
                                                    </td>
                                                    <td className="px-2 py-1 text-xs font-bold text-orange-600">{fmt(c.tax)}</td>
                                                    <td className="px-2 py-1 text-xs font-bold text-gray-900">{fmt(c.total)}</td>
                                                    <td className="px-1 py-1">
                                                        <div 
                                                            className="relative group outline-none" 
                                                            tabIndex="0" 
                                                            onBlur={(e) => {
                                                                if (!e.currentTarget.contains(e.relatedTarget)) {
                                                                    setTimeout(() => setActiveAccIdx(null), 250);
                                                                }
                                                            }}
                                                        >
                                                            <div 
                                                                className="w-full rounded-md border border-gray-300 text-xs py-1.5 px-2 bg-white cursor-pointer hover:border-blue-400 transition-all flex justify-between items-center"
                                                                onClick={() => setActiveAccIdx(activeAccIdx === idx ? null : idx)}
                                                            >
                                                                <span className="truncate max-w-[100px]">
                                                                    {expenseAccounts.find(a => a.id == line.expense_account_id)?.name || 'اختر حساب'}
                                                                </span>
                                                                <svg className="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" /></svg>
                                                            </div>
                                                            {activeAccIdx === idx && (
                                                                <div className="absolute z-[110] left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-xl py-1 min-w-[200px] max-h-64 overflow-hidden flex flex-col">
                                                                    <div className="p-2 border-b bg-gray-50">
                                                                        <input 
                                                                            type="text" 
                                                                            className="w-full rounded border-gray-300 text-[10px] py-1 px-2 focus:ring-blue-500 focus:border-blue-500" 
                                                                            placeholder="ابحث بالاسم أو الكود.." 
                                                                            value={accSearch}
                                                                            autoFocus
                                                                            onChange={(e) => setAccSearch(e.target.value)}
                                                                            onKeyDown={(e) => e.stopPropagation()}
                                                                        />
                                                                    </div>
                                                                    <div className="overflow-y-auto flex-1">
                                                                        {expenseAccounts
                                                                            ?.filter(a => 
                                                                                a.name.toLowerCase().includes(accSearch.toLowerCase()) || 
                                                                                a.code.toLowerCase().includes(accSearch.toLowerCase())
                                                                            )
                                                                            .map(a => (
                                                                                <div 
                                                                                    key={a.id} 
                                                                                    onClick={() => { updateLine(idx, 'expense_account_id', a.id); setActiveAccIdx(null); setAccSearch(''); }}
                                                                                    className="px-4 py-2 text-xs hover:bg-blue-50 cursor-pointer border-b border-gray-50 last:border-0 flex items-center justify-between group"
                                                                                >
                                                                                    <span className="group-hover:text-blue-700 transition-colors">{a.name}</span>
                                                                                    <span className="text-gray-400 font-mono text-[10px]">[{a.code}]</span>
                                                                                </div>
                                                                            ))}
                                                                    </div>
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="px-1 py-1 text-center">
                                                        {lines.length > 1 && (
                                                            <button type="button" onClick={() => removeLine(idx)} className="text-red-500 hover:text-red-700 text-lg font-bold">×</button>
                                                        )}
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>

                            <div className="mt-4 flex gap-3">
                                <button type="button" onClick={addLine} className="bg-blue-50 text-blue-700 px-4 py-2.5 rounded-lg text-sm font-bold hover:bg-blue-100 border border-blue-200 transition-colors shadow-sm">
                                    + إضافة بند يدوي
                                </button>
                                {unpaidInvoices?.length > 0 && (
                                    <div className="relative flex-1 max-w-sm">
                                        <div className="relative">
                                            <input 
                                                className="w-full bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2.5 rounded-lg text-sm font-bold placeholder-emerald-600 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm"
                                                value={invoiceSearch}
                                                onFocus={() => setShowInvoiceDropdown(true)}
                                                onBlur={() => setTimeout(() => setShowInvoiceDropdown(false), 250)}
                                                onChange={(e) => setInvoiceSearch(e.target.value)}
                                                placeholder="🔍 ابحث عن فاتورة لسدادها..."
                                            />
                                            {showInvoiceDropdown && (
                                                <div className="absolute bottom-full mb-2 left-0 right-0 bg-white border border-gray-200 rounded-xl shadow-2xl z-50 max-h-64 overflow-y-auto py-2 animate-in fade-in slide-in-from-bottom-2">
                                                    <div className="px-3 py-1.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider bg-gray-50 border-b border-gray-100 mb-1">اختر فاتورة مشتريات للسداد</div>
                                                    {unpaidInvoices
                                                        .filter(inv => 
                                                            inv.invoice_no.toLowerCase().includes(invoiceSearch.toLowerCase()) || 
                                                            inv.contact?.name.toLowerCase().includes(invoiceSearch.toLowerCase())
                                                        )
                                                        .map(inv => (
                                                            <div 
                                                                key={inv.id} 
                                                                onClick={() => {
                                                                    addInvoiceLine(inv.id);
                                                                    setShowInvoiceDropdown(false);
                                                                    setInvoiceSearch('');
                                                                }}
                                                                className="px-4 py-3 hover:bg-emerald-50 cursor-pointer transition-colors border-b border-gray-50 last:border-0 group"
                                                            >
                                                                <div className="flex justify-between items-center mb-1">
                                                                    <span className="font-bold text-gray-900 text-xs group-hover:text-emerald-700 transition-colors">{inv.invoice_no}</span>
                                                                    <span className="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-md text-[10px] font-extrabold">{fmt(inv.total_amount)}</span>
                                                                </div>
                                                                <div className="text-[11px] text-gray-500 flex items-center gap-1">
                                                                    <svg className="w-3 h-3 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" /></svg>
                                                                    {inv.contact?.name}
                                                                </div>
                                                            </div>
                                                        ))}
                                                    {unpaidInvoices.filter(inv => 
                                                        inv.invoice_no.toLowerCase().includes(invoiceSearch.toLowerCase()) || 
                                                        inv.contact?.name.toLowerCase().includes(invoiceSearch.toLowerCase())
                                                    ).length === 0 && (
                                                        <div className="px-4 py-3 text-xs text-gray-400 italic text-center">لا توجد فواتير مطابقة</div>
                                                    )}
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Summary */}
                        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                            <h3 className="text-lg font-bold text-gray-900 mb-4">ملخص التصفية</h3>
                            <div className="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                                <div className="bg-gray-50 rounded-lg p-4 text-center">
                                    <div className="text-xs text-gray-500 mb-1">إجمالي المصروفات (قبل الضريبة)</div>
                                    <div className="text-xl font-bold text-gray-800">{fmt(totals.expenses)}</div>
                                </div>
                                <div className="bg-orange-50 rounded-lg p-4 text-center">
                                    <div className="text-xs text-orange-600 mb-1">إجمالي الضريبة</div>
                                    <div className="text-xl font-bold text-orange-700">{fmt(totals.tax)}</div>
                                </div>
                                <div className="bg-blue-50 rounded-lg p-4 text-center">
                                    <div className="text-xs text-blue-600 mb-1">الإجمالي شامل الضريبة</div>
                                    <div className="text-xl font-bold text-blue-800">{fmt(totals.total)}</div>
                                </div>
                            </div>

                            <div className="border-t pt-4">
                                <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    <div className="bg-green-50 rounded-lg p-4 text-center">
                                        <div className="text-xs text-green-600 mb-1">مبلغ العهدة المتبقي</div>
                                        <div className="text-xl font-bold text-green-700">{fmt(remaining)}</div>
                                    </div>
                                    {refundAmount > 0 && (
                                        <div className="bg-emerald-50 rounded-lg p-4 text-center border-2 border-emerald-300">
                                            <div className="text-xs text-emerald-600 mb-1">مبلغ مرتجع للشركة ✓</div>
                                            <div className="text-xl font-bold text-emerald-700">{fmt(refundAmount)}</div>
                                            <div className="text-[10px] text-emerald-500 mt-1">الموظف يعيد هذا المبلغ</div>
                                        </div>
                                    )}
                                    {additionalAmount > 0 && (
                                        <div className="bg-red-50 rounded-lg p-4 text-center border-2 border-red-300">
                                            <div className="text-xs text-red-600 mb-1">مبلغ إضافي للموظف !</div>
                                            <div className="text-xl font-bold text-red-700">{fmt(additionalAmount)}</div>
                                            <div className="text-[10px] text-red-500 mt-1">يجب دفعه للموظف</div>
                                        </div>
                                    )}
                                    {diff === 0 && totals.total > 0 && (
                                        <div className="bg-blue-50 rounded-lg p-4 text-center border-2 border-blue-300">
                                            <div className="text-xs text-blue-600 mb-1">متطابق ✓</div>
                                            <div className="text-lg font-bold text-blue-700">لا يوجد فرق</div>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Journal Preview */}
                            {totals.total > 0 && (
                                <div className="mt-6 border-t pt-4">
                                    <h4 className="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                        <svg className="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                        معاينة القيد المحاسبي
                                    </h4>
                                    <table className="w-full text-sm border rounded-lg overflow-hidden">
                                        <thead><tr className="bg-purple-50"><th className="px-3 py-2 text-right text-xs font-bold">الحساب</th><th className="px-3 py-2 text-right text-xs font-bold">مدين</th><th className="px-3 py-2 text-right text-xs font-bold">دائن</th></tr></thead>
                                        <tbody>
                                            {/* Expense accounts - debit */}
                                            {(() => {
                                                const grouped = {};
                                                lines.forEach(l => {
                                                    if (!l.expense_account_id || !l.amount) return;
                                                    const acc = expenseAccounts?.find(a => a.id == l.expense_account_id);
                                                    const key = l.expense_account_id;
                                                    if (!grouped[key]) grouped[key] = { name: acc ? `[${acc.code}] ${acc.name}` : 'حساب مصروف', amount: 0 };
                                                    grouped[key].amount += parseFloat(l.amount) || 0;
                                                });
                                                return Object.values(grouped).map((g, i) => (
                                                    <tr key={'e' + i} className="border-b"><td className="px-3 py-2">{g.name}</td><td className="px-3 py-2 font-bold text-green-700">{fmt(g.amount)}</td><td className="px-3 py-2">-</td></tr>
                                                ));
                                            })()}
                                            {totals.tax > 0 && <tr className="border-b"><td className="px-3 py-2">ضريبة القيمة المضافة - مدخلات</td><td className="px-3 py-2 font-bold text-green-700">{fmt(totals.tax)}</td><td className="px-3 py-2">-</td></tr>}
                                            {refundAmount > 0 && (
                                                <tr className="border-b">
                                                    <td className="px-3 py-2">
                                                        {data.refund_type === 'rollover' 
                                                            ? '[1106] عهد وسلف الموظفين (العهدة الجديدة المرحل إليها)' 
                                                            : (cashAccounts?.find(a => a.id == data.refund_account_id)?.name 
                                                                ? `[${cashAccounts.find(a => a.id == data.refund_account_id).code}] ${cashAccounts.find(a => a.id == data.refund_account_id).name} (إرجاع للشركة)` 
                                                                : 'حساب النقد (مرتجع للشركة)')}
                                                    </td>
                                                    <td className="px-3 py-2 font-bold text-green-700">{fmt(refundAmount)}</td>
                                                    <td className="px-3 py-2">-</td>
                                                </tr>
                                            )}
                                            <tr className="border-b bg-red-50/30">
                                                <td className="px-3 py-2">[1106] عهد وسلف الموظفين (العهدة الحالية)</td>
                                                <td className="px-3 py-2">-</td>
                                                <td className="px-3 py-2 font-bold text-red-600">
                                                    {fmt(totals.total + refundAmount <= remaining ? totals.total + refundAmount : remaining)}
                                                </td>
                                            </tr>
                                            {additionalAmount > 0 && (
                                                <tr className="border-b bg-red-50/30">
                                                    <td className="px-3 py-2">
                                                        {cashAccounts?.find(a => a.id == data.refund_account_id)?.name 
                                                            ? `[${cashAccounts.find(a => a.id == data.refund_account_id).code}] ${cashAccounts.find(a => a.id == data.refund_account_id).name} (دفع إضافي للموظف)` 
                                                            : 'حساب النقد (دفع إضافي)'}
                                                    </td>
                                                    <td className="px-3 py-2">-</td>
                                                    <td className="px-3 py-2 font-bold text-red-600">{fmt(additionalAmount)}</td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>

                        <div className="flex gap-3 mb-8">
                            <button type="submit" disabled={processing || totals.total <= 0} className={`${editingSettlementId ? 'bg-blue-600 hover:bg-blue-700' : 'bg-green-600 hover:bg-green-700'} text-white px-8 py-3 rounded-xl font-bold text-base disabled:opacity-50 shadow-lg`}>
                                {processing ? 'جاري الحفظ...' : editingSettlementId ? '✓ حفظ التعديلات' : '✓ تنفيذ التصفية وإنشاء القيد'}
                            </button>
                            {editingSettlementId && (
                                <button type="button" onClick={() => {
                                    setEditingSettlementId(null);
                                    setLines([emptyLine()]);
                                    setData({
                                        settlement_date: default_date,
                                        notes: '',
                                        refund_account_id: '',
                                        lines: []
                                    });
                                }} className="bg-orange-100 text-orange-700 px-6 py-3 rounded-xl font-bold hover:bg-orange-200">
                                    إلغاء التعديل
                                </button>
                            )}
                            <Link href={route('hr.advances')} className="bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-bold hover:bg-gray-300">رجوع للعهد</Link>
                        </div>
                    </form>

                    {/* Previous Settlements */}
                    {settlements?.length > 0 && (
                        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
                            <h3 className="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                التصفيات السابقة
                            </h3>
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="bg-gray-50 text-gray-600">
                                            <th className="px-3 py-2 text-right text-xs font-bold">رقم التصفية</th>
                                            <th className="px-3 py-2 text-right text-xs font-bold">التاريخ</th>
                                            <th className="px-3 py-2 text-right text-xs font-bold">المصروفات</th>
                                            <th className="px-3 py-2 text-right text-xs font-bold">الضريبة</th>
                                            <th className="px-3 py-2 text-right text-xs font-bold">الإجمالي</th>
                                            <th className="px-3 py-2 text-right text-xs font-bold">الحالة</th>
                                            <th className="px-3 py-2 text-center text-xs font-bold">الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {settlements.map(s => (
                                            <tr key={s.id} className="border-b hover:bg-gray-50 transition-colors">
                                                <td className="px-3 py-1.5 font-bold text-gray-900">{s.settlement_no}</td>
                                                <td className="px-3 py-1.5 text-gray-600 text-xs">{s.settlement_date ? s.settlement_date.substring(0, 10) : ''}</td>
                                                <td className="px-3 py-1.5 text-xs">{fmt(s.total_expenses)}</td>
                                                <td className="px-3 py-1.5 text-orange-600 text-xs">{fmt(s.total_tax)}</td>
                                                <td className="px-3 py-1.5 font-bold text-blue-800 text-xs">{fmt(s.total_amount)}</td>
                                                <td className="px-3 py-1.5">{statusBadge(s.status)}</td>
                                                <td className="px-3 py-1.5 text-center">
                                                    <div className="flex justify-center gap-1">
                                                        <button 
                                                            type="button" 
                                                            onClick={() => {
                                                                setEditingSettlementId(s.id);
                                                                const sDate = s.settlement_date ? s.settlement_date.substring(0, 10) : '';
                                                                setData({
                                                                    ...data,
                                                                    settlement_date: sDate,
                                                                    notes: s.notes || '',
                                                                    refund_type: s.refund_type || 'bank_cash',
                                                                    refund_account_id: s.refund_account_id || '',
                                                                });
                                                                
                                                                const mapped = s.lines.map(l => ({
                                                                    type: l.type || 'expense',
                                                                    invoice_no: l.invoice_no || '',
                                                                    invoice_date: l.invoice_date ? l.invoice_date.substring(0, 10) : sDate,
                                                                    vendor_name: l.vendor_name || '',
                                                                    description: l.description || '',
                                                                    amount: String(l.amount || 0),
                                                                    is_taxable: Boolean(l.is_taxable),
                                                                    tax_rate: String(l.tax_rate || 0),
                                                                    expense_account_id: String(l.expense_account_id || ''),
                                                                    notes: l.notes || ''
                                                                }));
                                                                
                                                                setLines(mapped);
                                                                window.scrollTo({ top: 0, behavior: 'smooth' });
                                                            }}
                                                            className="p-1 text-blue-600 hover:bg-blue-50 rounded border border-blue-100"
                                                            title="تحميل للتعديل"
                                                        >
                                                            <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                        </button>
                                                        <button 
                                                            type="button" 
                                                            onClick={() => {
                                                                if(confirm('حذف التصفية نهائياً؟')) {
                                                                    router.delete(route('hr.advances.settlement.destroy', s.id));
                                                                }
                                                            }}
                                                            className="p-1 text-red-600 hover:bg-red-50 rounded border border-red-100"
                                                            title="حذف"
                                                        >
                                                            <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
