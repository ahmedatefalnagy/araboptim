import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

const formatNumber = (num) => {
    return (num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

export default function CashRegister({ auth, accounts, selectedAccountId, openingBalance, currentBalance, lines, filters }) {
    const [startDate, setStartDate] = useState(filters.start_date || '');
    const [endDate, setEndDate] = useState(filters.end_date || '');
    const [accountId, setAccountId] = useState(selectedAccountId || '');

    const applyFilters = () => {
        router.get(route('vouchers.cash-register'), {
            account_id: accountId,
            start_date: startDate,
            end_date: endDate
        }, { preserveState: true });
    };

    const handleAccountChange = (id) => {
        setAccountId(id);
        router.get(route('vouchers.cash-register'), {
            account_id: id,
            start_date: startDate,
            end_date: endDate
        });
    };

    // Calculate running balance sequentially
    let running = openingBalance;
    const linesWithBalance = lines.map(line => {
        running = running + (parseFloat(line.debit) || 0) - (parseFloat(line.credit) || 0);
        return {
            ...line,
            running_balance: running
        };
    });

    // We want to show the list starting with the most recent transaction, but calculating the running balance requires chronological order.
    // So we calculate the running balance first (chronological), and then reverse the array to display latest first!
    const displayLines = [...linesWithBalance].reverse();

    const selectedAccountName = accounts.find(a => a.id == accountId)?.name || 'الصندوق';

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`رصيد وحركة الصندوق - ${selectedAccountName}`} />

            <div className="min-h-screen bg-gray-50 pb-12" dir="rtl">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                    
                    {/* Header */}
                    <div className="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">حركات ورصيد الصناديق والبنوك</h1>
                            <p className="mt-1 text-sm text-gray-600">دفتر حركة النقدية التفصيلي وأرصدة الحسابات السائلة</p>
                        </div>
                        <Link href={route('dashboard')} className="text-gray-600 hover:text-gray-900 px-4 py-2 bg-white rounded-xl shadow-sm border border-gray-200">
                            العودة للرئيسية
                        </Link>
                    </div>

                    {/* Selector & Date Filters */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label className="block text-xs font-bold text-gray-500 mb-2">اختر حساب الصندوق / البنك</label>
                                <select 
                                    className="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 font-bold text-gray-800"
                                    value={accountId}
                                    onChange={e => handleAccountChange(e.target.value)}
                                >
                                    {accounts.map(acc => (
                                        <option key={acc.id} value={acc.id}>
                                            [{acc.code}] {acc.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="block text-xs font-bold text-gray-500 mb-2">من تاريخ</label>
                                <input 
                                    type="date" 
                                    className="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500"
                                    value={startDate}
                                    onChange={e => setStartDate(e.target.value)}
                                />
                            </div>
                            <div>
                                <label className="block text-xs font-bold text-gray-500 mb-2">إلى تاريخ</label>
                                <input 
                                    type="date" 
                                    className="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500"
                                    value={endDate}
                                    onChange={e => setEndDate(e.target.value)}
                                />
                            </div>
                            <div>
                                <button 
                                    onClick={applyFilters}
                                    className="w-full bg-blue-600 text-white font-bold py-2.5 px-4 rounded-xl hover:bg-blue-700 transition-colors shadow-sm text-sm"
                                >
                                    تحديث البيانات
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Stats Dashboard */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
                            <span className="text-xs font-bold text-gray-400">الرصيد الافتتاحي (في {startDate})</span>
                            <span className="text-2xl font-bold text-gray-900 mt-2 font-mono">{formatNumber(openingBalance)} <span className="text-sm font-sans text-gray-500">SAR</span></span>
                        </div>
                        <div className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
                            <span className="text-xs font-bold text-gray-400">حجم التدفقات خلال الفترة (مدين - قبض)</span>
                            <span className="text-2xl font-bold text-emerald-600 mt-2 font-mono">+{formatNumber(lines.reduce((acc, l) => acc + l.debit, 0))} <span className="text-sm font-sans text-emerald-500">SAR</span></span>
                        </div>
                        <div className="bg-white rounded-2xl p-6 shadow-sm border border-blue-200 bg-blue-50/20 flex flex-col justify-between border-2">
                            <span className="text-xs font-bold text-blue-600">الرصيد الحالي (في {endDate})</span>
                            <span className="text-2xl font-bold text-blue-900 mt-2 font-mono">{formatNumber(currentBalance)} <span className="text-sm font-sans text-blue-500">SAR</span></span>
                        </div>
                    </div>

                    {/* Transactions Table */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="p-5 border-b border-gray-100">
                            <h2 className="text-base font-bold text-gray-950">تفاصيل حركات الصندوق</h2>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-right text-sm">
                                <thead className="bg-gray-50 text-gray-700 border-b">
                                    <tr>
                                        <th className="px-6 py-4 font-semibold">التاريخ</th>
                                        <th className="px-6 py-4 font-semibold">رقم السند/القيد</th>
                                        <th className="px-6 py-4 font-semibold w-1/3">البيان</th>
                                        <th className="px-6 py-4 font-semibold text-emerald-600">قبض (مدين)</th>
                                        <th className="px-6 py-4 font-semibold text-red-600">صرف (دائن)</th>
                                        <th className="px-6 py-4 font-semibold text-blue-700">الرصيد الجاري</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {displayLines.length > 0 ? (
                                        displayLines.map((line, idx) => (
                                            <tr key={line.id || idx} className="hover:bg-gray-50">
                                                <td className="px-6 py-4 text-gray-600 font-medium">{line.date}</td>
                                                <td className="px-6 py-4">
                                                    {line.transaction_type === 'voucher' ? (
                                                        <Link 
                                                            href={route('vouchers.index', { type: line.credit > 0 ? 'payment' : 'receipt' })}
                                                            className="text-blue-600 font-mono font-bold hover:underline"
                                                        >
                                                            سند #{line.entry_no}
                                                        </Link>
                                                    ) : (
                                                        <span className="font-mono text-gray-700">قيد #{line.entry_no}</span>
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 text-gray-800">{line.description}</td>
                                                <td className="px-6 py-4 font-mono font-bold text-emerald-600">
                                                    {line.debit > 0 ? `+${formatNumber(line.debit)}` : '-'}
                                                </td>
                                                <td className="px-6 py-4 font-mono font-bold text-red-500">
                                                    {line.credit > 0 ? `-${formatNumber(line.credit)}` : '-'}
                                                </td>
                                                <td className="px-6 py-4 font-mono font-bold text-blue-900 bg-blue-50/10">
                                                    {formatNumber(line.running_balance)}
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan="6" className="px-6 py-12 text-center text-gray-500">
                                                لا توجد حركات مسجلة لهذا الحساب خلال الفترة المحددة.
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
