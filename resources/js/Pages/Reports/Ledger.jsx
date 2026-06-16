import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState, useRef, useEffect, useMemo } from 'react';
import PageHeader from '@/Components/PageHeader';
import DataTable from '@/Components/DataTable';

export default function Ledger({ auth, accounts, contacts, filters, lines, openingBalance, selectedAccount, selectedContact }) {
    const [searchTerm, setSearchTerm] = useState('');
    const [showDropdown, setShowDropdown] = useState(false);
    const dropdownRef = useRef(null);

    const { data, setData, get, processing } = useForm({
        account_id: filters.account_id || '',
        contact_id: filters.contact_id || '',
        start_date: filters.start_date || '',
        end_date: filters.end_date || '',
    });

    const currentAccount = accounts.find(a => a.id == data.account_id);
    const isControlAccount = currentAccount && ['1130', '2110', '1155'].includes(currentAccount.code.toString());
    
    const filteredContactsList = (contacts || []).filter(c => {
        if (!currentAccount) return true;
        if (currentAccount.code == '1130') return c.type === 'customer' || c.is_customer;
        if (currentAccount.code == '2110') return c.type === 'supplier' || c.is_supplier;
        if (currentAccount.code == '1155') return c.type === 'partner' || c.is_related_party;
        return true;
    });

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setShowDropdown(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const filteredAccounts = accounts.filter(acc => 
        acc.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
        acc.code.toString().includes(searchTerm)
    );

    const submit = (e) => {
        e.preventDefault();
        get(route('reports.ledger'), { preserveState: true });
    };

    const fmt = (num) => {
        const val = Number(num || 0);
        return val >= 0 
            ? val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
            : `(${Math.abs(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })})`;
    };

    // Calculate running balance for the data table
    const processedLines = useMemo(() => {
        let currentBalance = openingBalance || 0;
        const isDebitNormal = selectedAccount?.type?.normal_balance === 'debit';
        
        return lines.map(line => {
            if (isDebitNormal) {
                currentBalance += line.debit - line.credit;
            } else {
                currentBalance += line.credit - line.debit;
            }
            return { ...line, runningBalance: currentBalance };
        });
    }, [lines, openingBalance, selectedAccount]);

    const columns = [
        { key: 'entry_no', label: 'رقم القيد', className: 'w-24', render: (row) => <span className="text-slate-400 font-bold font-mono">#{row.entry_no}</span> },
        { key: 'date', label: 'التاريخ', className: 'w-32', tdClassName: 'font-mono font-bold text-slate-500' },
        { key: 'debit', label: 'مدين (-)', align: 'left', tdClassName: 'text-red-600 font-bold', render: (row) => row.debit > 0 ? fmt(row.debit) : '0.00' },
        { key: 'credit', label: 'دائن (+)', align: 'left', tdClassName: 'text-emerald-600 font-bold', render: (row) => row.credit > 0 ? fmt(row.credit) : '0.00' },
        { key: 'runningBalance', label: 'الرصيد التراكمي', align: 'left', tdClassName: 'bg-slate-50 font-black text-slate-900', render: (row) => fmt(row.runningBalance) },
        { key: 'description', label: 'البيان', render: (row) => <div className="text-[11px] font-medium text-slate-600 leading-relaxed max-w-[300px]">{row.description}</div> },
    ];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="دفتر الأستاذ" />

            <div className="min-h-screen bg-slate-50 py-6" dir="rtl">
                <div className="mx-auto max-w-[1400px] px-4">
                    
                    <PageHeader 
                        title="دفتر الأستاذ (General Ledger)" 
                        subtitle="كشف حساب تفصيلي للقيود المرحلة"
                        backRoute={route('reports.index')}
                        stats={selectedAccount ? [
                            { label: 'الرصيد الحالي', value: fmt(processedLines[processedLines.length - 1]?.runningBalance || openingBalance), color: 'blue' },
                            { label: 'الرصيد الافتتاحي', value: fmt(openingBalance) },
                            { label: 'إجمالي مدين', value: fmt(lines.reduce((s, l) => s + l.debit, 0)), color: 'red' },
                            { label: 'إجمالي دائن', value: fmt(lines.reduce((s, l) => s + l.credit, 0)), color: 'emerald' },
                        ] : []}
                        actions={[
                            <a target="_blank" href={route('reports.ledger.excel', filters)} className="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-lg border border-emerald-100 hover:bg-emerald-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ملف Excel
                            </a>,
                            <a target="_blank" href={route('reports.ledger.pdf', filters)} className="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 text-sm font-bold rounded-lg border border-red-100 hover:bg-red-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ملف PDF
                            </a>,
                            <button onClick={() => window.print()} className="inline-flex items-center px-4 py-2 bg-white text-slate-700 text-sm font-bold rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                طباعة
                            </button>
                        ]}
                    />

                    <div className="bg-white p-6 rounded-2xl shadow-sm mb-8 border border-slate-200 hide-print">
                        <form onSubmit={submit} className="space-y-4">
                            <div className="grid grid-cols-12 gap-4 items-end">
                                <div className="col-span-12 md:col-span-5 relative" ref={dropdownRef}>
                                    <label className="block text-[11px] font-black text-slate-400 uppercase mb-1.5 mr-1">الحساب المحاسبي</label>
                                    <div 
                                        className="w-full rounded-xl border border-slate-200 h-[46px] px-4 cursor-pointer bg-slate-50 flex justify-between items-center hover:border-slate-400 transition-all font-bold text-slate-700"
                                        onClick={() => setShowDropdown(!showDropdown)}
                                    >
                                        <span className="truncate">
                                            {currentAccount ? `${currentAccount.code} - ${currentAccount.name}` : '-- اختر الحساب --'}
                                        </span>
                                        <svg className={`w-5 h-5 transition-transform ${showDropdown ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>

                                    {showDropdown && (
                                        <div className="absolute z-50 mt-2 w-full bg-white border border-slate-200 rounded-xl shadow-2xl overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
                                            <div className="p-3 border-b border-slate-100 bg-slate-50">
                                                <input 
                                                    autoFocus
                                                    type="text"
                                                    className="w-full rounded-lg border-slate-200 text-sm p-2.5 focus:ring-slate-400"
                                                    placeholder="بحث باسم الحساب أو الكود..."
                                                    value={searchTerm}
                                                    onChange={(e) => setSearchTerm(e.target.value)}
                                                />
                                            </div>
                                            <div className="max-h-64 overflow-y-auto">
                                                {filteredAccounts.length > 0 ? (
                                                    filteredAccounts.map(acc => (
                                                        <div 
                                                            key={acc.id}
                                                            className={`p-3 text-sm cursor-pointer hover:bg-slate-50 border-b border-slate-50 flex justify-between items-center ${data.account_id == acc.id ? 'bg-blue-50 border-r-4 border-blue-600' : ''}`}
                                                            onClick={() => {
                                                                setData('account_id', acc.id);
                                                                setShowDropdown(false);
                                                                setSearchTerm('');
                                                            }}
                                                        >
                                                            <span className="font-bold text-slate-700">{acc.name}</span>
                                                            <span className="text-slate-400 font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded">{acc.code}</span>
                                                        </div>
                                                    ))
                                                ) : (
                                                    <div className="p-4 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">لا توجد نتائج</div>
                                                )}
                                            </div>
                                        </div>
                                    )}
                                </div>
                                <div className="col-span-6 md:col-span-2">
                                    <label className="block text-[11px] font-black text-slate-400 uppercase mb-1.5 mr-1">من تاريخ</label>
                                    <input type="date" className="w-full rounded-xl border-slate-200 bg-slate-50 h-[46px] px-3 font-bold text-slate-700 focus:ring-slate-900" value={data.start_date} onChange={e => setData('start_date', e.target.value)} />
                                </div>
                                <div className="col-span-6 md:col-span-2">
                                    <label className="block text-[11px] font-black text-slate-400 uppercase mb-1.5 mr-1">إلى تاريخ</label>
                                    <input type="date" className="w-full rounded-xl border-slate-200 bg-slate-50 h-[46px] px-3 font-bold text-slate-700 focus:ring-slate-900" value={data.end_date} onChange={e => setData('end_date', e.target.value)} />
                                </div>
                                <div className="col-span-12 md:col-span-3">
                                    <button type="submit" disabled={processing} className="w-full bg-slate-900 text-white h-[46px] rounded-xl font-black hover:bg-slate-800 shadow-lg shadow-slate-200 transition-all disabled:opacity-50 text-sm">تحديث البيانات</button>
                                </div>
                            </div>

                            {isControlAccount && (
                                <div className="p-4 bg-blue-50 rounded-xl border border-blue-100 animate-in slide-in-from-top-2">
                                    <label className="block text-[11px] font-black text-blue-900 uppercase mb-2 mr-1">تصفية حسب الجهة (اختياري):</label>
                                    <select className="w-full rounded-xl border-blue-200 bg-white text-sm h-[46px] px-3 font-bold text-slate-700 focus:ring-blue-500" value={data.contact_id} onChange={e => setData('contact_id', e.target.value)}>
                                        <option value="">-- جميع الجهات (إجمالي الحساب) --</option>
                                        {(filteredContactsList || []).map(contact => (
                                            <option key={contact.id} value={contact.id}>{contact.name}</option>
                                        ))}
                                    </select>
                                </div>
                            )}


                        </form>
                    </div>

                    {selectedAccount ? (
                        <div className="space-y-4">
                            <h2 className="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2 mr-2">
                                <div className="w-1.5 h-1.5 bg-slate-400 rounded-full"></div>
                                كشف حساب: {selectedAccount.name} {selectedContact && <span className="text-blue-600">/ {selectedContact.name}</span>}
                            </h2>
                            
                            <DataTable 
                                columns={columns} 
                                data={processedLines} 
                                footer={
                                    <tr className="bg-slate-900 text-white font-black text-base">
                                        <td colSpan="6" className="px-5 py-6">
                                            <div className="flex items-center justify-center gap-12">
                                                <span className="text-slate-400 text-sm font-bold italic">الرصيد الختامي في {filters.end_date}</span>
                                                <div className="flex items-baseline gap-2">
                                                    <span className="text-blue-400 text-2xl">{fmt(processedLines[processedLines.length - 1]?.runningBalance || openingBalance)}</span>
                                                    <span className="text-[10px] font-bold text-slate-500 uppercase tracking-widest">SAR</span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                }
                            />
                        </div>
                    ) : (
                        <div className="py-24 text-center bg-white rounded-2xl border border-slate-200 border-dashed">
                             <div className="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg className="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                             </div>
                             <h3 className="text-lg font-black text-slate-800">يرجى اختيار حساب لعرض كشف الحساب</h3>
                             <p className="text-slate-400 text-sm mt-1 font-bold">حدد الحساب والفترة الزمنية من الأعلى للبدء</p>
                        </div>
                    )}

                    {/* Print Only Area */}
                    <div className="hidden print:block font-serif text-slate-900" dir="rtl">
                         <div className="flex justify-between items-start border-b-4 border-slate-900 pb-6 mb-8">
                            <div className="mt-4">
                                <h1 className="text-4xl font-black">كشف حساب تفصيلي</h1>
                                <p className="text-sm font-bold mt-2">مؤسسة التفاؤل العربية - Arab Optem</p>
                            </div>
                            <div className="text-left font-bold text-sm">
                                <p>التاريخ: {new Date().toLocaleDateString('ar-SA')}</p>
                                <p>الفترة: {filters.start_date} إلى {filters.end_date}</p>
                            </div>
                         </div>
                         {/* Simple Table for Print */}
                         <table className="w-full border-2 border-slate-900">
                            <thead>
                                <tr className="bg-slate-100 border-b-2 border-slate-900">
                                    <th className="p-2 border-r border-slate-900">التاريخ</th>
                                    <th className="p-2 border-r border-slate-900">القيد</th>
                                    <th className="p-2 border-r border-slate-900">مدين</th>
                                    <th className="p-2 border-r border-slate-900">دائن</th>
                                    <th className="p-2 border-r border-slate-900">الرصيد</th>
                                    <th className="p-2">البيان</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr className="bg-slate-50 font-bold italic">
                                    <td colSpan="4" className="p-2 border-r border-slate-900">رصيد افتتاحي</td>
                                    <td className="p-2 border-r border-slate-900 text-center">{fmt(openingBalance)}</td>
                                    <td className="p-2">—</td>
                                </tr>
                                {processedLines.map(row => (
                                    <tr key={row.id} className="border-b border-slate-900">
                                        <td className="p-2 border-r border-slate-900 text-center">{row.date}</td>
                                        <td className="p-2 border-r border-slate-900 text-center">#{row.entry_no}</td>
                                        <td className="p-2 border-r border-slate-900 text-center">{row.debit > 0 ? row.debit.toFixed(2) : '0.00'}</td>
                                        <td className="p-2 border-r border-slate-900 text-center">{row.credit > 0 ? row.credit.toFixed(2) : '0.00'}</td>
                                        <td className="p-2 border-r border-slate-900 text-center font-bold">{fmt(row.runningBalance)}</td>
                                        <td className="p-2 text-sm">{row.description}</td>
                                    </tr>
                                ))}
                            </tbody>
                         </table>
                    </div>
                </div>
            </div>
            
            <style dangerouslySetInnerHTML={{__html: `
                @media print {
                    @page { size: portrait; margin: 15mm; }
                    nav, aside, header, footer, .hide-print { display: none !important; }
                    body { background: white !important; margin: 0; padding: 0; }
                    .print-area { display: block !important; }
                }
            `}} />
        </AuthenticatedLayout>
    );
}
