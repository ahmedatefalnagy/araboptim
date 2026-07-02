import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth }) {
    const reportGroups = [
        {
            name: 'القوائم الختامية',
            items: [
                { name: 'ميزان المراجعة', route: 'reports.trialBalance', icon: '⚖️', color: 'bg-blue-600', desc: 'Trial Balance' },
                { name: 'قائمة الدخل', route: 'reports.incomeStatement', icon: '📈', color: 'bg-emerald-600', desc: 'P & L' },
                { name: 'المركز المالي', route: 'reports.balanceSheet', icon: '🏦', color: 'bg-indigo-600', desc: 'Balance Sheet' },
            ]
        },
        {
            name: 'التقارير التحليلية',
            items: [
                { name: 'دفتر الأستاذ', route: 'reports.ledger', icon: '📘', color: 'bg-slate-800', desc: 'General Ledger' },
                { name: 'المصروفات', route: 'reports.expenses', icon: '💸', color: 'bg-rose-600', desc: 'Expenses' },
                { name: 'الأصول الثابتة', route: 'reports.fixedAssets', icon: '🏢', color: 'bg-amber-600', desc: 'Fixed Assets' },
                { name: 'حركة مركز التكلفة', route: 'reports.costCenter', icon: '🎯', color: 'bg-teal-600', desc: 'Cost Center Ledger' },
                { name: 'حركة نقدية مراكز التكلفة', route: 'reports.costCenterCashflow', icon: '💵', color: 'bg-cyan-600', desc: 'Cost Center Cashflow' },
            ]
        },
        {
            name: 'الموارد والضرائب',
            items: [
                { name: 'الموارد البشرية', route: 'reports.hr', icon: '👨‍💼', color: 'bg-purple-600', desc: 'HR Financials' },
                { name: 'التقرير الضريبي', route: 'reports.tax', icon: '🇸🇦', color: 'bg-green-700', desc: 'VAT Report' },
            ]
        }
    ];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="مركز التقارير" />

            <div className="h-[calc(100vh-4rem)] bg-[#f1f4f9] py-4 px-6 md:px-12 overflow-hidden flex flex-col" dir="rtl">
                
                {/* Odoo Style Ultra-Compact Ribbon */}
                <div className="max-w-7xl mx-auto w-full mb-6">
                    <div className="bg-white rounded-2xl shadow-sm border border-slate-200 p-2.5 flex flex-wrap items-center justify-between gap-4">
                        <div className="flex items-center gap-4 px-3">
                            <div className="w-10 h-10 bg-slate-950 rounded-xl flex items-center justify-center text-white shadow-lg">
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </div>
                            <div className="hidden sm:block">
                                <h1 className="text-sm font-black text-slate-950">مركز التقارير الذكي</h1>
                                <p className="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Arab Optem BI</p>
                            </div>
                        </div>

                        <div className="flex items-center gap-6 text-[11px] font-black uppercase tracking-widest border-r border-slate-100 pr-6 mr-auto">
                            <div className="flex items-center gap-2">
                                <span className="text-slate-400">السنة:</span>
                                <span className="text-slate-950">2025</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <span className="text-slate-400">الحالة:</span>
                                <span className="text-emerald-600 flex items-center gap-1.5">
                                    <span className="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                    نشط
                                </span>
                            </div>
                        </div>

                        <Link href={route('ai.chat')} className="bg-slate-950 text-white text-[11px] font-black px-5 py-2.5 rounded-xl hover:bg-slate-800 transition-all flex items-center gap-2 shadow-lg shadow-slate-200 active:scale-95">
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            المساعد الذكي
                        </Link>
                    </div>
                </div>

                {/* Main Content Area - Optimized to fit in view */}
                <div className="max-w-7xl mx-auto w-full flex-1 overflow-y-auto custom-scrollbar pb-8">
                    <div className="space-y-8">
                        {reportGroups.map((group, gIdx) => (
                            <div key={gIdx} className="space-y-4">
                                <div className="flex items-center gap-3 mr-2">
                                    <div className="h-4 w-1 bg-slate-400 rounded-full"></div>
                                    <h2 className="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">{group.name}</h2>
                                </div>
                                
                                <div className="grid grid-cols-2 sm:grid-cols-4 gap-6">
                                    {group.items.map((report) => (
                                        <Link 
                                            key={report.route} 
                                            href={route(report.route)}
                                            className="group flex items-center gap-5 p-4 bg-white rounded-[1.5rem] border border-transparent hover:border-slate-300 hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-300"
                                        >
                                            <div className={`w-16 h-16 shrink-0 ${report.color} rounded-2xl shadow-lg shadow-slate-200 flex items-center justify-center text-3xl group-hover:scale-105 transition-transform duration-300 relative overflow-hidden`}>
                                                <div className="absolute top-0 left-0 w-full h-1/2 bg-white/20 -skew-y-12 origin-top-left"></div>
                                                <span className="relative z-10">{report.icon}</span>
                                            </div>
                                            <div className="overflow-hidden">
                                                <h3 className="text-sm font-black text-slate-950 group-hover:text-blue-700 transition-colors truncate">
                                                    {report.name}
                                                </h3>
                                                <p className="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">
                                                    {report.desc}
                                                </p>
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Footer Decor - Minimalist */}
                <div className="mt-auto py-2 text-center opacity-20 pointer-events-none border-t border-slate-200">
                    <p className="text-[9px] font-black text-slate-800 uppercase tracking-[0.4em]">Arab Optem System v19.0</p>
                </div>

            </div>
            
            <style dangerouslySetInnerHTML={{__html: `
                .custom-scrollbar::-webkit-scrollbar { width: 4px; }
                .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
                .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
            `}} />
        </AuthenticatedLayout>
    );
}
