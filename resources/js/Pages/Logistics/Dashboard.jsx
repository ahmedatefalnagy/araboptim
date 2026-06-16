import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function Dashboard({ auth, stats, vehicles }) {
    const [search, setSearch] = useState('');

    const fmt = (num) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'SAR' }).format(num || 0);

    const getStatusInfo = (status, tripStatus) => {
        if (status === 'available') return { label: 'جاهزة', color: 'text-emerald-600 bg-emerald-50 border-emerald-100', icon: '✅' };
        if (status === 'maintenance' || status === 'breakdown') return { label: 'في الورشة', color: 'text-rose-600 bg-rose-50 border-rose-100', icon: '🛠️' };
        
        if (tripStatus === 'loading') return { label: 'جاري التحميل', color: 'text-orange-600 bg-orange-50 border-orange-100', icon: '🏗️' };
        if (tripStatus === 'transit') return { label: 'في الطريق', color: 'text-indigo-600 bg-indigo-50 border-indigo-100', icon: '🛣️' };
        if (tripStatus === 'at_destination') return { label: 'وصل الموقع', color: 'text-cyan-600 bg-cyan-50 border-cyan-100', icon: '📍' };
        
        return { label: 'غير محدد', color: 'text-slate-600 bg-slate-50 border-slate-100', icon: '⚪' };
    };

    const filteredVehicles = vehicles?.filter(v => 
        v.plate_no.toLowerCase().includes(search.toLowerCase()) || 
        v.driver_name.toLowerCase().includes(search.toLowerCase())
    ) || [];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="إدارة الأسطول الذكية - Odoo 19" />

            <div className="min-h-screen bg-[#f8fafc] pb-12" dir="rtl">
                {/* Odoo Style Top Bar */}
                <div className="bg-white border-b border-slate-200 sticky top-0 z-30 px-8 py-4 flex items-center justify-between shadow-sm">
                    <div className="flex items-center gap-6">
                        <h1 className="text-xl font-bold text-slate-800 flex items-center gap-2">
                            <span className="text-2xl">🚛</span> لوحة الأسطول والرحلات
                        </h1>
                        <div className="flex items-center gap-2 bg-slate-100 rounded-lg p-1">
                            <button className="px-4 py-1.5 bg-white shadow-sm rounded-md text-sm font-bold text-indigo-600">نظرة عامة</button>
                            <Link href={route('logistics.trips.index')} className="px-4 py-1.5 hover:bg-white/50 rounded-md text-sm font-medium text-slate-600 transition-all italic">سجل الرحلات</Link>
                            <Link href={route('logistics.routes.index')} className="px-4 py-1.5 hover:bg-white/50 rounded-md text-sm font-medium text-slate-600 transition-all italic">إدارة المسارات</Link>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        <Link href={route('logistics.trips.create')} className="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-md transition-all">
                            + فتح رحلة جديدة
                        </Link>
                    </div>
                </div>

                <div className="max-w-[1600px] mx-auto px-8 pt-8">
                    {/* Top Stats */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                        <OdooStat label="أرباح التشغيل" value={fmt(stats.monthly_revenue - stats.monthly_costs)} trend="+12%" icon="💰" color="text-emerald-600" />
                        <OdooStat label="شاحنات في الطريق" value={stats.on_road} sub={`${stats.loading} قيد التحميل`} icon="🛣️" color="text-indigo-600" />
                        <OdooStat label="تنبيهات AI" value={stats.oil_alerts + stats.tire_alerts} sub="تحتاج صيانة" icon="🤖" color="text-rose-600" />
                        <OdooStat label="الشاحنات المتوقفة" value={stats.stopped} sub="أثناء الرحلة" icon="🛑" color="text-orange-600" />
                        <OdooStat label="اكتمال الرحلات" value={stats.monthly_completed} sub="هذا الشهر" icon="🏆" color="text-fuchsia-600" />
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        {/* LEFT: Live Trip Lifecycle Feed (NEW REQUEST) */}
                        <div className="lg:col-span-4 space-y-6">
                            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                                <h3 className="text-lg font-black text-slate-800 mb-6 flex items-center gap-3 border-b pb-4">
                                    <span className="text-2xl">⏳</span> متابعة دورة حياة الرحلات
                                </h3>
                                <div className="space-y-10 pr-6 border-r-2 border-slate-100">
                                    {vehicles.filter(v => v.trip_id).map(v => (
                                        <div key={v.id} className="relative">
                                            {/* Status Dot on Line */}
                                            <div className={`absolute -right-[2.1rem] top-0 w-6 h-6 rounded-full border-4 border-white shadow-md flex items-center justify-center text-[10px] ${
                                                v.trip_status === 'loading' ? 'bg-orange-500' :
                                                v.trip_status === 'transit' ? 'bg-indigo-600 animate-pulse' :
                                                v.trip_status === 'at_destination' ? 'bg-cyan-500' : 'bg-emerald-500'
                                            }`}>
                                                {v.trip_status === 'loading' ? '🏗️' : v.trip_status === 'transit' ? '🚚' : '📍'}
                                            </div>
                                            
                                            <div className="flex flex-col">
                                                <div className="flex justify-between items-start mb-1">
                                                    <h4 className="text-sm font-black text-slate-900">{v.plate_no}</h4>
                                                    <Link href={route('logistics.trips.show', v.trip_id)} className="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">متابعة ←</Link>
                                                </div>
                                                <div className="text-xs font-bold text-slate-500 mb-2">{v.origin} ⟷ {v.destination}</div>
                                                
                                                {/* Visual Lifecycle Steps */}
                                                <div className="flex items-center gap-1">
                                                    <LifecycleStep label="تحميل" active={v.trip_status === 'loading'} completed={['transit', 'at_destination', 'completed'].includes(v.trip_status)} />
                                                    <LifecycleArrow />
                                                    <LifecycleStep label="تحرك" active={v.trip_status === 'transit'} completed={['at_destination', 'completed'].includes(v.trip_status)} />
                                                    <LifecycleArrow />
                                                    <LifecycleStep label="توقفات" active={v.is_stopped} completed={false} warning={v.is_stopped} />
                                                    <LifecycleArrow />
                                                    <LifecycleStep label="تفريغ" active={v.trip_status === 'at_destination'} completed={v.trip_status === 'completed'} />
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                    {vehicles.filter(v => v.trip_id).length === 0 && (
                                        <div className="text-center py-10 text-slate-400 italic text-sm">لا توجد رحلات نشطة حالياً</div>
                                    )}
                                </div>
                            </div>

                            {/* Maintenance Alerts */}
                            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                                <h3 className="text-sm font-bold text-slate-800 mb-6">تنبيهات الصيانة المبكرة</h3>
                                <div className="space-y-4">
                                    {vehicles.filter(v => v.ai_prediction?.status !== 'healthy').map(v => (
                                        <div key={v.id} className={`p-4 rounded-xl border flex items-center gap-4 ${v.ai_prediction?.status === 'critical' ? 'bg-rose-50 border-rose-100' : 'bg-orange-50 border-orange-100'}`}>
                                            <span className="text-2xl">🤖</span>
                                            <div>
                                                <p className="text-xs font-black text-slate-800">{v.plate_no}</p>
                                                <p className="text-[10px] font-bold text-slate-600">توقع الصيانة: خلال {v.ai_prediction?.days_left} أيام</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        {/* RIGHT: Detailed Fleet Overview */}
                        <div className="lg:col-span-8 space-y-6">
                            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                                <div className="p-6 border-b border-slate-100 flex items-center justify-between">
                                    <h2 className="text-lg font-bold text-slate-800 italic">مراقبة الأسطول والقطع الأكثر استهلاكاً</h2>
                                    <div className="flex items-center gap-4">
                                        <div className="relative">
                                            <input 
                                                type="text" value={search} onChange={e => setSearch(e.target.value)}
                                                placeholder="بحث بـ (اللوحة، السائق)..."
                                                className="bg-slate-50 border-slate-200 rounded-xl px-4 py-2 text-sm w-64 focus:ring-0 focus:border-indigo-400 transition-all pr-10"
                                            />
                                            <span className="absolute right-3 top-1/2 -translate-y-1/2 opacity-30">🔍</span>
                                        </div>
                                    </div>
                                </div>

                                <div className="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-6">
                                    {filteredVehicles.map(vehicle => {
                                        const status = getStatusInfo(vehicle.status, vehicle.trip_status);
                                        return (
                                            <div key={vehicle.id} className="bg-white border border-slate-100 rounded-2xl p-6 hover:shadow-xl transition-all group">
                                                <div className="flex justify-between items-start mb-4">
                                                    <div className="flex items-center gap-4">
                                                        <div className="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center text-2xl group-hover:bg-indigo-900 group-hover:text-white transition-all">
                                                            🚛
                                                        </div>
                                                        <div>
                                                            <h4 className="text-xl font-bold text-slate-900 leading-none mb-1">{vehicle.plate_no}</h4>
                                                            <p className="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{vehicle.driver_name}</p>
                                                        </div>
                                                    </div>
                                                    <span className={`px-3 py-1 rounded-full text-[10px] font-bold border ${status.color}`}>
                                                        {status.icon} {status.label}
                                                    </span>
                                                </div>

                                                <div className="grid grid-cols-3 gap-4 border-t border-slate-50 pt-4 mt-4">
                                                    <div>
                                                        <p className="text-[9px] font-black text-slate-400 uppercase">ODO</p>
                                                        <p className="text-xs font-bold text-slate-700">{vehicle.odometer?.toLocaleString()} KM</p>
                                                    </div>
                                                    <div>
                                                        <p className="text-[9px] font-black text-slate-400 uppercase">Oil Life</p>
                                                        <p className={`text-xs font-bold ${vehicle.needs_oil ? 'text-rose-600' : 'text-emerald-600'}`}>{vehicle.needs_oil ? 'تغيير!' : 'جيد'}</p>
                                                    </div>
                                                    <div>
                                                        <p className="text-[9px] font-black text-slate-400 uppercase">Efficiency</p>
                                                        <p className="text-xs font-bold text-indigo-600">{vehicle.ai_prediction?.avg_daily_km} km/d</p>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Most Used Parts Row */}
                            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
                                <h3 className="text-sm font-black text-slate-800 mb-6 uppercase tracking-widest">تحليل استهلاك قطع الغيار (المخزن ⟷ الورشة)</h3>
                                <div className="grid grid-cols-2 md:grid-cols-5 gap-6">
                                    {stats.top_parts?.map((part, i) => (
                                        <div key={i} className="text-center p-4 bg-slate-50 rounded-2xl border border-slate-100 hover:border-indigo-200 transition-all">
                                            <p className="text-xs font-bold text-slate-700 mb-1 truncate">{part.name}</p>
                                            <p className="text-lg font-black text-indigo-600 leading-none">{Math.round(part.total_qty)}</p>
                                            <p className="text-[9px] text-slate-400 font-bold mt-1 uppercase">وحدة</p>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function LifecycleStep({ label, active, completed, warning }) {
    return (
        <div className={`px-2 py-0.5 rounded text-[8px] font-black transition-all ${
            active ? 'bg-indigo-600 text-white shadow-sm ring-2 ring-indigo-100' :
            completed ? 'bg-emerald-500 text-white' : 
            warning ? 'bg-rose-500 text-white animate-bounce' : 'bg-slate-100 text-slate-400'
        }`}>
            {label}
        </div>
    );
}

function LifecycleArrow() { return <span className="text-[8px] text-slate-200">❯</span>; }

function OdooStat({ label, value, trend, sub, icon, color }) {
    return (
        <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all group overflow-hidden">
            <div className="flex items-center justify-between mb-4">
                <span className="text-2xl p-2 bg-slate-50 rounded-xl group-hover:bg-indigo-50 transition-colors">{icon}</span>
                {trend && <span className="text-[10px] font-bold text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-full">{trend}</span>}
            </div>
            <div className="space-y-1">
                <p className="text-[11px] font-bold text-slate-400 uppercase tracking-widest">{label}</p>
                <p className={`text-2xl font-black ${color} tracking-tighter`}>{value}</p>
                {sub && <p className="text-[10px] text-slate-400 italic leading-none">{sub}</p>}
            </div>
        </div>
    );
}
