import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Dashboard({ auth, stats, recentActivities: backendActivities }) {
    
    // Data from Backend
    const kpis = [
        { title: 'إجمالي المبيعات', value: `ر.س ${stats.sales}`, trend: '+12%', isPositive: true, icon: '📈', color: 'blue' },
        { title: 'المستحقات المعلقة', value: `ر.س ${stats.purchases}`, trend: '-5%', isPositive: false, icon: '🛒', color: 'rose' },
        { title: 'قيمة المخزون', value: `ر.س ${stats.inventory}`, trend: '+2%', isPositive: true, icon: '📦', color: 'emerald' },
        { title: 'الرصيد النقدي', value: `ر.س ${stats.cash}`, trend: '+8%', isPositive: true, icon: '💰', color: 'amber' },
    ];

    const quickReports = [
        { name: 'القوائم المالية', desc: 'الميزانية، الأرباح والخسائر، التدفقات', route: 'reports.index', icon: '🏦' },
        { name: 'تحليل المبيعات', desc: 'أداء المنتجات، ضريبة القيمة المضافة', route: 'reports.index', icon: '📊' },
        { name: 'إدارة المخزون', desc: 'جرد المستودعات، النواقص، التكاليف', route: 'items.index', icon: '🏭' },
        { name: 'الأستاذ العام', desc: 'كشوف الحسابات، ميزان المراجعة', route: 'reports.index', icon: '📖' },
    ];

    const recentActivities = backendActivities.length > 0 ? backendActivities : [
        { time: 'الآن', desc: 'لا توجد نشاطات حديثة مسجلة في النظام.', user: 'النظام', type: 'system' },
    ];

    const getActivityIcon = (type) => {
        switch(type) {
            case 'sale': return '💰';
            case 'purchase': return '🛒';
            case 'inventory': return '📦';
            default: return '⚙️';
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header="المركز الرئيسي للعمليات"
        >
            <Head title="لوحة التحكم" />

            <div className="py-6 bg-[#f8fafc] min-h-screen" dir="rtl">
                <div className="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                    
                    {/* Compact Modern Welcome */}
                    <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                        <div className="space-y-1">
                            <h2 className="text-2xl font-black text-slate-900">
                                طاب يومك، {auth.user?.name} ✨
                            </h2>
                            <p className="text-slate-500 text-sm font-medium">
                                إليك ملخص سريع لأداء مؤسستك اليوم، {new Date().toLocaleDateString('ar-SA', { weekday: 'long', day: 'numeric', month: 'long' })}
                            </p>
                        </div>
                        <div className="flex items-center gap-3">
                            <Link href={route('vouchers.create', {type: 'receipt'})} className="btn-primary py-2.5 px-5 text-sm shadow-lg shadow-blue-900/10">
                                + قيد جديد
                            </Link>
                            <Link href={route('invoices.index', {type: 'sale'})} className="btn-secondary py-2.5 px-5 text-sm">
                                المبيعات
                            </Link>
                        </div>
                    </div>

                    {/* KPI Pulse Grid */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        {kpis.map((kpi, index) => (
                            <div key={index} className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
                                <div className="flex items-center justify-between mb-4">
                                    <div className={`w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-xl group-hover:scale-110 transition-transform`}>
                                        {kpi.icon}
                                    </div>
                                    <div className={`flex items-center gap-1 text-xs font-black ${kpi.isPositive ? 'text-emerald-600' : 'text-rose-600'}`}>
                                        {kpi.isPositive ? '↑' : '↓'} {kpi.trend}
                                    </div>
                                </div>
                                <div className="space-y-1">
                                    <p className="text-slate-400 text-xs font-bold uppercase tracking-wider">{kpi.title}</p>
                                    <h4 className="text-2xl font-black text-slate-900 font-mono tracking-tighter">
                                        {kpi.value}
                                    </h4>
                                </div>
                                <div className={`absolute bottom-0 right-0 w-1 h-12 bg-blue-500/10 rounded-tl-full`}></div>
                            </div>
                        ))}
                    </div>

                    {/* Functional Analytics Hub */}
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        {/* Action Center & Reports (2/3) */}
                        <div className="lg:col-span-2 space-y-6">
                            <div className="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm">
                                <div className="flex items-center justify-between mb-8">
                                    <h3 className="text-lg font-black text-slate-900 flex items-center gap-2">
                                        <span className="w-1.5 h-6 bg-blue-600 rounded-full"></span>
                                        الوصول السريع والتقارير
                                    </h3>
                                </div>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {quickReports.map((report, idx) => (
                                        <Link 
                                            key={idx}
                                            href={route(report.route)} 
                                            className="flex items-center gap-4 p-5 rounded-2xl border border-slate-50 bg-slate-50/30 hover:bg-white hover:border-blue-100 hover:shadow-lg hover:shadow-blue-900/5 transition-all group"
                                        >
                                            <div className="w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center text-2xl group-hover:rotate-6 transition-transform">
                                                {report.icon}
                                            </div>
                                            <div className="flex-1">
                                                <h4 className="font-black text-slate-800 text-sm mb-0.5">{report.name}</h4>
                                                <p className="text-[11px] text-slate-400 font-bold">{report.desc}</p>
                                            </div>
                                            <svg className="w-5 h-5 text-slate-300 opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M15 19l-7-7 7-7" /></svg>
                                        </Link>
                                    ))}
                                </div>
                            </div>

                            {/* Operational Summary */}
                            <div className="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm">
                                <div className="flex items-center justify-between mb-6">
                                    <h3 className="text-lg font-black text-slate-900">نظرة تشغيلية</h3>
                                    <span className="text-[10px] font-black bg-blue-50 text-blue-600 px-3 py-1 rounded-full uppercase">Real-time</span>
                                </div>
                                <div className="h-48 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200 flex flex-col items-center justify-center space-y-3 group cursor-pointer hover:bg-white hover:border-blue-300 transition-all">
                                    <div className="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <svg className="w-5 h-5 text-blue-500 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
                                    </div>
                                    <p className="text-slate-400 text-xs font-bold">المحرك البياني قيد التحديث لفلترة البيانات الضخمة</p>
                                </div>
                            </div>
                        </div>

                        {/* Recent Activity Timeline (1/3) */}
                        <div className="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm flex flex-col">
                            <h3 className="text-lg font-black text-slate-900 mb-8">آخر التحركات</h3>
                            <div className="flex-1 space-y-6 relative">
                                <div className="absolute top-0 bottom-0 right-[1.1rem] w-[1px] bg-slate-100"></div>
                                {recentActivities.slice(0, 6).map((activity, idx) => (
                                    <div key={idx} className="relative flex gap-4 pr-10">
                                        <div className="absolute right-0 w-9 h-9 rounded-xl bg-white border border-slate-100 shadow-sm flex items-center justify-center text-sm z-10 group-hover:scale-110 transition-transform">
                                            {getActivityIcon(activity.type)}
                                        </div>
                                        <div className="space-y-1 py-1">
                                            <div className="flex items-center gap-2">
                                                <span className="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">{activity.time}</span>
                                                <span className="text-[10px] font-bold text-slate-300">| {activity.user}</span>
                                            </div>
                                            <p className="text-xs font-bold text-slate-700 leading-relaxed line-clamp-2">
                                                {activity.desc}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            <Link href="#" className="mt-8 pt-6 border-t border-slate-50 text-center text-xs font-black text-slate-400 hover:text-blue-600 transition-colors">
                                استعراض السجل الكامل لعمليات النظام
                            </Link>
                        </div>

                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
