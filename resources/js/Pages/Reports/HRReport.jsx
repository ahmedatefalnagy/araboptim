import { Head, usePage, router } from '@inertiajs/react';
import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import DataTable from '@/Components/DataTable';

export default function HRReport({ auth, employees, reportData, detailedTransactions, filters, totals }) {
    const [data, setData] = useState({
        employee_id: filters.employee_id || '',
        start_date: filters.start_date || '2025-01-01',
        end_date: filters.end_date || new Date().toISOString().split('T')[0],
        type: filters.type || '',
        status: filters.status || '',
    });

    const handleFilter = (e) => {
        e.preventDefault();
        router.get(route('reports.hr'), data, { preserveState: true });
    };

    const exportPdf = () => window.open(route('reports.hr.pdf', data), '_blank');
    const exportExcel = () => window.open(route('reports.hr.excel', data), '_blank');

    const fmt = (num) => new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0);

    const typeLabels = { advance: 'سلفة', custody: 'عهدة', bonus: 'مكافأة' };
    const statusLabels = { open: 'قيد الانتظار', settled: 'تمت التسوية' };

    const summaryColumns = [
        { key: 'name', label: 'الموظف', className: 'font-bold text-slate-900' },
        { key: 'basic_salary', label: 'الراتب الأساسي', align: 'center', render: (row) => fmt(row.basic_salary) },
        { 
            key: 'advances', 
            label: 'رصيد السلف', 
            align: 'center', 
            render: (row) => (
                <div className="flex flex-col">
                    <span className={`font-bold ${row.advances.remaining > 0 ? 'text-orange-600' : 'text-slate-400'}`}>{fmt(row.advances.remaining)}</span>
                    <span className="text-[9px] text-slate-400 font-bold uppercase tracking-widest">الإجمالي: {fmt(row.advances.total)}</span>
                </div>
            )
        },
        { 
            key: 'custodies', 
            label: 'رصيد العهد', 
            align: 'center', 
            render: (row) => (
                <div className="flex flex-col">
                    <span className={`font-bold ${row.custodies.remaining > 0 ? 'text-emerald-600' : 'text-slate-400'}`}>{fmt(row.custodies.remaining)}</span>
                    <span className="text-[9px] text-slate-400 font-bold uppercase tracking-widest">الإجمالي: {fmt(row.custodies.total)}</span>
                </div>
            )
        },
        { key: 'bonuses', label: 'إجمالي المكافآت', align: 'center', render: (row) => <span className="font-bold text-purple-600">{fmt(row.bonuses.total)}</span> },
        { key: 'total_payroll_period', label: 'صافي المدفوع', align: 'center', tdClassName: 'bg-slate-50/50 font-black text-slate-900', render: (row) => fmt(row.total_payroll_period) },
    ];

    const detailColumns = [
        { key: 'date', label: 'التاريخ', className: 'w-24', tdClassName: 'font-mono font-bold text-slate-400' },
        { key: 'employee_name', label: 'الموظف', className: 'font-bold text-slate-800' },
        { 
            key: 'type', 
            label: 'النوع', 
            align: 'center', 
            render: (row) => (
                <span className={`px-2 py-1 rounded text-[9px] font-black uppercase tracking-widest ${
                    row.type === 'advance' ? 'bg-orange-50 text-orange-600' : 
                    row.type === 'custody' ? 'bg-emerald-50 text-emerald-600' : 'bg-purple-50 text-purple-600'
                }`}>{typeLabels[row.type]}</span>
            )
        },
        { 
            key: 'purpose', 
            label: 'البيان / الغرض', 
            render: (row) => <div className="text-[11px] font-medium text-slate-600 whitespace-pre-wrap">{row.purpose || '—'}</div> 
        },
        { key: 'amount', label: 'المبلغ', align: 'center', className: 'w-24', tdClassName: 'font-bold text-slate-900', render: (row) => fmt(row.amount) },
        { key: 'remaining', label: 'المتبقي', align: 'center', className: 'w-24', tdClassName: 'font-medium text-slate-400', render: (row) => fmt(row.remaining) },
        { 
            key: 'status', 
            label: 'الحالة', 
            align: 'center', 
            render: (row) => (
                <span className={`inline-flex items-center gap-1.5 px-2 py-1 rounded-md font-black text-[9px] uppercase tracking-widest ${
                    row.status === 'settled' ? 'bg-emerald-50 text-emerald-600' : 'bg-orange-50 text-orange-600'
                }`}>
                    <span className={`w-1 h-1 rounded-full ${row.status === 'settled' ? 'bg-emerald-500' : 'bg-orange-500'}`}></span>
                    {statusLabels[row.status]}
                </span>
            )
        },
    ];

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-bold text-xl text-gray-800">التقرير المالي للموارد البشرية</h2>}>
            <Head title="التقرير المالي للموارد البشرية" />

            <div className="min-h-screen bg-slate-50 py-6" dir="rtl">
                <div className="mx-auto max-w-[1400px] px-4">
                    
                    <PageHeader 
                        title="التقرير المالي للموارد البشرية" 
                        subtitle={`تحليل السنة المالية 2025 • ${detailedTransactions.length} عملية`}
                        backRoute={route('reports.index')}
                        stats={[
                            { label: 'الرواتب', value: fmt(totals.salaries), prefix: 'SAR' },
                            { label: 'السلف', value: fmt(totals.advances_remaining), prefix: 'SAR', color: 'orange' },
                            { label: 'العهد', value: fmt(totals.custodies_remaining), prefix: 'SAR', color: 'emerald' },
                            { label: 'المكافآت', value: fmt(totals.bonuses), prefix: 'SAR' },
                            { label: 'المدفوعات', value: fmt(totals.payroll_period), prefix: 'SAR', color: 'blue' }
                        ]}
                        actions={[
                            <button onClick={exportExcel} className="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-lg border border-emerald-100 hover:bg-emerald-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ملف Excel
                            </button>,
                            <button onClick={exportPdf} className="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 text-sm font-bold rounded-lg border border-red-100 hover:bg-red-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ملف PDF
                            </button>
                        ]}
                    />

                    {/* Compact Filter */}
                    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-6 hide-print">
                        <form onSubmit={handleFilter} className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                            <div className="space-y-1.5">
                                <label className="text-[11px] font-bold text-slate-400 uppercase mr-1">الموظف</label>
                                <select value={data.employee_id} onChange={e => setData({...data, employee_id: e.target.value})} className="w-full bg-slate-50 border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-200 font-bold text-slate-700 transition-all">
                                    <option value="">جميع الموظفين</option>
                                    {employees.map(emp => <option key={emp.id} value={emp.id}>{emp.name}</option>)}
                                </select>
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-[11px] font-bold text-slate-400 uppercase mr-1">نوع الحركة</label>
                                <select value={data.type} onChange={e => setData({...data, type: e.target.value})} className="w-full bg-slate-50 border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-200 font-bold text-slate-700">
                                    <option value="">الكل</option>
                                    <option value="advance">سلف</option>
                                    <option value="custody">عهد</option>
                                    <option value="bonus">مكافآت</option>
                                </select>
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-[11px] font-bold text-slate-400 uppercase mr-1">الحالة</label>
                                <select value={data.status} onChange={e => setData({...data, status: e.target.value})} className="w-full bg-slate-50 border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-200 font-bold text-slate-700">
                                    <option value="">الكل</option>
                                    <option value="open">مفتوحة</option>
                                    <option value="settled">تمت التسوية</option>
                                </select>
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-[11px] font-bold text-slate-400 uppercase mr-1">من</label>
                                <input type="date" value={data.start_date} onChange={e => setData({...data, start_date: e.target.value})} className="w-full bg-slate-50 border-slate-200 rounded-lg px-3 py-2 text-sm font-bold text-slate-700" />
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-[11px] font-bold text-slate-400 uppercase mr-1">إلى</label>
                                <input type="date" value={data.end_date} onChange={e => setData({...data, end_date: e.target.value})} className="w-full bg-slate-50 border-slate-200 rounded-lg px-3 py-2 text-sm font-bold text-slate-700" />
                            </div>
                            <div className="flex items-end">
                                <button type="submit" className="w-full bg-slate-900 text-white font-bold py-2 px-4 rounded-lg hover:bg-slate-800 transition-all text-sm">تطبيق</button>
                            </div>
                        </form>
                    </div>

                    <div className="space-y-8">
                        <div className="space-y-3">
                            <h2 className="text-sm font-black text-slate-400 uppercase tracking-widest flex items-center gap-2 mr-2">
                                <div className="w-1.5 h-1.5 bg-slate-400 rounded-full"></div>
                                ملخص الأرصدة حسب الموظف
                            </h2>
                            <DataTable columns={summaryColumns} data={reportData} />
                        </div>

                        <div className="space-y-3">
                            <h2 className="text-sm font-black text-slate-400 uppercase tracking-widest flex items-center gap-2 mr-2">
                                <div className="w-1.5 h-1.5 bg-slate-400 rounded-full"></div>
                                سجل الحركات التفصيلي
                            </h2>
                            <DataTable columns={detailColumns} data={detailedTransactions} emptyMessage="لا توجد سجلات مطابقة للفلاتر المختارة" />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
