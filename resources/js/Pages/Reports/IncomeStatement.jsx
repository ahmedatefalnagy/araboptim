import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import DataTable from '@/Components/DataTable';

export default function IncomeStatement({ auth, filters, revenues, expenses, totalRevenue, totalExpense, netIncome }) {
    const { data, setData, get, processing } = useForm({
        start_date: filters.start_date || '',
        end_date: filters.end_date || '',
    });

    const submit = (e) => {
        e.preventDefault();
        get(route('reports.incomeStatement'), { preserveState: true });
    };

    const fmt = (num) => new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0);

    const columns = [
        { key: 'code', label: 'كود الحساب', className: 'w-24', tdClassName: 'font-mono font-bold text-slate-400' },
        { key: 'name', label: 'اسم الحساب', tdClassName: 'font-bold text-slate-900' },
        { key: 'balance', label: 'المبلغ (SAR)', align: 'left', tdClassName: 'font-black text-slate-700', render: (row) => fmt(row.balance) },
    ];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="قائمة الدخل" />

            <div className="min-h-screen bg-slate-50 py-6" dir="rtl">
                <div className="mx-auto max-w-[1000px] px-4">
                    
                    <PageHeader 
                        title="قائمة الدخل (Income Statement)" 
                        subtitle="تحليل الإيرادات والمصروفات وصافي الأرباح"
                        backRoute={route('reports.index')}
                        stats={[
                            { label: 'إجمالي الإيرادات', value: fmt(totalRevenue), color: 'emerald' },
                            { label: 'إجمالي المصروفات', value: fmt(totalExpense), color: 'red' },
                            { label: 'صافي الدخل', value: fmt(netIncome), color: netIncome >= 0 ? 'blue' : 'orange' }
                        ]}
                        actions={[
                            <a target="_blank" href={route('reports.incomeStatement.excel', filters)} className="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-lg border border-emerald-100 hover:bg-emerald-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ملف Excel
                            </a>,
                            <a target="_blank" href={route('reports.incomeStatement.pdf', filters)} className="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 text-sm font-bold rounded-lg border border-red-100 hover:bg-red-100 transition-colors">
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
                        <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                            <div className="space-y-1.5">
                                <label className="text-[11px] font-black text-slate-400 uppercase tracking-widest mr-1">من تاريخ</label>
                                <input type="date" className="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 font-bold text-slate-700" value={data.start_date} onChange={e => setData('start_date', e.target.value)} />
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-[11px] font-black text-slate-400 uppercase tracking-widest mr-1">إلى تاريخ</label>
                                <input type="date" className="w-full rounded-xl border-slate-200 bg-slate-50 p-2.5 font-bold text-slate-700" value={data.end_date} onChange={e => setData('end_date', e.target.value)} />
                            </div>
                            <button type="submit" disabled={processing} className="bg-slate-900 text-white font-black py-3 rounded-xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-100 disabled:opacity-50">تحديث القائمة</button>
                        </form>
                    </div>

                    <div className="space-y-8">
                        <div className="space-y-3">
                            <h2 className="text-xs font-black text-emerald-600 uppercase tracking-widest flex items-center gap-2 mr-2">الإيرادات (Revenues)</h2>
                            <DataTable columns={columns} data={revenues} />
                        </div>

                        <div className="space-y-3">
                            <h2 className="text-xs font-black text-rose-600 uppercase tracking-widest flex items-center gap-2 mr-2">المصروفات (Expenses)</h2>
                            <DataTable columns={columns} data={expenses} />
                        </div>

                        {/* Final Result Card */}
                        <div className={`p-8 rounded-[2.5rem] border-2 shadow-2xl flex justify-between items-center transition-all ${netIncome >= 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200'}`}>
                             <div>
                                 <h4 className="text-xs font-black text-slate-400 uppercase tracking-[0.3em] mb-1">{netIncome >= 0 ? 'صافي الربح' : 'صافي الخسارة'}</h4>
                                 <div className={`text-4xl font-black ${netIncome >= 0 ? 'text-emerald-900' : 'text-rose-900'}`}>
                                     {fmt(netIncome)} <span className="text-sm font-bold text-slate-400">SAR</span>
                                 </div>
                             </div>
                             <div className={`w-16 h-16 rounded-2xl flex items-center justify-center text-4xl ${netIncome >= 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'}`}>
                                 {netIncome >= 0 ? '🏆' : '⚠️'}
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
