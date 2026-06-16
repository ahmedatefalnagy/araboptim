import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';
import PageHeader from '@/Components/PageHeader';
import DataTable from '@/Components/DataTable';

export default function FixedAssets({ auth, filters, accounts, schedule, totals }) {
    const { data, setData, get, processing } = useForm({
        as_of_date: filters.as_of_date || '',
        account_ids: filters.account_ids || [],
    });

    const [showFilters, setShowFilters] = useState(false);

    const submit = (e) => {
        e.preventDefault();
        setShowFilters(false);
        get(route('reports.fixedAssets'), { preserveState: true });
    };

    const toggleAccount = (id) => {
        const current = [...data.account_ids];
        const index = current.indexOf(id);
        if (index > -1) {
            current.splice(index, 1);
        } else {
            current.push(id);
        }
        setData('account_ids', current);
    };

    const fmt = (num) => new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0);

    const columns = [
        { 
            key: 'name', 
            label: 'اسم الأصل / الحساب', 
            className: 'min-w-[250px]',
            render: (row) => (
                <div>
                    <div className="font-bold text-slate-900 text-xs">{row.name}</div>
                    <span className="text-[10px] text-slate-400 font-mono">{row.code}</span>
                </div>
            )
        },
        { 
            key: 'opening_asset', 
            label: 'رصيد الأصل 01-01-2025', 
            align: 'left', 
            render: (row) => <span className="font-bold text-slate-700 text-[11px]">{fmt(row.opening_asset)}</span> 
        },
        { 
            key: 'opening_acc_dep', 
            label: 'مجمع الإهلاك 01-01-2025', 
            align: 'left', 
            render: (row) => <span className="font-bold text-rose-500 text-[11px]">{fmt(row.opening_acc_dep)}</span> 
        },
        { 
            key: 'nbv_opening', 
            label: 'صافي رصيد الأصل 01-01-2025', 
            align: 'left', 
            className: 'bg-slate-50/50',
            render: (row) => <span className="font-black text-slate-900 text-[11px]">{fmt(row.nbv_opening)}</span> 
        },
        { 
            key: 'rate', 
            label: 'نسبة الإهلاك', 
            align: 'center', 
            render: (row) => <span className="font-black text-indigo-600 text-xs">{row.rate}%</span> 
        },
        { 
            key: 'dep_for_year', 
            label: 'الإهلاك (2025)', 
            align: 'left', 
            className: 'bg-indigo-50/30',
            render: (row) => <span className="font-black text-indigo-700 text-[11px]">{fmt(row.dep_for_year)}</span> 
        },
        { 
            key: 'nbv_closing', 
            label: 'صافي القيمة الدفترية حالياً', 
            align: 'left', 
            render: (row) => <span className="font-bold text-slate-400 text-[11px] italic">{fmt(row.nbv_closing)}</span> 
        },
    ];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="جدول الأصول والإهلاك التفصيلي" />

            <div className="min-h-screen bg-slate-50 py-6" dir="rtl">
                <div className="mx-auto max-w-[1500px] px-4">
                    
                    <PageHeader 
                        title="جدول الأصول والإهلاك" 
                        subtitle="بيان التكلفة، الإضافات، ومجمع الإهلاك للسنة المالية"
                        backRoute={route('reports.index')}
                        middle={
                            <div className="hidden xl:flex items-center gap-8 border-r border-slate-200 pr-6 mr-4">
                                <div className="flex flex-col items-center">
                                    <span className="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-none mb-1">صافي قيمة الأصول</span>
                                    <span className="text-xl font-black text-indigo-700 tracking-tighter">{fmt(totals.nbv_closing)} <span className="text-[10px] text-slate-400 font-bold mr-1">SAR</span></span>
                                </div>
                                <div className="flex flex-col items-center border-r border-slate-100 pr-8">
                                    <span className="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-none mb-1">إجمالي الإضافات</span>
                                    <span className="text-xl font-black text-emerald-600 tracking-tighter">+{fmt(totals.additions)}</span>
                                </div>
                            </div>
                        }
                        actions={[
                            <a target="_blank" href={route('reports.fixedAssets.excel', filters)} className="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-lg border border-emerald-100 hover:bg-emerald-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                Excel
                            </a>,
                            <a target="_blank" href={route('reports.fixedAssets.pdf', filters)} className="inline-flex items-center px-4 py-2 bg-rose-50 text-rose-700 text-sm font-bold rounded-lg border border-rose-100 hover:bg-rose-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 9h1m0 4h3m-3 4h3" /></svg>
                                PDF
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
                                <div className="relative">
                                    <input 
                                        type="date" 
                                        value={data.as_of_date} 
                                        onChange={e => setData('as_of_date', e.target.value)} 
                                        className="bg-slate-50 border-slate-200 rounded-lg px-2 py-1.5 text-xs font-bold text-slate-700 w-40 focus:ring-slate-900 focus:border-slate-900" 
                                    />
                                    <span className="absolute -top-2 right-2 px-1 bg-white text-[8px] font-bold text-slate-400">حتى تاريخ</span>
                                </div>
                                <button 
                                    type="button" 
                                    onClick={() => setShowFilters(!showFilters)} 
                                    className={`px-4 py-1.5 rounded-lg text-xs font-bold border transition-all ${data.account_ids.length > 0 ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-slate-50 border-slate-200 text-slate-600'}`}
                                >
                                    ⚙️ الحسابات ({data.account_ids.length})
                                </button>
                                <button 
                                    type="submit" 
                                    disabled={processing} 
                                    className="bg-slate-900 text-white font-bold py-1.5 px-6 rounded-lg hover:bg-slate-800 transition-all text-xs disabled:opacity-50"
                                >
                                    تحديث التقرير
                                </button>
                            </div>

                            {showFilters && (
                                <div className="w-full p-4 bg-slate-50 rounded-xl border border-slate-200 animate-in fade-in slide-in-from-top-2 duration-200 mt-2">
                                    <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2">
                                        {accounts.map(acc => (
                                            <label key={acc.id} className="flex items-center gap-2 p-2 bg-white hover:bg-indigo-50 rounded-lg cursor-pointer transition-colors border border-slate-100 hover:border-indigo-200 group">
                                                <input 
                                                    type="checkbox" 
                                                    className="rounded text-indigo-600 focus:ring-indigo-500 w-3 h-3"
                                                    checked={data.account_ids.includes(acc.id)}
                                                    onChange={() => toggleAccount(acc.id)}
                                                />
                                                <span className="text-[11px] font-bold text-slate-600 group-hover:text-indigo-700 truncate">{acc.name}</span>
                                            </label>
                                        ))}
                                    </div>
                                    <div className="pt-3 border-t border-slate-200 mt-4 flex justify-end gap-4">
                                        <button type="button" onClick={() => setData('account_ids', [])} className="text-[10px] font-bold text-red-500 hover:text-red-700">إلغاء الكل</button>
                                        <button type="button" onClick={() => setData('account_ids', accounts.map(a => a.id))} className="text-[10px] font-bold text-indigo-600 hover:text-indigo-800">تحديد الكل</button>
                                    </div>
                                </div>
                            )}
                        </form>
                    </div>

                    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <DataTable 
                            columns={columns} 
                            data={schedule} 
                            footer={
                                <tr className="bg-slate-900 text-white font-black text-[11px]">
                                    <td className="px-5 py-4 text-center border-t border-slate-800 uppercase tracking-widest">الإجمالي</td>
                                    <td className="px-5 py-4 text-left border-t border-slate-800 text-slate-300">{fmt(totals.opening_asset)}</td>
                                    <td className="px-5 py-4 text-left border-t border-slate-800 text-rose-300">{fmt(totals.opening_acc_dep)}</td>
                                    <td className="px-5 py-4 text-left border-t border-slate-800 bg-slate-800">{fmt(totals.nbv_opening)}</td>
                                    <td className="px-5 py-4 text-center border-t border-slate-800 opacity-50">---</td>
                                    <td className="px-5 py-4 text-left border-t border-slate-800 bg-indigo-900/50 text-indigo-100">{fmt(totals.dep_for_year)}</td>
                                    <td className="px-5 py-4 text-left border-t border-slate-800 italic opacity-50">{fmt(totals.nbv_closing)}</td>
                                </tr>
                            }
                        />
                    </div>

                </div>
            </div>
            <style dangerouslySetInnerHTML={{__html: `
                @media print {
                    body { background: white !important; font-size: 10px; }
                    nav, aside, header, .hide-print { display: none !important; }
                    .print-area { position: static; width: 100%; border: none !important; box-shadow: none !important; margin: 0; padding: 0; }
                    td, th { padding: 4px !important; }
                }
            `}} />
        </AuthenticatedLayout>
    );
}
