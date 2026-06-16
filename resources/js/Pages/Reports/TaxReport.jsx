import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import DataTable from '@/Components/DataTable';

export default function TaxReport({ auth, accounts, filters, salesTaxEntries, purchaseTaxEntries, totals }) {
    const { data, setData, get, processing } = useForm({
        year: filters.year || new Date().getFullYear(),
        quarter: filters.quarter || 1,
        sales_account_id: filters.sales_account_id || '',
        sales_returns_account_id: filters.sales_returns_account_id || '',
        purchases_account_id: filters.purchases_account_id || '',
        purchases_returns_account_id: filters.purchases_returns_account_id || '',
    });

    const submit = (e) => {
        e.preventDefault();
        get(route('reports.tax'), { preserveState: true });
    };

    const fmt = (num) => new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0);

    const detailColumns = [
        { key: 'date', label: 'التاريخ', className: 'w-24', tdClassName: 'font-mono font-bold text-slate-400' },
        { key: 'entry_no', label: 'رقم القيد', className: 'w-24', render: (row) => <span className="font-mono text-slate-500">#{row.entry_no}</span> },
        { key: 'description', label: 'البيان', render: (row) => <div className="text-[11px] text-slate-600 leading-relaxed">{row.description} <span className="text-slate-300 mx-1">•</span> <span className="text-slate-400">{row.account_name}</span></div> },
        { key: 'type', label: 'النوع', align: 'center', render: (row) => (
            <span className={`px-2 py-0.5 rounded-[4px] text-[9px] font-black uppercase tracking-widest ${
                row.type === 'return' ? 'bg-rose-50 text-rose-600' : 'bg-blue-50 text-blue-600'
            }`}>{row.type === 'return' ? 'مردود' : 'أساسي'}</span>
        )},
        { key: 'base_amount', label: 'المبلغ الأساس', align: 'center', tdClassName: 'font-bold text-slate-500', render: (row) => fmt(row.base_amount) },
        { key: 'tax_amount', label: 'الضريبة (15%)', align: 'center', tdClassName: 'font-black text-slate-950', render: (row) => fmt(row.tax_amount) },
    ];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="التقرير الضريبي" />

            <div className="min-h-screen bg-slate-50 py-6" dir="rtl">
                <div className="mx-auto max-w-[1400px] px-4">
                    
                    <PageHeader 
                        title="إقرار ضريبة القيمة المضافة (VAT Report)" 
                        subtitle={`الربع ${filters.quarter} سنة ${filters.year} • احتساب تلقائي 15%`}
                        backRoute={route('reports.index')}
                        stats={[
                            { label: 'ضريبة المخرجات', value: fmt(totals.output_tax), color: 'blue' },
                            { label: 'ضريبة المدخلات', value: fmt(totals.input_tax), color: 'orange' },
                            { label: 'الصافي للمصلحة', value: fmt(totals.net_vat), color: totals.net_vat > 0 ? 'red' : 'emerald' }
                        ]}
                        actions={[
                            <a target="_blank" href={route('reports.tax.excel', filters)} className="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-lg border border-emerald-100 hover:bg-emerald-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ملف Excel
                            </a>,
                            <a target="_blank" href={route('reports.tax.pdf', filters)} className="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 text-sm font-bold rounded-lg border border-red-100 hover:bg-red-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ملف PDF
                            </a>,
                            <button onClick={() => window.print()} className="inline-flex items-center px-4 py-2 bg-white text-slate-700 text-sm font-bold rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                طباعة
                            </button>
                        ]}
                    />

                    {/* Elite Filter Grid */}
                    <div className="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 mb-8 hide-print">
                        <form onSubmit={submit} className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div className="space-y-1.5">
                                    <label className="text-[10px] font-black text-blue-600 uppercase tracking-widest mr-1">حساب المبيعات</label>
                                    <select value={data.sales_account_id} onChange={e => setData('sales_account_id', e.target.value)} className="w-full bg-blue-50/30 border-blue-100 rounded-xl px-3 py-2 text-sm font-bold text-slate-700 focus:ring-blue-200 transition-all">
                                        <option value="">-- اختر الحساب --</option>
                                        {accounts.map(acc => <option key={acc.id} value={acc.id}>{acc.code} - {acc.name}</option>)}
                                    </select>
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-[10px] font-black text-blue-400 uppercase tracking-widest mr-1">مردودات المبيعات</label>
                                    <select value={data.sales_returns_account_id} onChange={e => setData('sales_returns_account_id', e.target.value)} className="w-full bg-slate-50 border-slate-200 rounded-xl px-3 py-2 text-sm font-bold text-slate-700">
                                        <option value="">-- اختياري --</option>
                                        {accounts.map(acc => <option key={acc.id} value={acc.id}>{acc.code} - {acc.name}</option>)}
                                    </select>
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-[10px] font-black text-orange-600 uppercase tracking-widest mr-1">حساب المشتريات</label>
                                    <select value={data.purchases_account_id} onChange={e => setData('purchases_account_id', e.target.value)} className="w-full bg-orange-50/30 border-orange-100 rounded-xl px-3 py-2 text-sm font-bold text-slate-700 focus:ring-orange-200 transition-all">
                                        <option value="">-- اختر الحساب --</option>
                                        {accounts.map(acc => <option key={acc.id} value={acc.id}>{acc.code} - {acc.name}</option>)}
                                    </select>
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-[10px] font-black text-orange-400 uppercase tracking-widest mr-1">مردودات المشتريات</label>
                                    <select value={data.purchases_returns_account_id} onChange={e => setData('purchases_returns_account_id', e.target.value)} className="w-full bg-slate-50 border-slate-200 rounded-xl px-3 py-2 text-sm font-bold text-slate-700">
                                        <option value="">-- اختياري --</option>
                                        {accounts.map(acc => <option key={acc.id} value={acc.id}>{acc.code} - {acc.name}</option>)}
                                    </select>
                                </div>
                            </div>
                            <div className="flex flex-wrap items-end gap-6 pt-4 border-t border-slate-100">
                                <div className="flex-1 min-w-[150px] space-y-1.5">
                                    <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest">السنة والربع</label>
                                    <div className="flex gap-2">
                                        <input type="number" value={data.year} onChange={e => setData('year', e.target.value)} className="w-24 bg-slate-50 border-slate-200 rounded-xl px-3 py-2 text-sm font-bold text-slate-700" />
                                        <select value={data.quarter} onChange={e => setData('quarter', e.target.value)} className="flex-1 bg-slate-50 border-slate-200 rounded-xl px-3 py-2 text-sm font-bold text-slate-700">
                                            <option value="1">الربع الأول</option>
                                            <option value="2">الربع الثاني</option>
                                            <option value="3">الربع الثالث</option>
                                            <option value="4">الربع الرابع</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" disabled={processing} className="bg-slate-900 text-white font-black px-12 py-3 rounded-xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-100 disabled:opacity-50">تحديث الإقرار الضريبي</button>
                            </div>
                        </form>
                    </div>

                    <div className="space-y-12">
                         <div className="space-y-4">
                            <h2 className="text-sm font-black text-blue-700 uppercase tracking-[0.2em] flex items-center gap-2 mr-2">
                                <div className="w-2 h-2 bg-blue-600 rounded-full"></div>
                                تفاصيل ضريبة المخرجات (المبيعات)
                            </h2>
                            <DataTable columns={detailColumns} data={salesTaxEntries} />
                         </div>

                         <div className="space-y-4">
                            <h2 className="text-sm font-black text-orange-700 uppercase tracking-[0.2em] flex items-center gap-2 mr-2">
                                <div className="w-2 h-2 bg-orange-600 rounded-full"></div>
                                تفاصيل ضريبة المدخلات (المشتريات)
                            </h2>
                            <DataTable columns={detailColumns} data={purchaseTaxEntries} />
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
