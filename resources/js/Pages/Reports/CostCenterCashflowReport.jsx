import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState, useRef, useEffect, useMemo } from 'react';
import PageHeader from '@/Components/PageHeader';
import DataTable from '@/Components/DataTable';

export default function CostCenterCashflowReport({ auth, costCenters = [], filters, lines = [], openingBalance = 0, selectedCostCenter }) {
    const [searchTerm, setSearchTerm] = useState('');
    const [showDropdown, setShowDropdown] = useState(false);
    const dropdownRef = useRef(null);
    const [payeeFilter, setPayeeFilter] = useState('');
    const [payeeDropdownOpen, setPayeeDropdownOpen] = useState(false);
    const payeeDropdownRef = useRef(null);

    const { data, setData, get, processing } = useForm({
        cost_center_id: filters.cost_center_id || '',
        start_date: filters.start_date || '',
        end_date: filters.end_date || '',
    });

    const currentCostCenter = costCenters.find(c => c.id == data.cost_center_id);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setShowDropdown(false);
            }
            if (payeeDropdownRef.current && !payeeDropdownRef.current.contains(event.target)) {
                setPayeeDropdownOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const filteredCostCenters = costCenters.filter(cc => 
        cc.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
        cc.code.toString().toLowerCase().includes(searchTerm.toLowerCase())
    );

    const submit = (e) => {
        e.preventDefault();
        get(route('reports.costCenterCashflow'), { preserveState: true });
    };

    const fmt = (num) => {
        const val = Number(num || 0);
        return val >= 0 
            ? val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
            : `(${Math.abs(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })})`;
    };

    // Calculate executive summary categories
    const summary = useMemo(() => {
        let revenue = 0;
        let expenses = 0;
        let purchases = 0;
        let contractors = 0;
        let custodyIn = 0;
        let custodyOut = 0;

        lines.forEach(l => {
            const code = String(l.account_code || '');
            const name = String(l.account_name || '');
            const desc = String(l.description || '');
            const contact = String(l.contact_name || '');

            const isBankOrCash = code.startsWith('1101') || code.startsWith('1102') || name.includes('بنك') || name.includes('الراجحي') || name.includes('الرياض') || name.includes('صندوق') || name.includes('خزينة') || name.includes('خزنه');
            const isSafeOrCustody = code.startsWith('1106') || name.includes('عهدة') || name.includes('عهده') || name.includes('سلفة') || name.includes('سلفه');
            const isContractor = !isBankOrCash && !isSafeOrCustody && (contact.includes('مقاول') || name.includes('مقاول') || desc.includes('مقاول'));

            if ((code.startsWith('4') || code.startsWith('1103')) && !isContractor) {
                revenue += (l.credit - l.debit);
                return;
            }

            if (isBankOrCash) {
                return;
            }

            if (isSafeOrCustody) {
                custodyIn += l.debit;
                custodyOut += l.credit;
                return;
            }

            if (isContractor) {
                contractors += (l.debit - l.credit);
            } else if (code.startsWith('5')) {
                expenses += (l.debit - l.credit);
            } else if (code.startsWith('3')) {
                purchases += (l.debit - l.credit);
            } else {
                if (l.debit > 0) {
                    expenses += l.debit;
                }
                if (l.credit > 0) {
                    revenue += l.credit;
                }
            }
        });

        const actualSpent = expenses + purchases + contractors;
        const custodyRemaining = Math.max(0, custodyIn - custodyOut);
        const remaining = revenue - actualSpent;

        return { revenue, expenses, purchases, contractors, custodyRemaining, actualSpent, remaining };
    }, [lines]);

    // Cash flows detailed array mapping "الوارد والمنصرف"
    const cashFlows = useMemo(() => {
        return lines.filter(l => {
            const code = String(l.account_code || '');
            const name = String(l.account_name || '');
            const isBankOrCash = code.startsWith('1101') || code.startsWith('1102') || name.includes('بنك') || name.includes('الراجحي') || name.includes('الرياض') || name.includes('صندوق') || name.includes('خزينة') || name.includes('خزنه');
            return !isBankOrCash;
        }).map(l => {
            const code = String(l.account_code || '');
            const name = String(l.account_name || '');
            const desc = String(l.description || '');
            const contact = String(l.contact_name || '');

            let flowType = 'outgoing'; // default
            let categoryText = 'مصروف تشغيلي';
            let amount = l.debit - l.credit;

            const isContractor = contact.includes('مقاول') || name.includes('مقاول') || desc.includes('مقاول');

            if ((code.startsWith('4') || code.startsWith('1103')) && !isContractor) {
                flowType = 'incoming';
                categoryText = 'إيراد من عميل';
                amount = l.credit - l.debit;
            } else if (isContractor) {
                categoryText = 'مصروف تشغيلي (مقاولين)';
                amount = l.debit - l.credit;
            } else if (code.startsWith('1106') || name.includes('عهدة') || name.includes('عهده') || name.includes('سلفة') || name.includes('سلفه') || desc.includes('عهدة') || desc.includes('عهده')) {
                categoryText = 'عهدة موظفين / عهد';
                amount = l.debit - l.credit;
            } else if (code.startsWith('3')) {
                categoryText = 'مشتريات مباشرة';
                amount = l.debit - l.credit;
            } else if (code.startsWith('5')) {
                categoryText = 'مصروف إداري / تشغيلي';
                amount = l.debit - l.credit;
            } else {
                if (l.credit > l.debit) {
                    flowType = 'incoming';
                    categoryText = 'إيراد آخر';
                    amount = l.credit - l.debit;
                } else {
                    amount = l.debit - l.credit;
                }
            }

            return {
                id: l.id,
                date: l.date,
                entry_no: l.entry_no,
                flowType,
                categoryText,
                amount,
                account: `${code} - ${name}`,
                payee: contact || name || 'الصندوق / البنك الدولي للمشروع',
                reason: desc
            };
        });
    }, [lines]);

    const filteredCashFlows = useMemo(() => {
        return cashFlows.filter(flow => {
            if (!payeeFilter) return true;
            return String(flow.payee || '').toLowerCase().includes(payeeFilter.toLowerCase());
        });
    }, [cashFlows, payeeFilter]);

    const uniquePayees = useMemo(() => {
        const payees = new Set();
        cashFlows.forEach(flow => {
            if (flow.payee) {
                payees.add(flow.payee);
            }
        });
        return Array.from(payees).sort((a, b) => a.localeCompare(b, 'ar'));
    }, [cashFlows]);

    const filteredUniquePayees = useMemo(() => {
        const isExactMatch = uniquePayees.includes(payeeFilter);
        if (isExactMatch) {
            return uniquePayees;
        }
        return uniquePayees.filter(p => 
            p.toLowerCase().includes(payeeFilter.toLowerCase())
        );
    }, [uniquePayees, payeeFilter]);

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="تقرير حركة نقدية مركز التكلفة" />

            <div className="min-h-screen bg-slate-50 py-6" dir="rtl">
                <div className="mx-auto max-w-[1400px] px-4">
                    
                    <PageHeader 
                        title="تقرير حركة النقدية التفصيلي للمشروع (الوارد والمنصرف)" 
                        subtitle="تحليل التدفق النقدي ومقبوضات ومدفوعات المشروع بالتفصيل"
                        backRoute={route('cost-centers.index')}
                        stats={[]}
                        actions={selectedCostCenter ? [
                            <a target="_blank" href={route('reports.costCenter.excel', filters)} className="inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-lg border border-emerald-100 hover:bg-emerald-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ملف Excel
                            </a>,
                            <a target="_blank" href={route('reports.costCenter.pdf', filters)} className="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 text-sm font-bold rounded-lg border border-red-100 hover:bg-red-100 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                ملف PDF
                            </a>,
                            <button onClick={() => window.print()} className="inline-flex items-center px-4 py-2 bg-white text-slate-700 text-sm font-bold rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                                <svg className="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                طباعة
                            </button>
                        ] : []}
                    />

                    <div className="bg-white p-6 rounded-2xl shadow-sm mb-8 border border-slate-200 hide-print">
                        <form onSubmit={submit} className="space-y-4">
                            <div className="grid grid-cols-12 gap-4 items-end">
                                <div className="col-span-12 md:col-span-5 relative" ref={dropdownRef}>
                                    <label className="block text-[11px] font-black text-slate-400 uppercase mb-1.5 mr-1">مركز التكلفة</label>
                                    <div 
                                        className="w-full rounded-xl border border-slate-200 h-[46px] px-4 cursor-pointer bg-slate-50 flex justify-between items-center hover:border-slate-400 transition-all font-bold text-slate-700"
                                        onClick={() => setShowDropdown(!showDropdown)}
                                    >
                                        <span className="truncate">
                                            {currentCostCenter ? `[${currentCostCenter.code}] ${currentCostCenter.name}` : '-- اختر مركز التكلفة --'}
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
                                                    placeholder="بحث باسم المركز أو الكود..."
                                                    value={searchTerm}
                                                    onChange={(e) => setSearchTerm(e.target.value)}
                                                />
                                            </div>
                                            <div className="max-h-64 overflow-y-auto">
                                                {filteredCostCenters.length > 0 ? (
                                                    filteredCostCenters.map(cc => (
                                                        <div 
                                                            key={cc.id}
                                                            className={`p-3 text-sm cursor-pointer hover:bg-slate-50 border-b border-slate-50 flex justify-between items-center ${data.cost_center_id == cc.id ? 'bg-blue-50 border-r-4 border-blue-600' : ''}`}
                                                            onClick={() => {
                                                                setData('cost_center_id', cc.id);
                                                                setShowDropdown(false);
                                                                setSearchTerm('');
                                                            }}
                                                        >
                                                            <span className="font-bold text-slate-700">{cc.name}</span>
                                                            <span className="text-slate-400 font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded">{cc.code}</span>
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
                        </form>
                    </div>

                    {selectedCostCenter ? (
                        <div className="space-y-8">
                            
                            {/* Premium executive dashboard cards */}
                            <div className="grid grid-cols-1 md:grid-cols-5 gap-6 hide-print">
                                <div className="bg-gradient-to-br from-emerald-50 to-emerald-100/50 p-6 rounded-2xl border border-emerald-100 shadow-sm flex flex-col justify-between">
                                    <div className="text-emerald-800 font-bold text-xs uppercase tracking-wider mb-2 flex items-center justify-between">
                                        <span>إجمالي الإيرادات (الوارد)</span>
                                        <div className="w-2 h-2 rounded-full bg-emerald-500"></div>
                                    </div>
                                    <div className="text-3xl font-black text-emerald-950">{fmt(summary.revenue)}</div>
                                    <div className="text-[10px] text-emerald-600 font-semibold mt-2">عائدات ومقبوضات المشروع</div>
                                </div>

                                <div className="bg-gradient-to-br from-rose-50 to-rose-100/50 p-6 rounded-2xl border border-rose-100 shadow-sm flex flex-col justify-between">
                                    <div className="text-rose-800 font-bold text-xs uppercase tracking-wider mb-2 flex items-center justify-between">
                                        <span>المصروفات التشغيلية</span>
                                        <div className="w-2 h-2 rounded-full bg-rose-500"></div>
                                    </div>
                                    <div className="text-3xl font-black text-rose-950">{fmt(summary.expenses + summary.contractors)}</div>
                                    <div className="text-[10px] text-rose-600 font-semibold mt-2">تكاليف ونفقات تشغيلية ودفعات مقاولين</div>
                                </div>

                                <div className="bg-gradient-to-br from-amber-50 to-amber-100/50 p-6 rounded-2xl border border-amber-100 shadow-sm flex flex-col justify-between">
                                    <div className="text-amber-800 font-bold text-xs uppercase tracking-wider mb-2 flex items-center justify-between">
                                        <span>المشتريات المباشرة</span>
                                        <div className="w-2 h-2 rounded-full bg-amber-500"></div>
                                    </div>
                                    <div className="text-3xl font-black text-amber-950">{fmt(summary.purchases)}</div>
                                    <div className="text-[10px] text-amber-600 font-semibold mt-2">مواد ومشتريات خاصة بالمشروع</div>
                                </div>

                                <div className="bg-gradient-to-br from-teal-50 to-teal-100/50 p-6 rounded-2xl border border-teal-100 shadow-sm flex flex-col justify-between">
                                    <div className="text-teal-800 font-bold text-xs uppercase tracking-wider mb-2 flex items-center justify-between">
                                        <span>العهد المتبقية</span>
                                        <div className="w-2 h-2 rounded-full bg-teal-500"></div>
                                    </div>
                                    <div className="text-3xl font-black text-teal-950">{fmt(summary.custodyRemaining)}</div>
                                    <div className="text-[10px] text-teal-600 font-semibold mt-2">
                                        عهدة معلقة لدى الموظفين للمشروع
                                    </div>
                                </div>

                                <div className="bg-gradient-to-br from-indigo-900 to-slate-950 p-6 rounded-2xl text-white shadow-lg flex flex-col justify-between">
                                    <div className="text-indigo-200 font-bold text-xs uppercase tracking-wider mb-2 flex items-center justify-between">
                                        <span>متبقي الإيراد (صافي الفائض)</span>
                                        <div className="w-2 h-2 rounded-full bg-indigo-400"></div>
                                    </div>
                                    <div className="text-3xl font-black text-indigo-100">{fmt(summary.remaining)}</div>
                                    <div className="text-[10px] text-indigo-300 font-semibold mt-2">فائض السيولة المتبقي للمشروع</div>
                                </div>
                            </div>

                            {/* Detailed cash flow list */}
                            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                                <div className="border-b border-slate-100 pb-4 mb-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                    <div>
                                        <h3 className="text-base font-black text-slate-800">حركة الوارد والمنصرف التفصيلية للمشروع</h3>
                                        <p className="text-xs text-slate-400 font-bold mt-1">تفصيل كامل لمصادر الأموال ومصارفها بالأسماء والسبب</p>
                                    </div>
                                    <div className="w-full md:w-80 flex items-center gap-3 hide-print" ref={payeeDropdownRef}>
                                        <div className="relative w-full">
                                            <div className="relative">
                                                <input 
                                                    type="text"
                                                    placeholder="اختر أو اكتب اسم الجهة للفلترة..." 
                                                    className="w-full rounded-xl border-slate-200 bg-slate-50 h-[46px] px-4 pl-10 font-bold text-slate-700 focus:ring-slate-900 focus:border-slate-900 text-xs cursor-pointer"
                                                    value={payeeFilter}
                                                    onChange={e => {
                                                        setPayeeFilter(e.target.value);
                                                        setPayeeDropdownOpen(true);
                                                    }}
                                                    onFocus={() => setPayeeDropdownOpen(true)}
                                                />
                                                <div 
                                                    className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 cursor-pointer"
                                                    onClick={() => setPayeeDropdownOpen(!payeeDropdownOpen)}
                                                >
                                                    <svg className={`w-4 h-4 transition-transform ${payeeDropdownOpen ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </div>
                                            </div>
                                            
                                            {payeeDropdownOpen && (
                                                <div className="absolute z-50 mt-2 w-full bg-white border border-slate-200 rounded-xl shadow-2xl overflow-hidden max-h-64 overflow-y-auto animate-in fade-in slide-in-from-top-2 duration-200">
                                                    {filteredUniquePayees.length > 0 ? (
                                                        filteredUniquePayees.map(p => (
                                                            <div 
                                                                key={p}
                                                                className={`p-3 text-xs cursor-pointer hover:bg-slate-50 border-b border-slate-50 font-bold text-slate-700 ${payeeFilter === p ? 'bg-blue-50 border-r-4 border-blue-600' : ''}`}
                                                                onClick={() => {
                                                                    setPayeeFilter(p);
                                                                    setPayeeDropdownOpen(false);
                                                                }}
                                                            >
                                                                {p}
                                                            </div>
                                                        ))
                                                    ) : (
                                                        <div className="p-4 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">لا توجد جهات مطابقة</div>
                                                    )}
                                                </div>
                                            )}
                                        </div>
                                        {payeeFilter && (
                                            <button 
                                                type="button"
                                                onClick={() => {
                                                    setPayeeFilter('');
                                                    setPayeeDropdownOpen(false);
                                                }}
                                                className="text-xs font-bold text-rose-600 hover:text-rose-800 transition-colors shrink-0"
                                            >
                                                إعادة تعيين
                                            </button>
                                        )}
                                    </div>
                                </div>

                                <div className="overflow-x-auto">
                                    <table className="w-full text-right text-xs">
                                        <thead>
                                            <tr className="bg-slate-50 text-slate-500 font-bold border-b border-slate-100">
                                                <th className="p-3">التاريخ والبيان</th>
                                                <th className="p-3">نوع الحركة</th>
                                                <th className="p-3">الحساب المالي (منين)</th>
                                                <th className="p-3">الجهة المستلمة (لمين)</th>
                                                <th className="p-3">السبب والبيان (ليه)</th>
                                                <th className="p-3 text-left">المبلغ</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-50">
                                            {filteredCashFlows.map(flow => (
                                                <tr key={flow.id} className="hover:bg-slate-50/50 transition-colors">
                                                    <td className="p-3">
                                                        <div className="font-bold text-slate-700">{flow.date}</div>
                                                        <div className="text-[10px] text-slate-400 font-mono mt-0.5">#{flow.entry_no}</div>
                                                    </td>
                                                    <td className="p-3">
                                                        <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black ${
                                                            flow.flowType === 'incoming' 
                                                                ? 'bg-emerald-50 text-emerald-700' 
                                                                : 'bg-rose-50 text-rose-700'
                                                        }`}>
                                                            {flow.flowType === 'incoming' ? 'وارد (+)' : 'منصرف (-)'} - {flow.categoryText}
                                                        </span>
                                                    </td>
                                                    <td className="p-3 font-semibold text-slate-600">{flow.account}</td>
                                                    <td className="p-3 font-bold text-slate-700">{flow.payee}</td>
                                                    <td className="p-3 text-slate-500 max-w-xs truncate" title={flow.reason}>{flow.reason}</td>
                                                    <td className="p-3 font-black text-left text-sm text-slate-800">{fmt(flow.amount)}</td>
                                                </tr>
                                            ))}
                                            {filteredCashFlows.length === 0 && (
                                                <tr>
                                                    <td colSpan="6" className="p-8 text-center text-slate-400 font-semibold">لا توجد عمليات حركة نقدية للفترة أو الجهة المحددة</td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div className="py-24 text-center bg-white rounded-2xl border border-slate-200 border-dashed">
                             <div className="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg className="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                             </div>
                             <h3 className="text-lg font-black text-slate-800">يرجى اختيار مشروع لعرض حركة النقدية</h3>
                             <p className="text-slate-400 text-sm mt-1 font-bold">حدد مركز التكلفة والفترة الزمنية من الأعلى للبدء</p>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
