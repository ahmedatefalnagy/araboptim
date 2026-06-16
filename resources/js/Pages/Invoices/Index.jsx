import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, type, invoices, flash }) {
    const titles = {
        sale: 'فواتير المبيعات الضريبية',
        sale_return: 'مردودات المبيعات',
        purchase: 'فواتير المشتريات والتوريد',
        purchase_return: 'مردودات المشتريات',
        sale_quotation: 'عروض أسعار المبيعات',
        sale_order: 'أوامر البيع',
        purchase_quotation: 'طلبات عروض أسعار المشتريات',
        purchase_order: 'أوامر الشراء',
        work_order: 'طلبات عروض العمل والصيانة',
        goods_receipt: 'سندات استلام المواد للمستودع',
        goods_issue: 'سندات صرف المواد من المستودع',
    };

    const isFinancial = ['sale', 'sale_return', 'purchase', 'purchase_return'].includes(type);
    const contactHeader = String(type).includes('sale') ? 'اسم العميل' : (String(type).includes('work') ? 'العميل' : 'اسم المورد');

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">{titles[type]}</h2>}
        >
            <Head title={titles[type]} />

            <div className="min-h-screen bg-gray-50 pb-12" dir="rtl">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                    <div className="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">{titles[type]}</h1>
                            <p className="mt-1 text-sm text-gray-600">
                                {isFinancial ? 'إدارة التفاصيل، استعراض الفواتير، وإنشاء الإدخالات اليومية التلقائية وتحديث المخزون' : 'أرشفة المستندات الإدارية المساعدة ومتابعة حالتها'}
                            </p>
                        </div>
                        <div className="flex items-center gap-3">
                            <Link href={route('dashboard')} className="text-gray-600 hover:text-gray-900 px-4 py-2 bg-white rounded-xl shadow-sm border border-gray-200">
                                العودة للرئيسية
                            </Link>
                            <Link 
                                href={route('invoices.create', { type })}
                                className="bg-blue-600 text-white px-6 py-2 rounded-xl hover:bg-blue-700 shadow-sm font-bold"
                            >
                                + إنشاء مستند جديد
                            </Link>
                        </div>
                    </div>

                    {flash?.success && (
                        <div className="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-xl text-green-800">
                            {flash.success}
                        </div>
                    )}
                    {flash?.error && (
                        <div className="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-xl text-red-800">
                            {flash.error}
                        </div>
                    )}

                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full text-right text-sm">
                                <thead className="bg-gray-50 text-gray-700 border-b">
                                    <tr>
                                        <th className="px-6 py-4 font-semibold">رقم المستند</th>
                                        <th className="px-6 py-4 font-semibold">التاريخ</th>
                                        <th className="px-6 py-4 font-semibold">{contactHeader}</th>
                                        {type !== 'work_order' && (
                                            <>
                                                {!['goods_receipt', 'goods_issue'].includes(type) && (
                                                    <th className="px-6 py-4 font-semibold text-center">ZATCA</th>
                                                )}
                                                <th className="px-6 py-4 font-semibold">المبلغ (أساس)</th>
                                                <th className="px-6 py-4 font-semibold text-blue-600">الضريبة</th>
                                                <th className="px-6 py-4 font-semibold text-green-700">الإجمالي</th>
                                            </>
                                        )}
                                        <th className="px-6 py-4 font-semibold text-center">إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {invoices.length > 0 ? (
                                        invoices.map(invoice => (
                                            <tr key={invoice.id} className="hover:bg-gray-50">
                                                <td className="px-6 py-4 font-mono font-medium">{invoice.invoice_no}</td>
                                                <td className="px-6 py-4 text-gray-600">{invoice.invoice_date}</td>
                                                <td className="px-6 py-4 font-bold text-gray-800">{invoice.contact?.name || 'محذوف'}</td>
                                                {type !== 'work_order' && (
                                                    <>
                                                        {!['goods_receipt', 'goods_issue'].includes(type) && (
                                                            <td className="px-6 py-4 text-center">
                                                                {invoice.qr_code_base64 ? (
                                                                    <span title="مكتملة ZATCA" className="inline-flex items-center justify-center bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold w-full mx-auto">
                                                                        <svg className="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                                                        معتمدة
                                                                    </span>
                                                                ) : (
                                                                    <span className="text-gray-300">-</span>
                                                                )}
                                                            </td>
                                                        )}
                                                        <td className="px-6 py-4 font-mono">{invoice.total_base}</td>
                                                        <td className="px-6 py-4 font-mono text-blue-600">{invoice.total_tax}</td>
                                                        <td className="px-6 py-4 font-mono font-bold text-green-700 bg-green-50/30">{invoice.total_amount}</td>
                                                    </>
                                                )}
                                                <td className="px-6 py-4 text-center">
                                                    <div className="flex items-center justify-center gap-2">
                                                        <Link 
                                                            href={route('invoices.show', invoice.id)} 
                                                            className="text-emerald-600 hover:bg-emerald-50 px-3 py-1 rounded-lg text-xs font-bold border border-emerald-200"
                                                        >
                                                            عرض
                                                        </Link>
                                                        {(!['sale', 'sale_return', 'purchase', 'purchase_return'].includes(type) || !['cleared', 'reported'].includes(invoice.zatca_status)) ? (
                                                            <Link 
                                                                href={route('invoices.edit', invoice.id)} 
                                                                className="text-amber-600 hover:bg-amber-50 px-3 py-1 rounded-lg text-xs font-bold border border-amber-200"
                                                            >
                                                                تعديل
                                                            </Link>
                                                        ) : (
                                                            <span className="text-gray-400 bg-gray-50 px-3 py-1 rounded-lg text-xs border border-gray-200 cursor-not-allowed" title="معتمدة ومرحلة، لا يمكن تعديلها">
                                                                تعديل
                                                            </span>
                                                        )}
                                                        <a 
                                                            href={
                                                                type === 'goods_receipt' ? route('invoices.grn', invoice.id) : 
                                                                (type === 'goods_issue' ? route('invoices.delivery-note', invoice.id) : 
                                                                route('invoices.pdf', invoice.id))
                                                            } 
                                                            target="_blank" 
                                                            className="text-blue-600 hover:bg-blue-50 px-3 py-1 rounded-lg text-xs font-bold border border-blue-200"
                                                        >
                                                            PDF
                                                        </a>
                                                        {type === 'sale_quotation' && (
                                                            <Link href={route('invoices.create', { type: 'sale_order', parent_document_id: invoice.id })} className="text-blue-600 hover:bg-blue-50 px-2 py-1 rounded text-xs font-bold border border-blue-200">
                                                                تحويل لأمر بيع
                                                            </Link>
                                                        )}
                                                        {type === 'sale_order' && (
                                                            <Link href={route('invoices.create', { type: 'sale', parent_document_id: invoice.id })} className="text-blue-600 hover:bg-blue-50 px-2 py-1 rounded text-xs font-bold border border-blue-200">
                                                                تحويل لفاتورة
                                                            </Link>
                                                        )}
                                                        {invoice.journal_entry_id && (
                                                            <Link 
                                                                href={route('journal.entries.show', invoice.journal_entry_id)} 
                                                                className="text-indigo-600 hover:bg-indigo-50 px-3 py-1 rounded-lg text-xs font-bold border border-indigo-200"
                                                            >
                                                                القيد
                                                            </Link>
                                                        )}
                                                        <Link 
                                                            href={route('invoices.destroy', invoice.id)} 
                                                            method="delete" 
                                                            as="button"
                                                            className="text-red-500 hover:text-red-800 bg-red-50 px-3 py-1 rounded-lg text-xs"
                                                            onClick={e => {
                                                                if(!confirm('هل أنت متأكد من الحذف؟ سيتم حذف القيد المحاسبي التابع أيضاً!')) e.preventDefault();
                                                            }}
                                                        >
                                                            حذف
                                                        </Link>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan="7" className="px-6 py-12 text-center text-gray-500 font-bold">
                                                لا يوجد أي سجلات في هذا القسم حالياً.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
