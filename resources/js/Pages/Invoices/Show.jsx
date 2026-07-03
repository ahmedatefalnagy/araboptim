import { Head, Link, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, invoice }) {
    const { settings } = usePage().props;
    const isSale = ['sale', 'sale_return', 'sale_quotation', 'sale_order'].includes(invoice.type);

    const formatDate = (dateString) => {
        if (!dateString) return '';
        try {
            const d = new Date(dateString);
            if (isNaN(d.getTime())) return dateString;
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            return `${day}-${month}-${year}`;
        } catch (e) {
            return dateString;
        }
    };
    
    const docTypeLabels = {
        sale: 'فاتورة مبيعات ضريبية',
        sale_return: 'مردود مبيعات',
        purchase: 'فاتورة مشتريات',
        purchase_return: 'مردود مشتريات',
        sale_quotation: 'عرض سعر مبيعات',
        sale_order: 'أمر بيع إداري',
        purchase_quotation: 'طلب عرض سعر مشتريات',
        purchase_order: 'أمر شراء مبدئي',
        work_order: 'أمر شغل',
        goods_receipt: 'تسوية مستودع - إضافة بضاعة',
        goods_issue: 'تسوية مستودع - إضافة تالف',
    };

    const titles = {
        sale: { ar: 'فاتورة ضريبية' },
        sale_return: { ar: 'إشعار دائن ضريبي' },
        purchase: { ar: 'فاتورة مشتريات' },
        purchase_return: { ar: 'إشعار مدين' },
        sale_quotation: { ar: 'عرض سعر مبيعات' },
        sale_order: { ar: 'أمر بيع إداري' },
        purchase_quotation: { ar: 'طلب عرض سعر مشتريات' },
        purchase_order: { ar: 'أمر شراء مبدئي' },
        work_order: { ar: 'طلب صيانة / خدمة' },
        goods_receipt: { ar: 'تسوية المستودع - إضافة بضاعة' },
        goods_issue: { ar: 'تسوية المستودع - إضافة تالف' },
    };

    const currentTitle = titles[invoice.type] || { ar: 'مستند مالي' };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`${currentTitle.ar} - ${invoice.invoice_no}`} />

            <div className="min-h-screen bg-gray-100 py-4 sm:py-8 print:bg-white print:py-0" dir="rtl">
                <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    
                    {/* Action Bar - Hidden during print */}
                    <div className="mb-6 flex flex-wrap justify-between items-center gap-4 print:hidden">
                        <Link 
                            href={route('invoices.index', { type: invoice.type })} 
                            className="text-gray-600 hover:text-gray-900 font-bold flex items-center gap-2 bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-200"
                        >
                            <span>&rarr;</span> العودة لقائمة {currentTitle.ar}
                        </Link>
                        <div className="flex gap-3 flex-wrap">
                            {(!['sale', 'sale_return', 'purchase', 'purchase_return'].includes(invoice.type) || !['cleared', 'reported'].includes(invoice.zatca_status)) && (
                                <Link 
                                    href={route('invoices.edit', invoice.id)} 
                                    className="bg-amber-500 text-white px-6 py-2.5 rounded-xl shadow-sm hover:bg-amber-600 transition-all font-bold flex items-center gap-2 border border-amber-600"
                                >
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    تعديل المستند
                                </Link>
                            )}

                            
                            {!['goods_receipt', 'goods_issue'].includes(invoice.type) && (
                                <a 
                                    href={route('invoices.pdf', invoice.id)} 
                                    target="_blank"
                                    className="bg-blue-600 text-white px-6 py-2.5 rounded-xl shadow-lg hover:bg-blue-700 transition-all font-black flex items-center gap-2"
                                >
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    تحميل الفاتورة الرسمية PDF
                                </a>
                            )}

                            {(invoice.type === 'sale' || invoice.type === 'goods_issue') && (
                                <a 
                                    href={route('invoices.delivery-note', invoice.id)} 
                                    target="_blank"
                                    className="bg-orange-600 text-white px-6 py-2.5 rounded-xl shadow-lg hover:bg-orange-700 transition-all font-black flex items-center gap-2"
                                >
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    {invoice.type === 'goods_issue' ? 'تحميل سند صرف مواد PDF' : 'سند تسليم مخزن'}
                                </a>
                            )}

                            {(invoice.type === 'purchase' || invoice.type === 'goods_receipt') && (
                                <a 
                                    href={route('invoices.grn', invoice.id)} 
                                    target="_blank"
                                    className="bg-emerald-600 text-white px-6 py-2.5 rounded-xl shadow-lg hover:bg-emerald-700 transition-all font-black flex items-center gap-2"
                                >
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    {invoice.type === 'goods_receipt' ? 'تحميل سند استلام بضاعة PDF' : 'سند استلام مخزن'}
                                </a>
                            )}

                            {invoice.attachment_path && (
                                <a 
                                    href={invoice.attachment_path} 
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="bg-purple-600 text-white px-6 py-2.5 rounded-xl shadow-lg hover:bg-purple-700 transition-all font-black flex items-center gap-2"
                                >
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.414a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    {invoice.type === 'purchase' ? 'فاتورة الشراء الأصل' : 
                                     (invoice.type === 'sale' ? 'سند التسليم المختوم' : 
                                     (invoice.type === 'goods_receipt' ? 'سند الاستلام الموقع' : 
                                     (invoice.type === 'goods_issue' ? 'سند الصرف الموقع' : 'المرفق الأصلي')))}
                                </a>
                            )}

                            {/* Conversion Action Buttons */}
                            {invoice.type === 'sale_quotation' && (
                                <>
                                    <Link 
                                        href={route('invoices.create', { type: 'sale_order', parent_document_id: invoice.id })}
                                        className="bg-emerald-600 text-white px-6 py-2.5 rounded-xl shadow-sm hover:bg-emerald-700 transition-all font-bold flex items-center gap-2 border border-emerald-700"
                                    >
                                        تحويل لأمر بيع إداري
                                    </Link>
                                    <Link 
                                        href={route('invoices.create', { type: 'work_order', parent_document_id: invoice.id })}
                                        className="bg-indigo-600 text-white px-6 py-2.5 rounded-xl shadow-sm hover:bg-indigo-700 transition-all font-bold flex items-center gap-2 border border-indigo-700"
                                    >
                                        تعميد كأمر شغل
                                    </Link>
                                </>
                            )}
                            {invoice.type === 'sale_order' && (
                                <Link 
                                    href={route('invoices.create', { type: 'sale', parent_document_id: invoice.id })}
                                    className="bg-emerald-600 text-white px-6 py-2.5 rounded-xl shadow-sm hover:bg-emerald-700 transition-all font-bold flex items-center gap-2 border border-emerald-700"
                                >
                                    إصدار فاتورة مبيعات
                                </Link>
                            )}
                            {invoice.type === 'purchase_quotation' && (
                                <Link 
                                    href={route('invoices.create', { type: 'purchase_order', parent_document_id: invoice.id })}
                                    className="bg-emerald-600 text-white px-6 py-2.5 rounded-xl shadow-sm hover:bg-emerald-700 transition-all font-bold flex items-center gap-2 border border-emerald-700"
                                >
                                    تحويل لأمر شراء
                                </Link>
                            )}
                            {invoice.type === 'purchase_order' && (
                                <Link 
                                    href={route('invoices.create', { type: 'purchase', parent_document_id: invoice.id })}
                                    className="bg-emerald-600 text-white px-6 py-2.5 rounded-xl shadow-sm hover:bg-emerald-700 transition-all font-bold flex items-center gap-2 border border-emerald-700"
                                >
                                    إصدار فاتورة مشتريات
                                </Link>
                            )}

                        </div>
                    </div>

                    {/* Document Traceability Chain */}
                    {(invoice.parent_document || (invoice.child_documents && invoice.child_documents.length > 0)) && (
                        <div className="mb-6 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm print:hidden">
                            <h3 className="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                تتبع المستندات المرتبطة (Traceability Chain)
                            </h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {invoice.parent_document && (
                                    <div className="p-4 bg-blue-50/50 rounded-xl border border-blue-100 flex justify-between items-center">
                                        <div>
                                            <span className="text-[10px] text-blue-600 font-bold block mb-1">مستند المصدر الأب (Origin Document)</span>
                                            <span className="font-mono text-sm text-gray-900 font-bold">
                                                {docTypeLabels[invoice.parent_document.type] || invoice.parent_document.type} - {invoice.parent_document.invoice_no}
                                            </span>
                                        </div>
                                        <Link 
                                            href={route('invoices.show', invoice.parent_document.id)}
                                            className="text-xs bg-white text-blue-700 px-3 py-1.5 rounded-lg border border-blue-200 font-bold hover:bg-blue-50 transition-colors"
                                        >
                                            عرض المستند الأب &larr;
                                        </Link>
                                    </div>
                                )}
                                {invoice.child_documents && invoice.child_documents.length > 0 && (
                                    <div className="p-4 bg-emerald-50/50 rounded-xl border border-emerald-100 space-y-3">
                                        <span className="text-[10px] text-emerald-600 font-bold block">المستندات التابعة الابنة (Follow-up Documents)</span>
                                        <div className="space-y-2">
                                            {invoice.child_documents.map(child => (
                                                <div key={child.id} className="flex justify-between items-center bg-white p-2 rounded-lg border border-emerald-100/50">
                                                    <span className="font-mono text-xs text-gray-700">
                                                        {docTypeLabels[child.type] || child.type} - <span className="font-bold">{child.invoice_no}</span>
                                                    </span>
                                                    <Link 
                                                        href={route('invoices.show', child.id)}
                                                        className="text-[11px] text-emerald-700 font-bold hover:underline"
                                                    >
                                                        عرض &larr;
                                                    </Link>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    {/* Invoice Paper Document */}
                    <div className="bg-white shadow-2xl rounded-sm overflow-hidden print:shadow-none print:border-0 print:m-0 print:w-full document-paper">
                        
                        {/* Header Section */}
                        <div className="p-8 border-b-2 border-gray-900 bg-white">
                            <div className="flex justify-between items-start">
                                {/* Company / Supplier Info */}
                                {['purchase', 'purchase_return', 'purchase_quotation', 'purchase_order'].includes(invoice.type) ? (
                                    <div className="space-y-1">
                                        <h1 className="text-3xl font-black text-gray-900">{invoice.contact.name}</h1>
                                        <h2 className="text-xl font-bold text-gray-600">بيانات المورد (صاحب الفاتورة)</h2>
                                        <div className="mt-4 text-sm text-gray-700 font-medium">
                                            <p>الرقم الضريبي: <span className="font-mono">{invoice.contact.tax_number || 'N/A'}</span></p>
                                            <p>العنوان: {invoice.contact.address || 'N/A'}</p>
                                            <p>هاتف: {invoice.contact.phone || 'N/A'}</p>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="space-y-1">
                                        <h1 className="text-3xl font-black text-gray-900">{settings?.company_name || 'مؤسسة عرب أوبتيما للتجارة'}</h1>
                                        <h2 className="text-xl font-bold text-gray-600">بيانات المنشأة</h2>
                                        <div className="mt-4 text-sm text-gray-700 font-medium">
                                            <p>الرقم الضريبي: <span className="font-mono">{settings?.company_vat_no || '300000000000003'}</span></p>
                                            <p>العنوان: {settings?.company_address || 'الرياض - حي المروج - طريق الملك فهد'}</p>
                                            <p>هاتف: {settings?.company_phone || '0500000000'}</p>
                                        </div>
                                    </div>
                                )}

                                {/* QR Code and Title */}
                                <div className="text-left flex flex-col items-end">
                                    <div className="text-center mb-4">
                                        <h3 className="text-2xl font-black text-blue-900 underline decoration-double">{invoice.type === 'sale' ? 'فاتورة ضريبية' : currentTitle.ar}</h3>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {/* Document Meta Info */}
                        <div className="p-8 bg-gray-50/50 border-b border-gray-100">
                            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
                                <div>
                                    <p className="text-[10px] text-gray-400 font-bold uppercase">رقم المستند</p>
                                    <p className="font-black text-lg font-mono text-gray-900">{invoice.invoice_no}</p>
                                </div>
                                <div>
                                    <p className="text-[10px] text-gray-400 font-bold uppercase">تاريخ الإصدار</p>
                                    <p className="font-black text-lg font-mono text-gray-900">{formatDate(invoice.invoice_date)}</p>
                                </div>
                                {invoice.due_date && (
                                    <div>
                                        <p className="text-[10px] text-gray-400 font-bold uppercase">تاريخ الاستحقاق</p>
                                        <p className="font-black text-lg font-mono text-gray-900">{formatDate(invoice.due_date)}</p>
                                    </div>
                                )}
                                {['purchase', 'purchase_return', 'purchase_quotation', 'purchase_order'].includes(invoice.type) ? (
                                    <>
                                        <div>
                                            <p className="text-[10px] text-gray-400 font-bold uppercase">المشتري (منشأتنا)</p>
                                            <p className="font-black text-lg text-gray-900">{settings?.company_name || 'مؤسسة عرب أوبتيما للتجارة'}</p>
                                        </div>
                                        <div>
                                            <p className="text-[10px] text-gray-400 font-bold uppercase">الرقم الضريبي للمشتري</p>
                                            <p className="font-black text-lg font-mono text-gray-900">{settings?.company_vat_no || '300000000000003'}</p>
                                        </div>
                                    </>
                                ) : (
                                    <>
                                        <div>
                                            <p className="text-[10px] text-gray-400 font-bold uppercase">{String(invoice.type).includes('sale') || String(invoice.type).includes('work') ? 'اسم العميل' : 'اسم المورد'}</p>
                                            <p className="font-black text-lg text-gray-900">{invoice.contact.name}</p>
                                        </div>
                                        {invoice.type !== 'work_order' && (
                                            <div>
                                                <p className="text-[10px] text-gray-400 font-bold uppercase">الرقم الضريبي</p>
                                                <p className="font-black text-lg font-mono text-gray-900">{invoice.contact.tax_number || 'N/A'}</p>
                                            </div>
                                        )}
                                    </>
                                )}
                            </div>
                        </div>

                        {/* Notes and Attachments if exist */}
                        {(invoice.notes || invoice.attachment_path) && (
                            <div className="px-8 py-4 bg-amber-50/30 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                {invoice.notes && (
                                    <div className="flex-1">
                                        <p className="text-[10px] text-gray-400 font-bold uppercase">ملاحظات</p>
                                        <p className="text-sm text-gray-700 font-medium">{invoice.notes}</p>
                                    </div>
                                )}
                                {invoice.attachment_path && (
                                    <div className="print:hidden flex flex-col items-start">
                                        <p className="text-[10px] text-gray-400 font-bold uppercase mb-1">المستند المرفق</p>
                                        <a 
                                            href={invoice.attachment_path} 
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-xs text-purple-700 hover:text-purple-900 font-bold flex items-center gap-1 bg-purple-50 px-3 py-1.5 rounded-lg border border-purple-200"
                                        >
                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.414a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            {invoice.type === 'purchase' ? 'تحميل فاتورة الشراء الأصلية' : 
                                             (invoice.type === 'sale' ? 'تحميل سند التسليم الموقع' : 
                                             (invoice.type === 'goods_receipt' ? 'تحميل سند الاستلام الموقع' : 
                                             (invoice.type === 'goods_issue' ? 'تحميل سند الصرف الموقع' : 'تحميل المستند المرفق')))}
                                        </a>
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Items Table */}
                        <div className="p-8 min-h-[400px]">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="bg-gray-900 text-white">
                                        <th className="px-4 py-3 text-right">#</th>
                                        <th className="px-4 py-3 text-right">الوصف</th>
                                        <th className="px-4 py-3 text-center">الكمية</th>
                                        {!['work_order', 'goods_receipt', 'goods_issue'].includes(invoice.type) && (
                                            <>
                                                <th className="px-4 py-3 text-center">سعر الوحدة</th>
                                                <th className="px-4 py-3 text-center">الضريبة</th>
                                                <th className="px-4 py-3 text-left">الإجمالي</th>
                                            </>
                                        )}
                                    </tr>
                                </thead>
                                <tbody className="border-x border-b border-gray-200">
                                    {invoice.lines && invoice.lines.map((line, index) => (
                                        <tr key={line.id} className={index % 2 === 0 ? 'bg-white' : 'bg-gray-50'}>
                                            <td className="px-4 py-4 border-b border-gray-100 font-mono text-gray-400">{index + 1}</td>
                                            <td className="px-4 py-4 border-b border-gray-100">
                                                <p className="font-black text-gray-900">{line.item_name}</p>
                                                <p className="text-[10px] text-gray-500 font-mono uppercase">{line.item?.sku}</p>
                                            </td>
                                            <td className="px-4 py-4 border-b border-gray-100 text-center font-bold font-mono text-lg">{parseFloat(line.quantity)}</td>
                                            {!['work_order', 'goods_receipt', 'goods_issue'].includes(invoice.type) && (
                                                <>
                                                    <td className="px-4 py-4 border-b border-gray-100 text-center font-mono">{parseFloat(line.unit_price).toFixed(2)}</td>
                                                    <td className="px-4 py-4 border-b border-gray-100 text-center">
                                                        <span className="text-xs text-gray-500 block">%{line.tax_rate}</span>
                                                        <span className="font-mono text-sm">{parseFloat(line.tax_amount).toFixed(2)}</span>
                                                    </td>
                                                    <td className="px-4 py-4 border-b border-gray-100 text-left font-black font-mono text-blue-900">{parseFloat(line.total).toFixed(2)}</td>
                                                </>
                                            )}
                                        </tr>
                                    ))}
                                    {/* Placeholder rows to maintain height if needed */}
                                    {[...Array(Math.max(0, 5 - (invoice.lines?.length || 0)))].map((_, i) => (
                                        <tr key={`blank-${i}`} className="h-10 border-b border-gray-100/50">
                                            <td colSpan="6"></td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Summary Section */}
                        <div className="px-8 pb-8 flex flex-col md:flex-row justify-between gap-12">
                            <div className="flex-1 space-y-6">
                                <div>
                                    <h4 className="text-xs font-black text-gray-400 uppercase tracking-widest mb-2 border-b-2 border-gray-100 pb-1">الشروط والأحكام / Terms</h4>
                                    <ul className="text-[11px] text-gray-500 list-disc list-inside leading-relaxed space-y-1">
                                        <li>تُعتبر هذه الفاتورة إقراراً بصحة المبالغ المذكورة.</li>
                                        <li>البضاعة المباعة لا ترد ولا تستبدل بعد مرور 3 أيام.</li>
                                        <li>في حال الدفع الآجل، يلزم السداد خلال 30 يوماً من تاريخه.</li>
                                    </ul>
                                </div>
                                <div className="grid grid-cols-2 gap-8 text-center pt-8 border-t border-dashed border-gray-200">
                                    <div>
                                        <p className="text-[10px] font-bold text-gray-400 underline">توقيع مقدم الطلب / Requester Sign</p>
                                        <div className="h-12"></div>
                                        <p className="text-sm border-t border-gray-300 pt-1">........................................</p>
                                    </div>
                                    <div>
                                        <p className="text-[10px] font-bold text-gray-400 underline">اعتماد الإدارة / Approval</p>
                                        <div className="h-12"></div>
                                        <p className="text-sm border-t border-gray-300 pt-1">{settings?.company_name || 'مؤسسة عرب أوبتيما'}</p>
                                    </div>
                                </div>
                            </div>

                            {!['work_order', 'goods_receipt', 'goods_issue'].includes(invoice.type) && (
                                <div className="w-full md:w-80 space-y-3 bg-gray-50 p-6 rounded-2xl border border-gray-100 h-fit">
                                    <div className="flex justify-between items-center text-gray-600">
                                        <span className="text-sm font-bold">المجموع الفرعي</span>
                                        <span className="font-mono font-bold">{parseFloat(invoice.total_base).toFixed(2)} ر.س</span>
                                    </div>
                                    <div className="flex justify-between items-center text-gray-600">
                                        <span className="text-sm font-bold">الضريبة (15%)</span>
                                        <span className="font-mono font-bold">{parseFloat(invoice.total_tax).toFixed(2)} ر.س</span>
                                    </div>
                                    <div className="pt-4 border-t-2 border-gray-900 flex justify-between items-center text-gray-900">
                                        <span className="text-lg font-black italic">الإجمالي</span>
                                        <span className="text-2xl font-black font-mono text-blue-700">{parseFloat(invoice.total_amount).toFixed(2)} ر.س</span>
                                    </div>
                                    <div className="text-[10px] text-center text-gray-400 mt-4 leading-tight font-bold italic">
                                        {`المبلغ كتابة: فـقـط لا غـيـر`}
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Footer Strip */}
                        <div className="bg-gray-100 text-gray-400 px-8 py-4 text-[10px] font-bold flex justify-between items-center border-t border-gray-200">
                            <span>صُدرت إلكترونياً عبر نظام Arab Optima ERP</span>
                            <span className="font-mono">{new Date().toLocaleString()}</span>
                        </div>
                    </div>
                </div>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                @media print {
                    @page {
                        margin: 0;
                        size: A4;
                    }
                    body {
                        background: white;
                    }
                    .print\\:hidden { display: none !important; }
                    .document-paper {
                        shadow: none !important;
                        border: none !important;
                        width: 100% !important;
                        max-width: none !important;
                    }
                    nav, aside, header { display: none !important; }
                    main { margin: 0 !important; padding: 0 !important; overflow: visible !important; }
                    .min-h-screen { min-height: auto !important; height: auto !important; }
                }
                .document-paper {
                    font-family: 'Inter', 'Amiri', serif;
                }
            `}} />
        </AuthenticatedLayout>
    );
}
