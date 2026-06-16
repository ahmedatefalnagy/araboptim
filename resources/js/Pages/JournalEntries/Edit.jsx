import { Head, useForm, Link } from '@inertiajs/react';
import { useState, useEffect, useRef } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Edit({ auth, entry, accounts = [], fiscalYears = [], contacts = [] }) {
    const { data, setData, put, processing, errors } = useForm({
        entry_date: entry.entry_date || '',
        description: entry.description || '',
        fiscal_year_id: entry.fiscal_year_id || '',
        lines: entry.lines || [
            { account_id: '', contact_id: '', description: '', debit: '', credit: '' },
            { account_id: '', contact_id: '', description: '', debit: '', credit: '' },
        ],
    });

    const [accountSearch, setAccountSearch] = useState({});
    const [showDropdown, setShowDropdown] = useState({});
    const dropdownRefs = useRef({});

    useEffect(() => {
        const initialSearch = {};
        if (entry.lines) {
            entry.lines.forEach((line, index) => {
                initialSearch[index] = `${line.account_code} - ${line.account_name}`;
            });
        }
        setAccountSearch(initialSearch);
    }, [entry.lines]);

    useEffect(() => {
        function handleClickOutside(event) {
            Object.keys(dropdownRefs.current).forEach(key => {
                if (dropdownRefs.current[key] && !dropdownRefs.current[key].contains(event.target)) {
                    setShowDropdown(prev => ({ ...prev, [key]: false }));
                }
            });
        }
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const getFilteredAccounts = (index) => {
        const search = accountSearch[index] || '';
        if (!search) return accounts.slice(0, 50);
        const lowerSearch = search.toLowerCase();
        return accounts.filter(acc => 
            acc.code.toLowerCase().includes(lowerSearch) || 
            acc.name.toLowerCase().includes(lowerSearch)
        ).slice(0, 50);
    };

    const selectAccount = (index, account) => {
        const lines = [...data.lines];
        lines[index].account_id = account.id;
        setData('lines', lines);
        setAccountSearch(prev => ({ ...prev, [index]: `${account.code} - ${account.name}` }));
        setShowDropdown(prev => ({ ...prev, [index]: false }));
    };

    const handleAccountInput = (index, value) => {
        setAccountSearch(prev => ({ ...prev, [index]: value }));
        setShowDropdown(prev => ({ ...prev, [index]: true }));
        
        const lines = [...data.lines];
        lines[index].account_id = '';
        setData('lines', lines);
    };

    const addLine = () => {
        const newIndex = data.lines.length;
        setData('lines', [...data.lines, { account_id: '', contact_id: '', description: '', debit: '', credit: '' }]);
        setAccountSearch(prev => ({ ...prev, [newIndex]: '' }));
    };

    const removeLine = (index) => {
        if (data.lines.length <= 2) return;
        const newLines = data.lines.filter((_, i) => i !== index);
        setData('lines', newLines);
    };

    const updateLine = (index, field, value) => {
        const lines = [...data.lines];
        if (field === 'debit' && value > 0) lines[index].credit = '';
        if (field === 'credit' && value > 0) lines[index].debit = '';
        lines[index][field] = value;
        setData('lines', lines);
    };

    const totalDebit = data.lines.reduce((sum, line) => sum + (parseFloat(line.debit) || 0), 0);
    const totalCredit = data.lines.reduce((sum, line) => sum + (parseFloat(line.credit) || 0), 0);
    const difference = Math.abs(totalDebit - totalCredit);
    const isBalanced = difference < 0.01 && totalDebit > 0;

    const selectedYear = fiscalYears.find(y => y.id == data.fiscal_year_id);

    const submit = (e) => {
        e.preventDefault();
        put(route('journal.entries.update', entry.id));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="تعديل قيد يومية" />

            <div className="min-h-screen bg-gray-50 pb-12" dir="rtl">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                     
                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">تعديل قيد يومية</h1>
                            <p className="mt-1 text-sm text-gray-600">رقم القيد: {entry.entry_no}</p>
                        </div>
                        <Link href={route('journal.entries.index')} className="text-gray-600 hover:text-gray-900 border border-gray-300 bg-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition">
                            &larr; العودة للقيود
                        </Link>
                    </div>

                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <form onSubmit={submit} className="space-y-6">
                            
                            <div className="grid grid-cols-1 md:grid-cols-12 gap-6 max-w-5xl mx-auto">
                                <div className="md:col-span-3">
                                    <label className="block text-sm font-medium text-gray-700 mb-1">تاريخ القيد *</label>
                                    <input
                                        type="date" required
                                        className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        value={data.entry_date}
                                        onChange={e => setData('entry_date', e.target.value)}
                                    />
                                </div>
                                <div className="md:col-span-3">
                                    <label className="block text-sm font-medium text-gray-700 mb-1">السنة المالية *</label>
                                    <select
                                        required
                                        className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        value={data.fiscal_year_id}
                                        onChange={e => setData('fiscal_year_id', e.target.value)}
                                    >
                                        <option value="">-- اختر --</option>
                                        {fiscalYears.map(y => <option key={y.id} value={y.id}>{y.name}</option>)}
                                    </select>
                                </div>
                                <div className="md:col-span-6">
                                    <label className="block text-sm font-medium text-gray-700 mb-1">البيان العام / الوصف</label>
                                    <input
                                        type="text"
                                        className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        placeholder="شرح عام للقيد..."
                                        value={data.description}
                                        onChange={e => setData('description', e.target.value)}
                                    />
                                </div>
                            </div>

                            <div className="p-6 bg-gray-50 rounded-xl border border-gray-200">
                                <table className="w-full text-right">
                                    <thead>
                                        <tr className="border-b border-gray-200">
                                            <th className="px-2 py-3 text-sm font-black text-gray-500 w-10 text-center">#</th>
                                            <th className="px-2 py-3 text-sm font-black text-gray-500">الحساب المحاسبي</th>
                                            <th className="px-2 py-3 text-sm font-black text-gray-500">البيان</th>
                                            <th className="px-2 py-3 text-sm font-black text-gray-500 w-24">مدين</th>
                                            <th className="px-2 py-3 text-sm font-black text-gray-500 w-24">دائن</th>
                                            <th className="px-2 py-3 text-sm font-black text-gray-500">الجهة</th>
                                            <th className="px-2 py-3 w-10 text-center"></th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100">
                                        {data.lines.map((line, index) => (
                                            <tr key={index}>
                                                <td className="px-2 py-3 text-sm font-bold text-gray-400 text-center">{index + 1}</td>
                                                <td className="px-2 py-3">
                                                    <div className="relative" ref={el => dropdownRefs.current[index] = el}>
                                                        <input
                                                            type="text"
                                                            placeholder="اكتب للبحث عن الحساب..."
                                                            className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                            style={{ minWidth: '200px' }}
                                                            value={accountSearch[index] || ''}
                                                            onChange={e => handleAccountInput(index, e.target.value)}
                                                            onFocus={() => setShowDropdown(prev => ({ ...prev, [index]: true }))}
                                                            autoComplete="off"
                                                        />
                                                        {showDropdown[index] && getFilteredAccounts(index).length > 0 && (
                                                            <div 
                                                                className="absolute z-[9999] mb-1 bg-white border border-gray-200 rounded-xl shadow-xl"
                                                                style={{ 
                                                                    width: '350px', 
                                                                    maxHeight: '200px',
                                                                    overflowY: 'auto',
                                                                    marginTop: '4px'
                                                                }}
                                                            >
                                                                {getFilteredAccounts(index).map(acc => (
                                                                    <div
                                                                        key={acc.id}
                                                                        className="px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-50 last:border-0"
                                                                        onClick={() => selectAccount(index, acc)}
                                                                    >
                                                                        <span className="font-mono font-bold text-blue-600">{acc.code}</span>
                                                                        <span className="mx-2 text-gray-400">-</span>
                                                                        <span className="text-gray-700">{acc.name}</span>
                                                                    </div>
                                                                ))}
                                                            </div>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="px-2 py-3">
                                                    <input
                                                        type="text"
                                                        className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                        placeholder="شرح..."
                                                        value={line.description}
                                                        onChange={e => updateLine(index, 'description', e.target.value)}
                                                    />
                                                </td>
                                                <td className="px-2 py-3">
                                                    <input
                                                        type="number" step="0.01"
                                                        className="w-full rounded-xl border-blue-200 bg-blue-50/50 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm font-bold text-blue-700 text-center font-mono"
                                                        placeholder="0.00"
                                                        value={line.debit}
                                                        onChange={e => updateLine(index, 'debit', e.target.value)}
                                                    />
                                                </td>
                                                <td className="px-2 py-3">
                                                    <input
                                                        type="number" step="0.01"
                                                        className="w-full rounded-xl border-rose-200 bg-rose-50/50 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm font-bold text-rose-700 text-center font-mono"
                                                        placeholder="0.00"
                                                        value={line.credit}
                                                        onChange={e => updateLine(index, 'credit', e.target.value)}
                                                    />
                                                </td>
                                                <td className="px-2 py-3">
                                                    <select
                                                        className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                        value={line.contact_id}
                                                        onChange={e => updateLine(index, 'contact_id', e.target.value)}
                                                    >
                                                        <option value="">- اختياري -</option>
                                                        {contacts.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                                    </select>
                                                </td>
                                                <td className="px-2 py-3 text-center">
                                                    {data.lines.length > 2 && (
                                                        <button type="button" onClick={() => removeLine(index)} className="text-gray-400 hover:text-red-500 transition-colors" title="حذف">
                                                            <svg className="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        </button>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colSpan="7" className="pt-4 pb-2 border-t border-gray-200">
                                                <button
                                                    type="button"
                                                    onClick={addLine}
                                                    className="text-sm border border-gray-300 bg-white text-gray-700 px-4 py-2 rounded-xl hover:bg-gray-100 font-bold shadow-sm flex items-center gap-2"
                                                >
                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                                    إضافة سطر
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div className="flex flex-col md:flex-row justify-between items-center gap-4 pt-4 border-t border-gray-100">
                                <div className="flex bg-gray-50 border border-gray-200 rounded-xl divide-x divide-x-reverse text-sm mb-4 md:mb-0 shadow-sm overflow-hidden">
                                    <div className="px-6 py-3">
                                        <span className="text-gray-500 font-bold ml-2">الإجمالي مدين:</span>
                                        <span className="font-mono text-lg font-bold text-blue-600">{totalDebit.toFixed(2)}</span>
                                    </div>
                                    <div className="px-6 py-3">
                                        <span className="text-gray-500 font-bold ml-2">الإجمالي دائن:</span>
                                        <span className="font-mono text-lg font-bold text-rose-600">{totalCredit.toFixed(2)}</span>
                                    </div>
                                    <div className={`px-6 py-3 font-bold ${difference === 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'}`}>
                                        <span className="ml-2 font-bold">الفرق:</span>
                                        <span className="font-mono text-lg">{difference.toFixed(2)}</span>
                                    </div>
                                </div>

                                <div className="flex gap-3">
                                    {errors.lines && <div className="text-red-500 text-sm font-bold flex items-center">{errors.lines}</div>}
                                    <button
                                        type="submit"
                                        disabled={processing || !isBalanced}
                                        className="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold shadow-sm hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                                    >
                                        {processing ? 'جاري الحفظ...' : 'تحديث القيد'}
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
            
            <style jsx>{`
                input[type='number']::-webkit-inner-spin-button,
                input[type='number']::-webkit-outer-spin-button {
                    -webkit-appearance: none;
                    margin: 0;
                }
            `}</style>
        </AuthenticatedLayout>
    );
}
