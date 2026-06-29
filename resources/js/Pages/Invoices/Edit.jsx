import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Edit({ auth, invoice, type, contacts, items, warehouses, paymentAccounts = [], costCenters = [], workOrders = [], fiscalYears = [], selectedFiscalYearId = null }) {
    const initialLines = invoice.lines && invoice.lines.length > 0
        ? invoice.lines.map(line => ({
            id: line.id,
            item_id: line.item_id,
            quantity: parseFloat(line.quantity),
            unit_price: parseFloat(line.unit_price),
            tax_rate: parseFloat(line.tax_rate),
            subtotal: parseFloat(line.subtotal),
            tax_amount: parseFloat(line.tax_amount),
            total: parseFloat(line.total),
            cost_center_id: line.cost_center_id || ''
        }))
        : [{ id: Date.now(), item_id: '', quantity: 1, unit_price: 0, tax_rate: 15, subtotal: 0, tax_amount: 0, total: 0 }];

    const { data, setData, post, processing, errors } = useForm({
        _method: 'PUT',
        type: invoice.type || type,
        parent_document_id: invoice.parent_document_id || '',
        contact_id: invoice.contact_id || '',
        cost_center_id: invoice.cost_center_id || '',
        warehouse_id: invoice.warehouse_id || (warehouses.length > 0 ? warehouses[0].id : ''),
        invoice_no: invoice.invoice_no || '',
        invoice_date: invoice.invoice_date || '',
        payment_mode: invoice.payment_mode || 'credit',
        payment_account_id: invoice.payment_account_id || '',
        notes: invoice.notes || '',
        attachment: null,
        lines: initialLines,
    });

    const calculateTotals = (linesArray) => {
        let total_base = 0;
        let total_tax = 0;
        linesArray.forEach(line => {
            total_base += parseFloat(line.subtotal || 0);
            total_tax += parseFloat(line.tax_amount || 0);
        });
        return { total_base, total_tax, total_amount: total_base + total_tax };
    };

    const updateLineItem = (index, field, value) => {
        const newLines = [...data.lines];
        const line = { ...newLines[index] };

        if (field === 'item_id') {
            const selectedItem = items.find(i => i.id == value);
            line.item_id = value;
            if (selectedItem) {
                line.unit_price = type === 'purchase' ? selectedItem.cost_price : selectedItem.price;
                line.tax_rate = selectedItem.tax_rate;
            } else {
                line.unit_price = 0;
                line.tax_rate = 15;
            }
        } else {
            line[field] = value;
        }

        const qty = parseFloat(line.quantity || 0);
        const price = parseFloat(line.unit_price || 0);
        const tr = parseFloat(line.tax_rate || 0);

        line.subtotal = qty * price;
        line.tax_amount = line.subtotal * (tr / 100);
        line.total = line.subtotal + line.tax_amount;

        newLines[index] = line;
        setData('lines', newLines);
    };

    const addLine = () => {
        setData('lines', [...data.lines, { id: Date.now(), item_id: '', quantity: 1, unit_price: 0, tax_rate: 15, subtotal: 0, tax_amount: 0, total: 0 }]);
    };

    const removeLine = (index) => {
        if (data.lines.length > 1) {
            const newLines = [...data.lines];
            newLines.splice(index, 1);
            setData('lines', newLines);
        }
    };

    const totals = calculateTotals(data.lines);

    const titles = {
        sale: 'فاتورة مبيعات ضريبية',
        sale_return: 'مردود مبيعات',
        purchase: 'فاتورة مشتريات (استلام مخزون)',
        purchase_return: 'مردود مشتريات للمورد',
        sale_quotation: 'عرض سعر (مبيعات)',
        sale_order: 'أمر بيع إداري',
        purchase_quotation: 'طلب عرض سعر مشتريات',
        purchase_order: 'أمر شراء مبدئي',
        work_order: 'طلب صيانة / خدمة',
        goods_receipt: 'تسوية المستودع - إضافة بضاعة',
        goods_issue: 'تسوية المستودع - إضافة تالف',
    };

    const contactLabel = String(type).includes('sale') ? 'العميل' : 
                         (String(type).includes('work') ? 'العميل' : 
                         (type === 'goods_receipt' ? 'جهة الاستلام (المورد/العميل/أخرى)' :
                         (type === 'goods_issue' ? 'جهة الصرف (المستلم/الموظف/العميل)' : 'المورد')));
                         
    const gradient = String(type).includes('sale') ? 'from-emerald-600 to-teal-700' : 
                     (String(type).includes('work') ? 'from-orange-500 to-red-600' : 
                     (['goods_receipt', 'goods_issue'].includes(type) ? 'from-purple-600 to-indigo-700' : 'from-blue-600 to-indigo-700'));
                     
    const accent = String(type).includes('sale') ? 'emerald' : 
                   (String(type).includes('work') ? 'orange' : 
                   (['goods_receipt', 'goods_issue'].includes(type) ? 'purple' : 'blue'));

    const submit = (e) => {
        e.preventDefault();
        post(route('invoices.update', invoice.id));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">تعديل {titles[type]}</h2>}
        >
            <Head title={`تعديل ${titles[type]}`} />

            <div className="py-12 bg-gray-50 min-h-screen" dir="rtl">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">

                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h1 className={`text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r ${gradient}`}>
                                تعديل {titles[type]}
                            </h1>
                            <p className="mt-2 text-sm text-gray-600">
                                تعديل بيانات المستند وإعادة ترحيل القيود وتأثير المخزون تلقائياً.
                            </p>
                        </div>
                        <Link href={route('invoices.index', { type })} className="text-gray-600 hover:text-gray-900 px-5 py-2.5 bg-white rounded-xl shadow-sm border border-gray-200 font-bold transition-all hover:bg-gray-50 flex items-center gap-2">
                            العودة للسجل
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        </Link>
                    </div>

                    <form onSubmit={submit} className="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">

                        <div className={`h-2 bg-gradient-to-r ${gradient} w-full`}></div>

                        <div className="p-8">
                            <div className="grid grid-cols-1 md:grid-cols-5 gap-6 bg-gray-50 p-6 rounded-2xl border border-gray-100 mb-8">

                                <div className="md:col-span-1">
                                    <label className="block text-sm font-bold text-gray-800 mb-2">{contactLabel} الأساسي <span className="text-red-500">*</span></label>
                                    <select
                                        required
                                        className={`w-full rounded-xl border-gray-200 shadow-sm focus:border-${accent}-500 focus:ring-${accent}-500 font-semibold text-gray-800 bg-white mb-4`}
                                        value={data.contact_id}
                                        onChange={e => setData('contact_id', e.target.value)}
                                    >
                                        <option value="">== اختر {contactLabel} ==</option>
                                        {contacts.map(contact => (
                                            <option key={contact.id} value={contact.id}>{contact.name}</option>
                                        ))}
                                    </select>
                                    {errors.contact_id && <div className="text-red-500 text-xs mt-2">{errors.contact_id}</div>}

                                    {/* Payment Mode Selection */}
                                    {!['work_order', 'goods_receipt', 'goods_issue'].includes(type) && (
                                        <div className="mt-4 p-4 bg-white rounded-xl border border-gray-100 shadow-inner">
                                            <label className="block text-xs font-black text-gray-500 uppercase tracking-widest mb-3">طريقة الدفع للمستند</label>
                                            <div className="flex gap-2 p-1 bg-gray-100 rounded-lg">
                                                <button
                                                    type="button"
                                                    onClick={() => { setData('payment_mode', 'credit'); setData('payment_account_id', ''); }}
                                                    className={`flex-1 py-2 rounded-md text-xs font-bold transition-all ${data.payment_mode === 'credit' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                                                >
                                                    آجل (على الحساب)
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => { setData('payment_mode', 'cash'); setData('payment_account_id', ''); }}
                                                    className={`flex-1 py-2 rounded-md text-xs font-bold transition-all ${data.payment_mode === 'cash' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                                                >
                                                    نقدي (كاش)
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => { setData('payment_mode', 'bank'); setData('payment_account_id', ''); }}
                                                    className={`flex-1 py-2 rounded-md text-xs font-bold transition-all ${data.payment_mode === 'bank' ? 'bg-white text-sky-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                                                >
                                                    تحويل بنكي (بنك)
                                                </button>
                                            </div>

                                            {(data.payment_mode === 'cash' || data.payment_mode === 'bank') && (
                                                <div className="mt-4 animate-in fade-in slide-in-from-top-2 duration-300">
                                                    <label className="block text-[10px] font-bold text-gray-400 mb-1">
                                                        {data.payment_mode === 'bank' ? 'اختر الحساب البنكي' : 'اختر الصندوق النقدي'}
                                                    </label>
                                                    <select
                                                        required={data.payment_mode === 'cash' || data.payment_mode === 'bank'}
                                                        className="w-full rounded-lg border-gray-200 text-xs font-bold focus:ring-emerald-500 focus:border-emerald-500"
                                                        value={data.payment_account_id}
                                                        onChange={e => setData('payment_account_id', e.target.value)}
                                                    >
                                                        <option value="">{data.payment_mode === 'bank' ? '-- اختر الحساب البنكي --' : '-- اختر الصندوق النقدي --'}</option>
                                                        {paymentAccounts
                                                            .filter(acc => {
                                                                const code = String(acc.code || '');
                                                                if (data.payment_mode === 'bank') return code.startsWith('112') || (code.startsWith('1') && /بنك|bank/i.test(acc.name || ''));
                                                                return code.startsWith('111') || (code.startsWith('1') && /صندوق|نقد|cash/i.test(acc.name || ''));
                                                            })
                                                            .map(acc => (
                                                                <option key={acc.id} value={acc.id}>{acc.code} - {acc.name}</option>
                                                            ))}
                                                    </select>
                                                    {errors.payment_account_id && <div className="text-red-500 text-[10px] mt-1">{errors.payment_account_id}</div>}
                                                </div>
                                            )}
                                        </div>
                                    )}
                                </div>

                                <div className="md:col-span-1 border-r border-gray-100 pr-4">
                                    <label className="block text-sm font-bold text-gray-800 mb-2">
                                        المستودع المرجعي
                                        {['sale', 'purchase', 'sale_return', 'purchase_return'].includes(type) ? <span className="text-red-500">*</span> : null}
                                    </label>
                                    <select
                                        required={['sale', 'purchase', 'sale_return', 'purchase_return'].includes(type)}
                                        className={`w-full rounded-xl border-gray-200 shadow-sm focus:border-${accent}-500 focus:ring-${accent}-500 bg-white`}
                                        value={data.warehouse_id}
                                        onChange={e => setData('warehouse_id', e.target.value)}
                                    >
                                        <option value="">== اختر المستودع ==</option>
                                        {warehouses.map(wh => (
                                            <option key={wh.id} value={wh.id}>{wh.name}</option>
                                        ))}
                                    </select>
                                    {errors.warehouse_id && <div className="text-red-500 text-xs mt-2">{errors.warehouse_id}</div>}

                                    <label className="block text-sm font-bold text-gray-800 mt-4 mb-2">مركز التكلفة (اختياري)</label>
                                    <select
                                        className={`w-full rounded-xl border-gray-200 shadow-sm focus:border-${accent}-500 focus:ring-${accent}-500 bg-white`}
                                        value={data.cost_center_id}
                                        onChange={e => setData('cost_center_id', e.target.value)}
                                    >
                                        <option value="">== بدون مركز تكلفة ==</option>
                                         {costCenters.map(cc => (
                                             <option key={cc.id} value={cc.id}>{cc.name}</option>
                                         ))}
                                    </select>
                                    {errors.cost_center_id && <div className="text-red-500 text-xs mt-2">{errors.cost_center_id}</div>}

                                    {['sale_quotation', 'purchase_quotation'].includes(type) && (
                                        <>
                                            <label className="block text-sm font-bold text-gray-800 mt-4 mb-2">ربط بأمر الشغل (اختياري)</label>
                                            <select
                                                className={`w-full rounded-xl border-gray-200 shadow-sm focus:border-${accent}-500 focus:ring-${accent}-500 bg-white`}
                                                value={data.parent_document_id}
                                                onChange={e => setData('parent_document_id', e.target.value)}
                                            >
                                                <option value="">== بدون أمر شغل ==</option>
                                                {workOrders.map(wo => (
                                                    <option key={wo.id} value={wo.id}>{wo.invoice_no}</option>
                                                ))}
                                            </select>
                                            {errors.parent_document_id && <div className="text-red-500 text-xs mt-2">{errors.parent_document_id}</div>}
                                        </>
                                    )}
                                </div>

                                <div className="md:col-span-1 border-r border-gray-100 pr-4">
                                    <label className="block text-sm font-bold text-gray-800 mb-2">رقم المستند الوثائقي</label>
                                    <input
                                        type="text"
                                        required
                                        className={`w-full rounded-xl border-gray-200 shadow-sm focus:border-${accent}-500 focus:ring-${accent}-500 font-mono bg-white`}
                                        value={data.invoice_no}
                                        onChange={e => setData('invoice_no', e.target.value)}
                                    />
                                    {errors.invoice_no && <div className="text-red-500 text-xs mt-2">{errors.invoice_no}</div>}
                                </div>

                                <div className="md:col-span-1 border-r border-gray-100 pr-4">
                                    <label className="block text-sm font-bold text-gray-800 mb-2">تاريخ الإصدار <span className="text-red-500">*</span></label>
                                    <input
                                        type="date"
                                        required
                                        className={`w-full rounded-xl border-gray-200 shadow-sm focus:border-${accent}-500 focus:ring-${accent}-500 bg-white`}
                                        value={data.invoice_date}
                                        onChange={e => setData('invoice_date', e.target.value)}
                                    />
                                    {errors.invoice_date && <div className="text-red-500 text-xs mt-2">{errors.invoice_date}</div>}
                                </div>

                                <div className="md:col-span-1 border-r border-gray-100 pr-4">
                                    <label className="block text-sm font-bold text-gray-800 mb-2">
                                        {type === 'purchase' ? 'فاتورة الشراء الأصلية' : 
                                         (type === 'sale' ? 'سند التسليم الموقع' : 
                                         (type === 'goods_receipt' ? 'سند الاستلام الموقع والمختوم' : 
                                         (type === 'goods_issue' ? 'سند الصرف الموقع والمختوم' : 'المرفق المساعد')))}
                                    </label>
                                    <input
                                        type="file"
                                        className="w-full rounded-xl border border-gray-200 p-2 shadow-sm text-xs bg-white"
                                        onChange={e => setData('attachment', e.target.files[0])}
                                        accept=".pdf,.png,.jpg,.jpeg"
                                    />
                                    {invoice.attachment_path && (
                                        <div className="mt-1">
                                            <a 
                                                href={invoice.attachment_path} 
                                                target="_blank" 
                                                className="text-[10px] text-blue-600 font-bold underline hover:text-blue-800"
                                            >
                                                تنزيل/عرض المرفق الحالي
                                            </a>
                                        </div>
                                    )}
                                    <p className="mt-1 text-[10px] text-gray-400">PDF, PNG, JPG (حتى 5 ميجابايت)</p>
                                    {errors.attachment && <div className="text-red-500 text-xs mt-2">{errors.attachment}</div>}
                                </div>
                            </div>

                            <div className="border border-gray-200 rounded-2xl overflow-hidden shadow-sm mb-8">
                                <div className="bg-gray-50/80 px-6 py-4 border-b border-gray-200 flex justify-between items-center backdrop-blur-sm">
                                    <h3 className="font-bold text-gray-800 text-lg flex items-center gap-2">
                                        <svg className={`w-5 h-5 text-${accent}-600`} fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                        سطور المنتجات والخدمات
                                    </h3>
                                    <button
                                        type="button"
                                        onClick={addLine}
                                        className={`text-white bg-${accent}-600 px-4 py-2 rounded-xl text-sm font-bold hover:bg-${accent}-700 transition-colors shadow-sm flex items-center gap-1`}
                                    >
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" /></svg>
                                        إضافة سطر
                                    </button>
                                </div>
                                <div className="overflow-x-auto">
                                    <table className="w-full text-right text-sm">
                                        <thead className="bg-white text-gray-500 border-b border-gray-100">
                                            <tr>
                                                <th className="px-6 py-4 font-bold w-2/5">الصنف / المنتج</th>
                                                <th className="px-4 py-4 font-bold w-32">الكمية</th>
                                                {!['work_order', 'goods_receipt', 'goods_issue'].includes(type) && (
                                                    <>
                                                        <th className="px-4 py-4 font-bold w-40">السعر الإفرادي (SAR)</th>
                                                        <th className="px-4 py-4 font-bold w-24">الضريبة %</th>
                                                        <th className="px-4 py-4 font-bold w-40">مجموع السطر</th>
                                                    </>
                                                )}
                                                <th className="px-4 py-4 font-bold w-16 text-center">إزالة</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-50 bg-gray-50/20">
                                            {data.lines.map((line, index) => (
                                                <tr key={line.id} className="hover:bg-white transition-colors duration-150">
                                                    <td className="px-6 py-4">
                                                        <select
                                                            required
                                                            className={`w-full rounded-lg border-gray-200 text-sm focus:border-${accent}-500 focus:ring-${accent}-500`}
                                                            value={line.item_id}
                                                            onChange={e => updateLineItem(index, 'item_id', e.target.value)}
                                                        >
                                                            <option value="">-- ابحث عن الصنف --</option>
                                                            {items.map(i => (
                                                                <option key={i.id} value={i.id}>
                                                                    {i.sku ? `[${i.sku}] ` : ''} {i.name} {i.track_inventory ? '📦' : '⚙️'}
                                                                </option>
                                                            ))}
                                                        </select>
                                                        {errors[`lines.${index}.item_id`] && <div className="text-red-500 text-xs mt-2">{errors[`lines.${index}.item_id`]}</div>}
                                                    </td>
                                                    <td className="px-4 py-4">
                                                        <input
                                                            type="number" inputMode="decimal" step="0.01" min="0.01" required
                                                            className={`w-full rounded-lg border-gray-200 text-sm focus:border-${accent}-500 focus:ring-${accent}-500 text-center font-bold`}
                                                            value={line.quantity}
                                                            onChange={e => updateLineItem(index, 'quantity', parseFloat(e.target.value) || 0)}
                                                        />
                                                    </td>
                                                    {!['work_order', 'goods_receipt', 'goods_issue'].includes(type) && (
                                                        <>
                                                            <td className="px-4 py-4">
                                                                <input
                                                                    type="number" inputMode="decimal" step="0.01" min="0" required
                                                                    className={`w-full rounded-lg border-gray-200 text-sm focus:border-${accent}-500 focus:ring-${accent}-500 text-right font-mono font-bold text-${accent}-700`}
                                                                    value={line.unit_price}
                                                                    onChange={e => updateLineItem(index, 'unit_price', parseFloat(e.target.value) || 0)}
                                                                />
                                                            </td>
                                                            <td className="px-4 py-4">
                                                                <div className="text-gray-600 bg-gray-100 px-3 py-2 rounded-lg text-center font-bold font-mono">
                                                                    {line.tax_rate}%
                                                                </div>
                                                            </td>
                                                            <td className="px-4 py-4">
                                                                <div className={`bg-${accent}-50 text-${accent}-800 px-3 py-2 rounded-lg font-mono font-extrabold`}>
                                                                    {line.total.toFixed(2)}
                                                                </div>
                                                            </td>
                                                        </>
                                                    )}
                                                    <td className="px-4 py-4 text-center">
                                                        <button
                                                            type="button"
                                                            onClick={() => removeLine(index)}
                                                            className="text-gray-400 hover:text-red-600 hover:bg-red-50 p-2 rounded-lg transition-colors"
                                                            disabled={data.lines.length === 1}
                                                        >
                                                            <svg className="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {errors.lines && <div className="text-red-500 text-sm bg-red-50 p-4 rounded-xl mb-6">{errors.lines}</div>}

                            <div className="flex flex-col md:flex-row justify-between items-start gap-8">
                                <div className="w-full md:w-1/2">
                                    <label className="block text-sm font-bold text-gray-800 mb-2">ملاحظات المستند</label>
                                    <textarea
                                        className={`w-full rounded-2xl border-gray-200 shadow-sm focus:border-${accent}-500 focus:ring-${accent}-500 bg-gray-50/50`}
                                        value={data.notes}
                                        onChange={e => setData('notes', e.target.value)}
                                        rows="5"
                                        placeholder="البيان، شروط الدفع، موقع التسليم، الخلاصة..."
                                    />
                                </div>

                                {!['work_order', 'goods_receipt', 'goods_issue'].includes(type) && (
                                    <div className={`w-full md:w-1/3 bg-white p-7 rounded-3xl shadow-lg border-2 border-${accent}-100 relative overflow-hidden`}>
                                        <div className={`absolute top-0 right-0 w-full h-1 bg-${accent}-500`}></div>
                                        <h4 className="text-lg font-bold text-gray-800 mb-6 border-b border-gray-100 pb-3">ملخص المبالغ</h4>

                                        <div className="flex justify-between items-center mb-4 text-gray-600">
                                            <span className="font-semibold text-sm">مجموع المبالغ (غير شامل الضريبة):</span>
                                            <span className="font-mono font-bold text-base">{totals.total_base.toFixed(2)}</span>
                                        </div>
                                        <div className="flex justify-between items-center mb-6 text-gray-600 border-b border-gray-100 pb-6">
                                            <span className="font-semibold text-sm">إجمالي ضريبة القيمة المضافة:</span>
                                            <span className="font-mono font-bold text-base">{totals.total_tax.toFixed(2)}</span>
                                        </div>
                                        <div className="flex justify-between items-center">
                                            <span className={`font-black text-xl text-${accent}-900`}>الصافي الإجمالي:</span>
                                            <div className="text-left">
                                                <span className={`block font-black text-3xl text-${accent}-700 font-mono`}>
                                                    {totals.total_amount.toFixed(2)}
                                                </span>
                                                <span className="text-gray-400 text-xs font-bold uppercase tracking-wider block mt-1">SAR</span>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>

                        <div className="p-6 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <button
                                type="submit"
                                disabled={processing}
                                className={`bg-gradient-to-r ${gradient} text-white px-12 py-4 rounded-xl font-bold hover:shadow-lg focus:ring-4 focus:ring-${accent}-200 disabled:opacity-50 text-lg transition-all transform active:scale-95 flex items-center gap-3`}
                            >
                                {processing ? (
                                    <span>جاري حفظ التعديلات...</span>
                                ) : (
                                    <>
                                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        حفظ التعديلات وتحديث القيود
                                    </>
                                )}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
