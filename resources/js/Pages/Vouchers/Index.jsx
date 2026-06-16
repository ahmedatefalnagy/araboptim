import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth,  type, vouchers, flash  }) {
    const titles = {
        expense: 'سندات المصروفات',
        advance: 'سلف الموظفين',
        petty_cash_issue: 'سندات صرف العهد',
        petty_cash_receipt: 'استعاضة وتسوية العهد',
        receipt: 'سندات القبض',
        payment: 'سندات الصرف',
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <>
            <Head title={titles[type]} />

            <div className="min-h-screen bg-gray-50 pb-12" dir="rtl">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                    <div className="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">{titles[type]}</h1>
                            <p className="mt-1 text-sm text-gray-600">إدارة السندات المالية والتوليد التلقائي للقيود</p>
                        </div>
                        <div className="flex items-center gap-3">
                            <Link href={route('dashboard')} className="text-gray-600 hover:text-gray-900 px-4 py-2 bg-white rounded-xl shadow-sm border border-gray-200">
                                العودة للرئيسية
                            </Link>
                            <Link 
                                href={route('vouchers.create', { type })}
                                className="bg-blue-600 text-white px-6 py-2 rounded-xl hover:bg-blue-700 shadow-sm"
                            >
                                + إنشاء سند جديد
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
                                        <th className="px-6 py-4 font-semibold">رقم السند</th>
                                        <th className="px-6 py-4 font-semibold">التاريخ</th>
                                        <th className="px-6 py-4 font-semibold w-1/4">البيان</th>
                                        <th className="px-6 py-4 font-semibold">من حساب (المدين)</th>
                                        <th className="px-6 py-4 font-semibold">إلى حساب (الدائن)</th>
                                        <th className="px-6 py-4 font-semibold text-blue-600">المبلغ</th>
                                        <th className="px-6 py-4 font-semibold text-center">إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {vouchers.length > 0 ? (
                                        vouchers.map(voucher => (
                                            <tr key={voucher.id} className="hover:bg-gray-50">
                                                <td className="px-6 py-4 font-mono font-medium">{voucher.voucher_no}</td>
                                                <td className="px-6 py-4 text-gray-600">{voucher.date}</td>
                                                <td className="px-6 py-4 text-gray-800">{voucher.description || '-'}</td>
                                                <td className="px-6 py-4 font-bold text-gray-800 bg-red-50/20">{voucher.debit_account?.name || '-'}</td>
                                                <td className="px-6 py-4 font-bold text-gray-800 bg-green-50/20">{voucher.credit_account?.name || '-'}</td>
                                                <td className="px-6 py-4 font-mono font-bold text-blue-700 bg-blue-50/30">{voucher.amount}</td>
                                                <td className="px-6 py-4 text-center">
                                                    <div className="flex items-center justify-center gap-2">
                                                        {voucher.journal_entry_id && (
                                                            <Link 
                                                                href={route('journal.entries.show', voucher.journal_entry_id)} 
                                                                className="text-indigo-600 hover:bg-indigo-50 px-3 py-1 rounded-lg text-xs font-bold border border-indigo-200"
                                                            >
                                                                القيد المحاسبي
                                                            </Link>
                                                        )}
                                                        <a 
                                                            href={route('vouchers.pdf', voucher.id)} 
                                                            target="_blank"
                                                            className="text-emerald-600 hover:bg-emerald-50 px-3 py-1 rounded-lg text-xs font-bold border border-emerald-200"
                                                        >
                                                            تحميل PDF
                                                        </a>
                                                        {voucher.attachment_path && (
                                                            <a 
                                                                href={`/storage/${voucher.attachment_path}`} 
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="text-purple-600 hover:bg-purple-50 px-3 py-1 rounded-lg text-xs font-bold border border-purple-200"
                                                            >
                                                                📎 السند الموقع
                                                            </a>
                                                        )}
                                                        <Link 
                                                            href={route('vouchers.destroy', voucher.id)} 
                                                            method="delete" 
                                                            as="button"
                                                            className="text-red-500 hover:text-red-800 bg-red-50 px-3 py-1 rounded-lg text-xs font-bold"
                                                            onClick={e => {
                                                                if(!confirm('هل أنت متأكد من الحذف؟ سيتم حذف القيد من الدفاتر أيضاً!')) e.preventDefault();
                                                            }}
                                                        >
                                                            إلغاء
                                                        </Link>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan="7" className="px-6 py-12 text-center text-gray-500">
                                                لا يوجد أي سندات مسجلة في هذا القسم.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </>
        </AuthenticatedLayout>
    );
}
