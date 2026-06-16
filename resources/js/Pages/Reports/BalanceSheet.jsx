import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import DataTable from '@/Components/DataTable';

export default function BalanceSheet({ auth, filters, assets, liabilities, equity, totalAssets, totalLiabilities, totalEquity }) {
    const { data, setData, get, processing } = useForm({
        as_of_date: filters.as_of_date || '',
    });

    const submit = (e) => {
        e.preventDefault();
        get(route('reports.balanceSheet'), { preserveState: true });
    };

    const fmt = (num) => new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0);

    const columns = [
        { key: 'code', label: 'كود الحساب', className: 'w-24', tdClassName: 'font-mono font-bold text-slate-400' },
        { key: 'name', label: 'اسم الحساب', tdClassName: 'font-bold text-slate-900' },
        { key: 'balance', label: 'المبلغ (SAR)', align: 'left', tdClassName: 'font-black text-slate-700', render: (row) => fmt(row.balance) },
    ];

    const isBalanced = Math.abs(totalAssets - (totalLiabilities + totalEquity)) < 0.1;

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="المركز المالي" />

            <div className="min-h-screen bg-slate-50 py-6" dir="rtl">
                <div className="mx-auto max-w-[1200px] px-4">
                    
                    <PageHeader 
                        title="قائمة المركز المالي (Balance Sheet)" 
                        subtitle={`تحليل الأصول والخصوم وحقوق الملكية • كما في ${filters.as_of_date}`}
                        backRoute={route('reports.index')}
                        stats={[
                            { label: 'إجمالي الأصول', value: fmt(totalAssets), color: 'blue' },
                            { label: 'الخصوم وحقوق الملكية', value: fmt(totalLiabilities + totalEquity), color: isBalanced ? 'emerald' : 'red' },
                            { label: 'حالة التوازن', value: isBalanced ? 'متوازنة' : 'غير متوازنة', color: isBalanced ? 'emerald' : 'red' }
                        ]}
                        actions={[
                            <a target="_blank" href={route('reports.balanceSheet.excel', filters)} className="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-lg border border-emerald-100 hover:bg-emerald-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ملف Excel
                            </a>,
                            <a target="_blank" href={route('reports.balanceSheet.pdf', filters)} className="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 text-sm font-bold rounded-lg border border-red-100 hover:bg-red-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ملف PDF
                            </a>,
                            <button onClick={() => window.print()} className="inline-flex items-center px-4 py-2 bg-white text-slate-700 text-sm font-bold rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                طباعة
                            </button>
                        ]}
                    />

                    {/* Filter */}
                    <div className="bg-white p-6 rounded-2xl shadow-sm mb-8 border border-slate-200 hide-print">
                        <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                            <div className="space-y-1.5">
                                <label className="text-[11px] font-black text-slate-400 uppercase tracking-widest mr-1">تاريخ التقرير / As of Date</label>
                                <input type="date" className="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 font-bold text-slate-700" value={data.as_of_date} onChange={e => setData('as_of_date', e.target.value)} />
                            </div>
                            <button type="submit" disabled={processing} className="bg-slate-900 text-white font-black py-3 rounded-xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-100 disabled:opacity-50">تحديث المركز المالي</button>
                        </form>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div className="space-y-4">
                            <h2 className="text-sm font-black text-blue-700 uppercase tracking-widest flex items-center gap-2 mr-2">الأصول (Assets)</h2>
                            <DataTable columns={columns} data={assets} footer={
                                <tr className="bg-blue-600 text-white font-black">
                                    <td colSpan="2" className="px-5 py-4 text-center">إجمالي الأصول</td>
                                    <td className="px-5 py-4 text-left">{fmt(totalAssets)}</td>
                                </tr>
                            } />
                        </div>

                        <div className="space-y-8">
                            <div className="space-y-4">
                                <h2 className="text-sm font-black text-rose-700 uppercase tracking-widest flex items-center gap-2 mr-2">الخصوم (Liabilities)</h2>
                                <DataTable columns={columns} data={liabilities} footer={
                                    <tr className="bg-rose-600 text-white font-black">
                                        <td colSpan="2" className="px-5 py-4 text-center">إجمالي الخصوم</td>
                                        <td className="px-5 py-4 text-left">{fmt(totalLiabilities)}</td>
                                    </tr>
                                } />
                            </div>

                            <div className="space-y-4">
                                <h2 className="text-sm font-black text-emerald-700 uppercase tracking-widest flex items-center gap-2 mr-2">حقوق الملكية (Equity)</h2>
                                <DataTable columns={columns} data={equity} footer={
                                    <tr className="bg-emerald-600 text-white font-black">
                                        <td colSpan="2" className="px-5 py-4 text-center">إجمالي حقوق الملكية</td>
                                        <td className="px-5 py-4 text-left">{fmt(totalEquity)}</td>
                                    </tr>
                                } />
                            </div>

                            {/* Balance Card */}
                            <div className={`p-8 rounded-[2.5rem] border-2 shadow-2xl flex justify-between items-center transition-all ${isBalanced ? 'bg-slate-900 border-slate-900 text-white' : 'bg-rose-50 border-rose-200 text-rose-900'}`}>
                                 <div>
                                     <h4 className="text-xs font-black opacity-50 uppercase tracking-[0.3em] mb-1">الخصوم + حقوق الملكية</h4>
                                     <div className="text-4xl font-black">
                                         {fmt(totalLiabilities + totalEquity)} <span className="text-sm font-bold opacity-50">SAR</span>
                                     </div>
                                 </div>
                                 {!isBalanced && (
                                     <div className="text-right">
                                         <p className="text-[10px] font-black uppercase tracking-widest mb-1">الفرق غير المتوازن</p>
                                         <p className="text-xl font-black">{fmt(totalAssets - (totalLiabilities + totalEquity))}</p>
                                     </div>
                                 )}
                                 <div className={`w-16 h-16 rounded-2xl flex items-center justify-center text-4xl ${isBalanced ? 'bg-white/10' : 'bg-rose-100 text-rose-600'}`}>
                                     {isBalanced ? '💎' : '🧨'}
                                 </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <style dangerouslySetInnerHTML={{__html: `
                @media print {
                    nav, aside, header, footer, .hide-print { display: none !important; }
                    body { background: white !important; }
                }
            `}} />
        </AuthenticatedLayout>
    );
}
