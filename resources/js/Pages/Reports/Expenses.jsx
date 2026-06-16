import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';
import PageHeader from '@/Components/PageHeader';
import DataTable from '@/Components/DataTable';

export default function Expenses({ auth, filters, accounts, balances, totalExpenses }) {
    const { data, setData, get, processing } = useForm({
        start_date: filters.start_date || '',
        end_date: filters.end_date || '',
        account_ids: (filters.account_ids || []).map(id => parseInt(id)),
    });

    const [showFilters, setShowFilters] = useState(false);

    const submit = (e) => {
        e.preventDefault();
        setShowFilters(false);
        get(route('reports.expenses'), { 
            preserveState: true,
            onSuccess: () => setShowFilters(false) 
        });
    };

    const toggleAccount = (id) => {
        const numericId = parseInt(id);
        const current = [...data.account_ids];
        const index = current.indexOf(numericId);
        if (index > -1) {
            current.splice(index, 1);
        } else {
            current.push(numericId);
        }
        setData('account_ids', current);
    };

    const fmt = (num) => new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0);

    const columns = [
        { key: 'code', label: 'الكود', className: 'w-24', render: (row) => <span className="font-mono font-bold text-slate-900">{row.code}</span> },
        { 
            key: 'name', 
            label: 'اسم الحساب', 
            className: 'w-64',
            render: (row) => (
                <Link 
                    href={route('reports.ledger', { account_id: row.account_id, start_date: filters.start_date, end_date: filters.end_date })}
                    className="font-bold text-slate-900 hover:text-blue-600 hover:underline transition-all"
                >
                    {row.name}
                </Link>
            )
        },
        { 
            key: 'last_desc', 
            label: 'آخر بيان / ملاحظة تم تسجيلها', 
            render: (row) => <span className="text-xs font-bold text-slate-700 leading-relaxed block max-w-md">{row.last_desc || '---'}</span> 
        },
        { 
            key: 'last_date', 
            label: 'تاريخ الحركة', 
            className: 'w-32',
            render: (row) => <span className="text-sm font-black text-slate-900 font-mono tracking-tighter">{row.last_date}</span> 
        },
        { 
            key: 'trans_count', 
            label: 'العمليات', 
            align: 'center',
            className: 'w-24',
            render: (row) => <span className="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full text-[10px] font-bold">{row.trans_count}</span> 
        },
        { 
            key: 'balance', 
            label: 'المبلغ (SAR)', 
            align: 'left', 
            className: 'w-40',
            render: (row) => <span className="font-black text-slate-900">{fmt(row.balance)}</span> 
        },
    ];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="تقرير المصروفات" />

            <div className="min-h-screen bg-slate-50 py-6" dir="rtl">
                <div className="mx-auto max-w-[1400px] px-4">
                    
                    <PageHeader 
                        title="تقرير المصروفات" 
                        subtitle="تحليل المصاريف الإدارية والعمومية"
                        backRoute={route('reports.index')}
                        middle={
                            <div className="hidden md:flex items-center gap-12 border-r border-slate-200 pr-6 mr-4">
                                <div className="flex flex-col items-center">
                                    <span className="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-none mb-1">إجمالي المصاريف</span>
                                    <span className="text-xl font-black text-slate-900 tracking-tighter">{fmt(totalExpenses)} <span className="text-[10px] text-slate-400 font-bold mr-1">SAR</span></span>
                                </div>
                                <div className="flex flex-col items-center">
                                    <span className="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-none mb-1">عدد الحسابات</span>
                                    <span className="text-xl font-black text-slate-900 tracking-tighter">{balances.length}</span>
                                </div>
                            </div>
                        }
                        actions={[
                            <a target="_blank" href={route('reports.expenses.excel', filters)} className="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-lg border border-emerald-100 hover:bg-emerald-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ملف Excel
                            </a>,
                            <a target="_blank" href={route('reports.expenses.pdf', filters)} className="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 text-sm font-bold rounded-lg border border-red-100 hover:bg-red-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ملف PDF
                            </a>,
                            <button onClick={() => window.print()} className="inline-flex items-center px-4 py-2 bg-white text-slate-700 text-sm font-bold rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                طباعة
                            </button>
                        ]}
                    />

                    {/* Compact Filter */}
                    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-6 hide-print">
                        <form onSubmit={submit} className="flex flex-wrap items-center justify-between gap-4">
                            <div className="flex items-center gap-3">
                                <input 
                                    type="date" 
                                    value={data.start_date} 
                                    onChange={e => setData('start_date', e.target.value)} 
                                    className="bg-slate-50 border-slate-200 rounded-lg px-3 py-2 text-xs font-bold text-slate-700 w-36 h-[38px] focus:ring-slate-900 focus:border-slate-900" 
                                />
                                <span className="text-slate-400 font-bold text-xs">إلى</span>
                                <input 
                                    type="date" 
                                    value={data.end_date} 
                                    onChange={e => setData('end_date', e.target.value)} 
                                    className="bg-slate-50 border-slate-200 rounded-lg px-3 py-2 text-xs font-bold text-slate-700 w-36 h-[38px] focus:ring-slate-900 focus:border-slate-900" 
                                />
                                <button 
                                    type="button" 
                                    onClick={() => setShowFilters(!showFilters)} 
                                    className={`px-4 h-[38px] rounded-lg text-xs font-bold border transition-all ${data.account_ids.length > 0 ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-slate-50 border-slate-200 text-slate-600'}`}
                                >
                                    ⚙️ الحسابات ({data.account_ids.length})
                                </button>
                                <button 
                                    type="submit" 
                                    disabled={processing} 
                                    className="bg-slate-900 text-white font-bold h-[38px] px-8 rounded-lg hover:bg-slate-800 transition-all text-xs disabled:opacity-50"
                                >
                                    تحديث
                                </button>
                            </div>

                            {showFilters && (
                                <div className="p-4 bg-slate-50 rounded-xl border border-slate-200 animate-in fade-in slide-in-from-top-2 duration-200">
                                    <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2">
                                        {accounts.map(acc => (
                                            <label key={acc.id} className="flex items-center gap-2 p-2 bg-white hover:bg-blue-50 rounded-lg cursor-pointer transition-colors border border-slate-100 hover:border-blue-200 group">
                                                <input 
                                                    type="checkbox" 
                                                    className="rounded text-blue-600 focus:ring-blue-500 w-3 h-3"
                                                    checked={data.account_ids.includes(acc.id)}
                                                    onChange={() => toggleAccount(acc.id)}
                                                />
                                                <span className="text-[11px] font-bold text-slate-600 group-hover:text-blue-700 truncate">{acc.name}</span>
                                            </label>
                                        ))}
                                    </div>
                                    <div className="col-span-full pt-3 border-t border-slate-200 mt-4 flex justify-end gap-4">
                                        <button type="button" onClick={() => setData('account_ids', [])} className="text-[10px] font-bold text-red-500 hover:text-red-700">إلغاء الكل</button>
                                        <button type="button" onClick={() => setData('account_ids', accounts.map(a => parseInt(a.id)))} className="text-[10px] font-bold text-blue-600 hover:text-blue-800">تحديد الكل</button>
                                    </div>
                                </div>
                            )}
                        </form>
                    </div>

                    <DataTable 
                        columns={columns} 
                        data={balances} 
                        footer={
                            <tr className="bg-slate-900 text-white font-black text-sm">
                                <td colSpan="5" className="px-5 py-4 text-center border-t border-slate-800 uppercase tracking-widest">الإجمالي الكلي للمصروفات</td>
                                <td className="px-5 py-4 text-left border-t border-slate-800">{fmt(totalExpenses)}</td>
                            </tr>
                        }
                    />

                </div>
            </div>
            <style dangerouslySetInnerHTML={{__html: `
                @media print {
                    body { background: white !important; }
                    nav, aside, header, .hide-print { display: none !important; }
                    .print-area { position: static; width: 100%; border: none !important; box-shadow: none !important; margin: 0; padding: 0; }
                }
            `}} />
        </AuthenticatedLayout>
    );
}
