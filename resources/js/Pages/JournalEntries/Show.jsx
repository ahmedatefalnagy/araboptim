import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, entry }) {
    if (!entry) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <div className="p-8 text-center text-red-600 font-bold">القيد غير موجود.</div>
            </AuthenticatedLayout>
        );
    }

    const totalDebit = entry.lines?.reduce((sum, line) => sum + parseFloat(line.debit || 0), 0) || 0;
    const totalCredit = entry.lines?.reduce((sum, line) => sum + parseFloat(line.credit || 0), 0) || 0;

    const handleApprove = () => {
        if (confirm('هل أنت متأكد من اعتماد هذا القيد؟')) {
            router.post(route('journal.entries.post', entry.id));
        }
    };

    const handleUnpost = () => {
        if (confirm('هل أنت متأكد من إلغاء اعتماد هذا القيد؟ سيعود القيد لحالة المسودة لتتمكن من تعديله.')) {
            router.post(route('journal.entries.unpost', entry.id));
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`قيد يومية - ${entry.entry_no}`} />

            <div className="min-h-screen bg-gray-50 pb-12" dir="rtl">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                    
                    <div className="mb-6 flex justify-between items-center">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">تفاصيل قيد رقم: <span className="text-blue-600 font-mono">{entry.entry_no}</span></h1>
                            <p className="mt-1 text-sm text-gray-600">عرض بيانات القيد المحاسبي والعمليات المرتبطة</p>
                        </div>
                        <div className="flex gap-3">
                            <Link href={route('journal.entries.index')} className="px-6 py-2 rounded-xl border border-gray-300 bg-white text-gray-700 font-bold hover:bg-gray-50 transition shadow-sm">
                                عودة للقائمة
                            </Link>
                            <button onClick={() => window.print()} className="px-6 py-2 rounded-xl border border-blue-300 bg-blue-50 text-blue-700 font-bold hover:bg-blue-100 transition shadow-sm">
                                طباعة السند
                            </button>
                            {entry.status === 'draft' ? (
                                <>
                                    <Link href={route('journal.entries.edit', entry.id)} className="px-6 py-2 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition shadow-sm">
                                        تعديل القيد
                                    </Link>
                                    <button onClick={handleApprove} className="bg-emerald-600 text-white px-6 py-2 rounded-xl font-bold shadow-sm hover:bg-emerald-700 transition">
                                        اعتماد القيد
                                    </button>
                                </>
                            ) : (
                                <button onClick={handleUnpost} className="bg-amber-600 text-white px-6 py-2 rounded-xl font-bold shadow-sm hover:bg-amber-700 transition">
                                    إلغاء الاعتماد (تعديل)
                                </button>
                            )}
                        </div>
                    </div>

                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-6">
                        <div className="grid grid-cols-1 md:grid-cols-12 gap-6 max-w-5xl mx-auto">
                            <div className="md:col-span-3 lg:col-span-3">
                                <span className="block text-sm font-bold text-gray-500 mb-1">تاريخ القيد</span>
                                <span className="text-lg font-bold text-gray-900">{entry.entry_date}</span>
                            </div>
                            <div className="md:col-span-3 lg:col-span-3">
                                <span className="block text-sm font-bold text-gray-500 mb-1">السنة المالية</span>
                                <span className="text-lg font-bold text-gray-900">{entry.fiscal_year}</span>
                            </div>
                            <div className="md:col-span-3 lg:col-span-2">
                                <span className="block text-sm font-bold text-gray-500 mb-1">حالة الاعتماد</span>
                                <span className={`inline-block mt-1 text-sm font-bold px-3 py-1 rounded-full ${entry.status === 'posted' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}`}>
                                    {entry.status === 'posted' ? 'مرحل ومغلق' : 'مسودة مؤقتة'}
                                </span>
                            </div>
                            <div className="md:col-span-3 lg:col-span-4 border-t md:border-t-0 md:border-r border-gray-100 md:pr-4 pt-4 md:pt-0 mt-2 md:mt-0">
                                <span className="block text-sm font-bold text-gray-500 mb-1">البيان العام للقيد</span>
                                <span className="text-base font-bold text-gray-700">{entry.description || '-'}</span>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <table className="w-full min-w-[900px] text-right">
                            <thead className="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th className="px-6 py-4 text-sm font-black text-gray-500 border-l border-gray-100 w-[22%]">الحساب المحاسبي</th>
                                    <th className="px-6 py-4 text-sm font-black text-gray-500 border-l border-gray-100 w-[28%]">البيان الفرعي</th>
                                    <th className="px-6 py-4 text-sm font-black text-blue-600 border-l border-gray-100 w-32 text-center">مدين</th>
                                    <th className="px-6 py-4 text-sm font-black text-rose-600 border-l border-gray-100 w-32 text-center">دائن</th>
                                    <th className="px-6 py-4 text-sm font-black text-gray-500 w-1/6">الجهة / الطرف</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {entry.lines && entry.lines.map((line) => (
                                    <tr key={line.id} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-6 py-4 border-l border-gray-50 font-bold text-gray-900">{line.account_code} - {line.account_name}</td>
                                        <td className="px-6 py-4 border-l border-gray-50 text-gray-600 text-sm font-medium">{line.description || '-'}</td>
                                        <td className="px-6 py-4 border-l border-gray-50 font-mono font-bold text-lg text-blue-600 bg-blue-50/10 text-center">{parseFloat(line.debit) > 0 ? parseFloat(line.debit).toFixed(2) : '-'}</td>
                                        <td className="px-6 py-4 border-l border-gray-50 font-mono font-bold text-lg text-rose-600 bg-rose-50/10 text-center">{parseFloat(line.credit) > 0 ? parseFloat(line.credit).toFixed(2) : '-'}</td>
                                        <td className="px-6 py-4 text-gray-600 text-sm font-bold">{line.contact || '-'}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        
                        <div className="bg-gray-50 border-t border-gray-100 p-6 flex flex-col md:flex-row justify-end items-center gap-6">
                            <div className="flex bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden text-lg">
                                <div className="px-6 py-4 border-l border-gray-200">
                                    <span className="text-gray-500 text-sm font-bold ml-3">الإجمالي مدين:</span>
                                    <span className="font-mono font-black text-blue-600">{totalDebit.toFixed(2)}</span>
                                </div>
                                <div className="px-6 py-4">
                                    <span className="text-gray-500 text-sm font-bold ml-3">الإجمالي دائن:</span>
                                    <span className="font-mono font-black text-rose-600">{totalCredit.toFixed(2)}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                @media print {
                    @page { margin: 1cm; size: A4 portrait; }
                    body { background: white !important; font-family: sans-serif; }
                    .py-8 { padding-top: 0 !important; padding-bottom: 0 !important; }
                    .bg-gray-50 { background: white !important; }
                    .max-w-7xl { max-width: 100% !important; width: 100% !important; padding: 0 !important; margin: 0 !important; }
                    .shadow-sm { box-shadow: none !important; border-color: #e5e7eb !important; }
                    .flex.gap-3 { display: none !important; }
                    header, footer, nav, aside { display: none !important; }
                    main { padding: 0 !important; margin: 0 !important; }
                    a[href] { text-decoration: none !important; color: black !important; }
                    * { -webkit-print-color-adjust: exact !important; color-adjust: exact !important; }
                }
            `}} />
        </AuthenticatedLayout>
    );
}
