import BackButton from './BackButton';

export default function PageHeader({ title, subtitle, stats = [], actions = [], middle, backRoute }) {
    return (
        <div className="mb-6 flex flex-col gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm overflow-hidden relative">
            {/* Background Accent */}
            <div className="absolute top-0 left-0 w-1 bg-slate-900 h-full"></div>

            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg shadow-slate-200">
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <div>
                        <h1 className="text-xl font-black text-slate-900 tracking-tight">{title}</h1>
                        {subtitle && <p className="text-xs text-slate-500 font-bold mt-0.5 uppercase tracking-wider">{subtitle}</p>}
                    </div>
                </div>

                {middle && <div className="flex-1 px-8">{middle}</div>}

                <div className="flex items-center gap-2">
                    {actions.map((action, i) => (
                        <div key={i}>{action}</div>
                    ))}
                    {backRoute && <BackButton className="mr-2" />}
                </div>
            </div>

            {stats.length > 0 && (
                <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mt-6 pt-6 border-t border-slate-50">
                    {stats.map((stat, i) => (
                        <div key={i} className="flex flex-col">
                            <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{stat.label}</span>
                            <div className={`text-lg font-black ${stat.color === 'red' ? 'text-red-600' : stat.color === 'emerald' ? 'text-emerald-600' : 'text-slate-900'}`}>
                                {stat.prefix && <span className="text-[10px] text-slate-400 ml-1">{stat.prefix}</span>}
                                {stat.value}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
