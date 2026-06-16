import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, entries = [], pagination = { links: [] }, fiscalYears = [], selectedYearId = null, startDate: initialStart, endDate: initialEnd }) {
    const selectedYear = fiscalYears.find(y => y.id == selectedYearId) || fiscalYears[0];
    const [startDate, setStartDate] = useState(initialStart || (selectedYear ? selectedYear.start_date?.split('T')[0] : ''));
    const [endDate, setEndDate] = useState(initialEnd || (selectedYear ? selectedYear.end_date?.split('T')[0] : ''));
    const [statusFilter, setStatusFilter] = useState('all');

    const handleSearch = (e) => {
        if (e) e.preventDefault();
        router.get(route('journal.entries.index'), {
            start_date: startDate,
            end_date: endDate
        }, { replace: true });
    };

    const handleApprove = (id) => {
        if (confirm('هل أنت متأكد من اعتماد هذا القيد؟')) {
            router.post(route('journal.entries.post', id));
        }
    };

    const handleUnpost = (id) => {
        if (confirm('هل أنت متأكد من إلغاء اعتماد هذا القيد؟ سيعود لحالة المسودة للتمكن من تعديله.')) {
            router.post(route('journal.entries.unpost', id));
        }
    };

    const handleExport = () => {
        const year = fiscalYears.find(y => y.id == selectedYearId);
        if (year) {
            window.location.href = route('journal.entries.export', {
                start_date: startDate || year.start_date.split('T')[0],
                end_date: endDate || year.end_date.split('T')[0]
            });
        }
    };

    const handleDelete = (id) => {
        if (confirm('هل أنت متأكد من حذف هذا القيد؟ لا يمكن التراجع.')) {
            router.delete(route('journal.entries.destroy', id));
        }
    };

    const handleImportClick = () => {
        document.getElementById('excel-import-input').click();
    };

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            alert('جاري معالجة الملف، يرجى الانتظار للحظات...');
            const formData = new FormData();
            formData.append('file', file);
            router.post(route('journal.entries.import.preview'), formData, {
                forceFormData: true,
                onSuccess: () => {
                    // Success will redirect
                },
                onError: (errors) => {
                    alert('فشل رفع الملف: ' + Object.values(errors).join('\n'));
                },
                onFinish: () => {
                    e.target.value = ''; // Always reset
                }
            });
        }
    };

    const formatNumber = (num) => {
        return (num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    const filteredEntries = entries.filter(entry => {
        if (statusFilter === 'all') return true;
        if (statusFilter === 'posted') return entry.status === 'posted';
        if (statusFilter === 'draft') return entry.status === 'draft';
        return true;
    });

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="القيود اليومية" />

            <div className="min-h-screen bg-gray-50" dir="rtl">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
                    <div className="mb-6 flex items-center justify-between flex-wrap gap-4">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h1 className="text-xl sm:text-2xl font-bold text-gray-900">القيود اليومية</h1>
                                {selectedYear && (
                                    <p className="text-xs sm:text-sm text-gray-500">
                                        {selectedYear.name} • {selectedYear.start_date?.split('T')[0]} إلى {selectedYear.end_date?.split('T')[0]}
                                    </p>
                                )}
                            </div>
                        </div>

                        <div className="flex items-center gap-2 flex-wrap">
                            <input
                                type="file"
                                id="excel-import-input"
                                className="hidden"
                                accept=".xlsx,.xls,.csv"
                                onChange={handleFileChange}
                            />
                            <button
                                onClick={handleImportClick}
                                className="flex items-center gap-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 px-4 py-2.5 rounded-xl font-bold transition text-sm border border-emerald-200"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                <span>استيراد Excel</span>
                            </button>
                            <a
                                href={route('journal.entries.template')}
                                className="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl font-bold transition text-sm border border-gray-200"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>تحميل النموذج</span>
                            </a>
                            <Link
                                href="/journal/entries/create"
                                className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-bold shadow-md transition text-sm"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>إضافة قيد جديد</span>
                            </Link>
                        </div>
                    </div>

                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="p-4 border-b border-gray-100 bg-gray-50">
                            <form onSubmit={handleSearch} className="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                                <div className="md:col-span-1">
                                    <label className="block text-sm font-medium text-gray-600 mb-1">من تاريخ</label>
                                    <input
                                        type="date"
                                        value={startDate}
                                        onChange={e => setStartDate(e.target.value)}
                                        className="w-full bg-white border border-gray-300 rounded-xl px-3 py-2.5 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>
                                <div className="md:col-span-1">
                                    <label className="block text-sm font-medium text-gray-600 mb-1">إلى تاريخ</label>
                                    <input
                                        type="date"
                                        value={endDate}
                                        onChange={e => setEndDate(e.target.value)}
                                        className="w-full bg-white border border-gray-300 rounded-xl px-3 py-2.5 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>
                                <div className="md:col-span-1">
                                    <label className="block text-sm font-medium text-gray-600 mb-1">الحالة</label>
                                    <select
                                        value={statusFilter}
                                        onChange={e => setStatusFilter(e.target.value)}
                                        className="w-full bg-white border border-gray-300 rounded-xl px-3 py-2.5 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="all">الكل</option>
                                        <option value="draft">مسودة</option>
                                        <option value="posted">معتمدة</option>
                                    </select>
                                </div>
                                <div className="md:col-span-2 flex items-center gap-2 mt-auto">
                                    <button
                                        type="submit"
                                        className="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-bold transition text-sm"
                                    >
                                        عرض القيود
                                    </button>
                                    <button
                                        type="button"
                                        onClick={handleExport}
                                        className="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl font-bold transition text-sm"
                                    >
                                        تصدير Excel
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => window.print()}
                                        className="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2.5 rounded-xl font-bold transition text-sm"
                                    >
                                        طباعة
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full">
                                <thead>
                                    <tr className="bg-gray-100 text-right">
                                        <th className="px-4 py-3 text-xs font-black text-gray-500 uppercase tracking-wider">رقم القيد</th>
                                        <th className="px-4 py-3 text-xs font-black text-gray-500 uppercase tracking-wider">التاريخ</th>
                                        <th className="px-4 py-3 text-xs font-black text-gray-500 uppercase tracking-wider">البيان</th>
                                        <th className="px-4 py-3 text-xs font-black text-gray-500 uppercase tracking-wider">مدين</th>
                                        <th className="px-4 py-3 text-xs font-black text-gray-500 uppercase tracking-wider">دائن</th>
                                        <th className="px-4 py-3 text-xs font-black text-gray-500 uppercase tracking-wider">الحالة</th>
                                        <th className="px-4 py-3 text-xs font-black text-gray-500 uppercase tracking-wider text-center">التحكم</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {filteredEntries.length === 0 ? (
                                        <tr>
                                            <td colSpan="7" className="px-4 py-12 text-center">
                                                <div className="flex flex-col items-center gap-2">
                                                    <svg className="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <p className="text-gray-400 font-medium">لا توجد قيود حالياً</p>
                                                </div>
                                            </td>
                                        </tr>
                                    ) : (
                                        filteredEntries.map((entry) => (
                                            <tr key={entry.id} className="hover:bg-gray-50/50 transition">
                                                <td className="px-4 py-3">
                                                    <div className="text-sm font-bold text-gray-900">{entry.entry_no}</div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="text-sm text-gray-600 font-bold">{entry.entry_date}</div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="text-sm text-gray-500 truncate max-w-xs" title={entry.description}>
                                                        {entry.description || '-'}
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <span className="text-sm font-bold text-red-600">{formatNumber(entry.total_debit)}</span>
                                                </td>
                                                <td className="px-4 py-3">
                                                    <span className="text-sm font-bold text-green-600">{formatNumber(entry.total_credit)}</span>
                                                </td>
                                                <td className="px-4 py-3">
                                                    {entry.status === 'posted' ? (
                                                        <span className="inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full text-xs font-bold">
                                                            <svg className="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                                            </svg>
                                                            معتمد
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center gap-1 bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full text-xs font-bold">
                                                            <svg className="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                                                            </svg>
                                                            مسودة
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <div className="flex items-center justify-center gap-1">
                                                        {entry.status === 'draft' ? (
                                                            <>
                                                                <Link
                                                                    href={`/journal/entries/${entry.id}/edit`}
                                                                    className="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                                                    title="تعديل"
                                                                >
                                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                    </svg>
                                                                </Link>
                                                                <button
                                                                    onClick={() => handleApprove(entry.id)}
                                                                    className="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                                                                    title="اعتماد"
                                                                >
                                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                                                                    </svg>
                                                                </button>
                                                                <button
                                                                    onClick={() => handleDelete(entry.id)}
                                                                    className="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                                                                    title="حذف"
                                                                >
                                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1H6a1 1 0 00-1 1v3M4 7h12" />
                                                                    </svg>
                                                                </button>
                                                            </>
                                                        ) : (
                                                            <button
                                                                onClick={() => handleUnpost(entry.id)}
                                                                className="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition"
                                                                title="إلغاء الاعتماد"
                                                            >
                                                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            </button>
                                                        )}

                                                        <Link
                                                            href={`/journal/entries/${entry.id}`}
                                                            className="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition"
                                                            title="عرض"
                                                        >
                                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                        </Link>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                        
                        <div className="px-4 py-3 border-t border-gray-100 bg-gray-50 flex items-center justify-between flex-wrap gap-4 text-sm">
                            <div className="flex items-center gap-4">
                                <span className="text-gray-500 font-bold">
                                    الإجمالي: {pagination.total || entries.length} قيد
                                </span>
                                <div className="flex items-center gap-4 border-r pr-4 border-gray-200">
                                    <span className="flex items-center gap-1">
                                        <span className="w-3 h-3 bg-amber-100 rounded-full"></span>
                                        <span className="text-xs text-gray-600 font-bold">{entries.filter(e => e.status === 'draft').length} مسودة (بهذه الصفحة)</span>
                                    </span>
                                    <span className="flex items-center gap-1">
                                        <span className="w-3 h-3 bg-emerald-100 rounded-full"></span>
                                        <span className="text-xs text-gray-600 font-bold">{entries.filter(e => e.status === 'posted').length} معتمدة (بهذه الصفحة)</span>
                                    </span>
                                </div>
                            </div>

                            {/* Pagination Links */}
                            <div className="flex items-center gap-1">
                                {pagination.links.map((link, i) => (
                                    <Link
                                        key={i}
                                        href={link.url || '#'}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                        className={`px-3 py-1.5 rounded-lg text-xs font-bold transition ${
                                            link.active 
                                                ? 'bg-blue-600 text-white shadow-md' 
                                                : link.url 
                                                    ? 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' 
                                                    : 'bg-gray-50 text-gray-300 cursor-not-allowed'
                                        }`}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
