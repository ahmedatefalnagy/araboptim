import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import DataTable from '@/Components/DataTable';

export default function TrialBalance({ auth, filters, balances, totals }) {
    const { data, setData, get, processing } = useForm({
        start_date: filters.start_date || '',
        end_date: filters.end_date || '',
        report_type: filters.report_type || 'detailed',
        max_level: filters.max_level || '',
    });

    const submit = (e) => {
        e.preventDefault();
        get(route('reports.trialBalance'), { preserveState: true });
    };

    const totalDebit = totals.debit;
    const totalCredit = totals.credit;
    const totalBalanceDebit = totals.balance_debit;
    const totalBalanceCredit = totals.balance_credit;

    const fmt = (num) => new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0);

    const columns = [
        { 
            key: 'code', 
            label: 'كود الحساب', 
            className: 'whitespace-nowrap min-w-[120px]', 
            tdClassName: 'font-mono font-bold text-slate-400 whitespace-nowrap' 
        },
        { 
            key: 'name', 
            label: 'اسم الحساب', 
            className: 'w-1/3', 
            render: (row) => (
                <div style={{ paddingRight: row.level ? `${(row.level - 1) * 20}px` : '0px' }} className={!row.is_postable ? 'font-black text-slate-900' : 'text-slate-600'}>
                    {!row.is_postable && <span className="ml-2 text-blue-500">●</span>}
                    {row.name}
                </div>
            )
        },
        { key: 'debit', label: 'مجاميع مدين', align: 'center', tdClassName: (row) => !row.is_postable ? 'font-bold text-slate-900 bg-slate-50/50' : 'text-slate-600', render: (row) => fmt(row.debit) },
        { key: 'credit', label: 'مجاميع دائن', align: 'center', tdClassName: (row) => !row.is_postable ? 'font-bold text-slate-900 bg-slate-50/50' : 'text-slate-600', render: (row) => fmt(row.credit) },
        { key: 'balance_debit', label: 'رصيد مدين', align: 'center', tdClassName: (row) => !row.is_postable ? 'bg-blue-100/30 font-black text-blue-900' : 'bg-blue-50/30 font-black text-blue-900', render: (row) => row.balance_debit > 0 ? fmt(row.balance_debit) : '-' },
        { key: 'balance_credit', label: 'رصيد دائن', align: 'center', tdClassName: (row) => !row.is_postable ? 'bg-blue-100/30 font-black text-blue-900' : 'bg-blue-50/30 font-black text-blue-900', render: (row) => row.balance_credit > 0 ? fmt(row.balance_credit) : '-' },
    ];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="ميزان المراجعة" />

            <div className="min-h-screen bg-slate-50 py-6" dir="rtl">
                <div className="mx-auto max-w-[1400px] px-4">
                    
                    <PageHeader 
                        title="ميزان المراجعة (Trial Balance)" 
                        subtitle="المجاميع والأرصدة للتحقق من التوازن الإجمالي"
                        backRoute={route('reports.index')}
                        stats={[
                            { label: 'إجمالي مدين', value: fmt(totalDebit), prefix: 'SAR' },
                            { label: 'إجمالي دائن', value: fmt(totalCredit), prefix: 'SAR' },
                            { label: 'الفرق المتبقي', value: fmt(Math.abs(totalBalanceDebit - totalBalanceCredit)), color: Math.abs(totalBalanceDebit - totalBalanceCredit) > 0.01 ? 'red' : 'emerald' }
                        ]}
                        actions={[
                            <a target="_blank" href={route('reports.trialBalance.excel', filters)} className="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-lg border border-emerald-100 hover:bg-emerald-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ملف Excel
                            </a>,
                            <a target="_blank" href={route('reports.trialBalance.pdf', filters)} className="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 text-sm font-bold rounded-lg border border-red-100 hover:bg-red-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ملف PDF
                            </a>,
                            <button onClick={() => window.print()} className="inline-flex items-center px-4 py-2 bg-white text-slate-700 text-sm font-bold rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                طباعة
                            </button>
                        ]}
                    />

                    {/* Filter Form */}
                    <div className="bg-white p-6 rounded-2xl shadow-sm mb-8 border border-slate-200 hide-print">
                        <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                            <div className="space-y-1.5">
                                <label className="text-[11px] font-black text-slate-400 uppercase tracking-widest mr-1">نوع التقرير</label>
                                <select className="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 font-bold text-slate-700" value={data.report_type} onChange={e => setData('report_type', e.target.value)}>
                                    <option value="detailed">تفصيلي (جميع الحسابات)</option>
                                    <option value="summary">تجميعي (حسابات الأب)</option>
                                </select>
                            </div>

                            {data.report_type === 'summary' && (
                                <div className="space-y-1.5 animate-in fade-in slide-in-from-right-4">
                                    <label className="text-[11px] font-black text-slate-400 uppercase tracking-widest mr-1">مستوى التجميع</label>
                                    <select className="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 font-bold text-slate-700" value={data.max_level} onChange={e => setData('max_level', e.target.value)}>
                                        <option value="">جميع المستويات</option>
                                        <option value="1">المستوى الأول (1)</option>
                                        <option value="2">المستوى الثاني (2)</option>
                                        <option value="3">المستوى الثالث (3)</option>
                                        <option value="4">المستوى الرابع (4)</option>
                                    </select>
                                </div>
                            )}

                            <div className="space-y-1.5">
                                <label className="text-[11px] font-black text-slate-400 uppercase tracking-widest mr-1">من تاريخ</label>
                                <input type="date" className="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 font-bold text-slate-700" value={data.start_date} onChange={e => setData('start_date', e.target.value)} />
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-[11px] font-black text-slate-400 uppercase tracking-widest mr-1">إلى تاريخ</label>
                                <input type="date" className="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 font-bold text-slate-700" value={data.end_date} onChange={e => setData('end_date', e.target.value)} />
                            </div>
                            <button type="submit" disabled={processing} className="bg-slate-900 text-white font-black py-3 rounded-xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-100 disabled:opacity-50">تحديث الميزان</button>
                        </form>
                    </div>

                    <DataTable 
                        columns={columns} 
                        data={balances} 
                        footer={
                            <tr className="bg-slate-900 text-white font-black text-base">
                                <td colSpan="2" className="px-5 py-4 text-center">إجمالي ميزان المراجعة المطابق</td>
                                <td className="px-5 py-4 text-center text-slate-300">{fmt(totalDebit)}</td>
                                <td className="px-5 py-4 text-center text-slate-300">{fmt(totalCredit)}</td>
                                <td className={`px-5 py-4 text-center ${Math.abs(totalBalanceDebit - totalBalanceCredit) > 0.01 ? 'text-red-400' : 'text-blue-400'}`}>{fmt(totalBalanceDebit)}</td>
                                <td className={`px-5 py-4 text-center ${Math.abs(totalBalanceDebit - totalBalanceCredit) > 0.01 ? 'text-red-400' : 'text-blue-400'}`}>{fmt(totalBalanceCredit)}</td>
                            </tr>
                        }
                    />

                </div>
            </div>
            
            <style dangerouslySetInnerHTML={{__html: `
                @media print {
                    @page { size: landscape; margin: 10mm; }
                    nav, aside, header, footer, .hide-print { display: none !important; }
                    body { background: white !important; }
                }
            `}} />
        </AuthenticatedLayout>
    );
}
