import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function ImportPreview({ auth, previewData = [], accounts = [], contacts = [] }) {
    const [entries, setEntries] = useState(previewData);
    const [isImporting, setIsImporting] = useState(false);

    const updateEntryState = (idx, newLines) => {
        const newEntries = [...entries];
        const entry = newEntries[idx];
        entry.lines = newLines;
        
        let totalDebit = 0;
        let totalCredit = 0;
        let hasErrors = false;

        entry.lines.forEach(line => {
            totalDebit += parseFloat(line.debit || 0);
            totalCredit += parseFloat(line.credit || 0);
            if (!line.account_id) hasErrors = true;
        });

        entry.total_debit = totalDebit;
        entry.total_credit = totalCredit;
        entry.is_balanced = Math.abs(totalDebit - totalCredit) < 0.01;
        entry.has_errors = hasErrors || !entry.is_balanced;

        setEntries(newEntries);
    };

    const handleLineChange = (entryIdx, lineIdx, field, value) => {
        const newLines = [...entries[entryIdx].lines];
        
        if (field === 'account_id') {
            const acc = accounts.find(a => a.id == value);
            newLines[lineIdx] = { 
                ...newLines[lineIdx], 
                account_id: value, 
                account_name: acc ? acc.name : '', 
                account_code: acc ? acc.code : '',
                error: !value ? 'الحساب مطلوب' : null 
            };
        } else if (field === 'contact_id') {
            const contact = contacts.find(c => c.id == value);
            newLines[lineIdx] = { 
                ...newLines[lineIdx], 
                contact_id: value, 
                contact_name: contact ? contact.name : '' 
            };
        } else {
            newLines[lineIdx] = { ...newLines[lineIdx], [field]: value };
        }

        updateEntryState(entryIdx, newLines);
    };

    const handleEntryFieldChange = (idx, field, value) => {
        const newEntries = [...entries];
        newEntries[idx][field] = value;
        setEntries(newEntries);
    };

    const handleRemoveEntry = (idx) => {
        if (confirm('هل تريد استبعاد هذا القيد من الاستيراد؟')) {
            setEntries(entries.filter((_, i) => i !== idx));
        }
    };

    const handleConfirm = () => {
        const validEntries = entries.filter(e => !e.has_errors);
        
        if (validEntries.length === 0) {
            alert('لا توجد قيود صالحة للاستيراد. يرجى تصحيح الأخطاء أولاً.');
            return;
        }

        if (entries.some(e => e.has_errors)) {
            if (!confirm(`يوجد ${entries.length - validEntries.length} قيد بها أخطاء وسيتم تجاهلها. هل تريد الاستمرار في حفظ القيود السليمة فقط؟`)) {
                return;
            }
        }

        setIsImporting(true);
        router.post(route('journal.entries.import.confirm'), {
            entries: validEntries
        }, {
            onFinish: () => setIsImporting(false)
        });
    };

    const formatNumber = (num) => {
        return (num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="مراجعة وتعديل الاستيراد" />

            <div className="min-h-screen bg-gray-50 pb-12" dir="rtl">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                    
                    {/* Page Header */}
                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">مراجعة وتعديل القيود المستوردة</h1>
                            <p className="mt-1 text-sm text-gray-600">يرجى مراجعة وتصحيح البيانات قبل التوثيق النهائي</p>
                        </div>
                        <Link href={route('journal.entries.index')} className="text-gray-600 hover:text-gray-900 border border-gray-300 bg-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition">
                            &larr; إلغاء والعودة
                        </Link>
                    </div>

                    {/* Global Actions Bar */}
                    <div className="mb-8 bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                        <div className="flex items-center gap-6">
                            <div className="flex flex-col">
                                <span className="text-xs text-gray-400 font-bold uppercase">إجمالي القيود</span>
                                <span className="text-lg font-black text-gray-700">{entries.length} قيد</span>
                            </div>
                            <div className="w-px h-10 bg-gray-100"></div>
                            <div className="flex flex-col">
                                <span className="text-xs text-gray-400 font-bold uppercase">القيود الجاهزة</span>
                                <span className="text-lg font-black text-emerald-600">{entries.filter(e => !e.has_errors).length} قيد</span>
                            </div>
                            <div className="w-px h-10 bg-gray-100"></div>
                            <div className="flex flex-col">
                                <span className="text-xs text-gray-400 font-bold uppercase">قيود بها أخطاء</span>
                                <span className="text-lg font-black text-red-600">{entries.filter(e => e.has_errors).length} قيد</span>
                            </div>
                        </div>
                        <button
                            onClick={handleConfirm}
                            disabled={isImporting || entries.length === 0}
                            className="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 disabled:opacity-50 transition-all hover:scale-105"
                        >
                            {isImporting ? 'جاري الحفظ...' : 'توثيق وترحيل القيود السليمة'}
                        </button>
                    </div>

                    {/* Entries List */}
                    <div className="space-y-10">
                        {entries.map((entry, idx) => (
                            <div key={idx} className={`bg-white rounded-2xl shadow-sm border p-8 relative transition-all ${entry.has_errors ? 'border-red-200 ring-4 ring-red-50' : 'border-gray-100'}`}>
                                
                                <button 
                                    onClick={() => handleRemoveEntry(idx)}
                                    className="absolute top-4 left-4 p-2 text-gray-300 hover:text-red-500 transition"
                                    title="استبعاد القيد"
                                >
                                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>

                                {/* Entry Info Form Style */}
                                <div className="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">
                                    <div className="md:col-span-3">
                                        <label className="block text-sm font-medium text-gray-700 mb-1">تاريخ القيد *</label>
                                        <input
                                            type="date"
                                            className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm font-bold"
                                            value={entry.entry_date}
                                            onChange={e => handleEntryFieldChange(idx, 'entry_date', e.target.value)}
                                        />
                                    </div>
                                    <div className="md:col-span-3">
                                        <label className="block text-sm font-medium text-gray-700 mb-1">المرجع / القيد</label>
                                        <input
                                            type="text"
                                            disabled
                                            className="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm text-sm font-bold text-gray-400"
                                            value={entry.reference}
                                        />
                                    </div>
                                    <div className="md:col-span-6">
                                        <label className="block text-sm font-medium text-gray-700 mb-1">البيان العام للقيد</label>
                                        <input
                                            type="text"
                                            className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm font-bold"
                                            value={entry.description}
                                            onChange={e => handleEntryFieldChange(idx, 'description', e.target.value)}
                                            placeholder="وصف عام..."
                                        />
                                    </div>
                                </div>

                                {/* Entry Lines Table Style */}
                                <div className="p-6 bg-gray-50 rounded-xl border border-gray-200">
                                    <table className="w-full text-right">
                                        <thead>
                                            <tr className="border-b border-gray-200">
                                                <th className="px-2 py-3 text-sm font-black text-gray-500 w-1/4">الحساب المحاسبي</th>
                                                <th className="px-2 py-3 text-sm font-black text-gray-500 w-1/6">جهة الاتصال</th>
                                                <th className="px-2 py-3 text-sm font-black text-gray-500">البيان</th>
                                                <th className="px-2 py-3 text-sm font-black text-gray-500 w-24 text-center">مدين</th>
                                                <th className="px-2 py-3 text-sm font-black text-gray-500 w-24 text-center">دائن</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-100">
                                            {entry.lines.map((line, lidx) => (
                                                <tr key={lidx}>
                                                    <td className="px-2 py-3">
                                                        <div className="relative group">
                                                            <input
                                                                list={`accounts-list-${idx}-${lidx}`}
                                                                className={`w-full rounded-xl shadow-sm text-sm font-bold ${!line.account_id ? 'border-red-300 ring-2 ring-red-50' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'}`}
                                                                placeholder="ابحث باسم الحساب أو الكود..."
                                                                defaultValue={line.account_id ? `[${line.account_code}] ${line.account_name}` : ''}
                                                                onChange={e => {
                                                                    const val = e.target.value;
                                                                    const acc = accounts.find(a => `[${a.code}] ${a.name}` === val);
                                                                    if (acc) handleLineChange(idx, lidx, 'account_id', acc.id);
                                                                }}
                                                            />
                                                            <datalist id={`accounts-list-${idx}-${lidx}`}>
                                                                {accounts.map(acc => (
                                                                    <option key={acc.id} value={`[${acc.code}] ${acc.name}`} />
                                                                ))}
                                                            </datalist>
                                                        </div>
                                                        {line.error && <p className="text-[10px] text-red-500 mt-1 font-bold">{line.error}</p>}
                                                    </td>
                                                    <td className="px-2 py-3">
                                                        <div className="relative group">
                                                            <input
                                                                list={`contacts-list-${idx}-${lidx}`}
                                                                className="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold"
                                                                placeholder="ابحث باسم الجهة..."
                                                                defaultValue={line.contact_name || ''}
                                                                onChange={e => {
                                                                    const val = e.target.value;
                                                                    const contact = contacts.find(c => c.name === val);
                                                                    if (contact) handleLineChange(idx, lidx, 'contact_id', contact.id);
                                                                }}
                                                            />
                                                            <datalist id={`contacts-list-${idx}-${lidx}`}>
                                                                    {contacts.map(c => (
                                                                        <option key={c.id} value={c.name} />
                                                                    ))}
                                                            </datalist>
                                                        </div>
                                                        {line.contact_name && !line.contact_id && <p className="text-[10px] text-amber-500 mt-1 font-bold">جهة غير معروفة: {line.contact_name}</p>}
                                                    </td>
                                                    <td className="px-2 py-3">
                                                        <input
                                                            type="text"
                                                            className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                            value={line.description}
                                                            onChange={e => handleLineChange(idx, lidx, 'description', e.target.value)}
                                                        />
                                                    </td>
                                                    <td className="px-2 py-3">
                                                        <input
                                                            type="number" step="0.01"
                                                            className="w-full rounded-xl border-blue-200 bg-blue-50/50 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm font-bold text-blue-700 text-center font-mono"
                                                            placeholder="0.00"
                                                            value={line.debit || ''}
                                                            onChange={e => handleLineChange(idx, lidx, 'debit', e.target.value)}
                                                        />
                                                    </td>
                                                    <td className="px-2 py-3">
                                                        <input
                                                            type="number" step="0.01"
                                                            className="w-full rounded-xl border-rose-200 bg-rose-50/50 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm font-bold text-rose-700 text-center font-mono"
                                                            placeholder="0.00"
                                                            value={line.credit || ''}
                                                            onChange={e => handleLineChange(idx, lidx, 'credit', e.target.value)}
                                                        />
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                {/* Summary Bar Style */}
                                <div className="flex flex-col md:flex-row justify-between items-center gap-4 pt-6 mt-2">
                                    <div className="flex bg-gray-50 border border-gray-200 rounded-xl divide-x divide-x-reverse text-sm shadow-sm overflow-hidden">
                                        <div className="px-6 py-3">
                                            <span className="text-gray-500 font-bold ml-2">الإجمالي مدين:</span>
                                            <span className="font-mono text-lg font-bold text-blue-600">{formatNumber(entry.total_debit)}</span>
                                        </div>
                                        <div className="px-6 py-3">
                                            <span className="text-gray-500 font-bold ml-2">الإجمالي دائن:</span>
                                            <span className="font-mono text-lg font-bold text-rose-600">{formatNumber(entry.total_credit)}</span>
                                        </div>
                                        <div className={`px-6 py-3 font-bold ${entry.is_balanced ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'}`}>
                                            <span className="ml-2 font-bold">الحالة:</span>
                                            <span className="text-sm uppercase font-black">
                                                {entry.is_balanced ? 'متزن ✓' : `غير متزن (الفرق: ${formatNumber(Math.abs(entry.total_debit - entry.total_credit))})`}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    {!entry.is_balanced && (
                                        <div className="text-red-500 text-xs font-bold animate-pulse">
                                            ⚠️ يرجى وزن القيد ليتم استيراده
                                        </div>
                                    )}
                                </div>
                            </div>
                        ))}
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
