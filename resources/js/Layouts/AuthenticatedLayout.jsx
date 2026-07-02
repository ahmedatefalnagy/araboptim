import { useState, useRef, useEffect } from 'react';
import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link, usePage, router } from '@inertiajs/react';

const UserMenu = ({ user }) => {
    const [isOpen, setIsOpen] = useState(false);
    const menuRef = useRef();

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (menuRef.current && !menuRef.current.contains(event.target)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    return (
        <div className="relative border-t border-[#334155] p-4 bg-[#1e293b] mt-auto" ref={menuRef}>
            {/* Upward Menu */}
            {isOpen && (
                <div className="absolute bottom-full right-0 left-0 mb-2 bg-[#0f172a] rounded shadow-xl border border-[#334155] overflow-hidden z-[100] animate-in fade-in slide-in-from-bottom-2 duration-200">
                    <div className="p-1 space-y-0.5">
                        <Link 
                            href={route('profile.edit')}
                            className="flex items-center gap-3 px-3 py-2 text-sm text-[#c2c7d0] hover:bg-white/5 hover:text-white rounded transition-colors"
                        >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            الملف الشخصي
                        </Link>
                        <button 
                            onClick={() => router.post(route('logout'))}
                            className="w-full flex items-center gap-3 px-3 py-2 text-sm text-red-400 hover:bg-red-950/20 rounded transition-colors text-right"
                        >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            تسجيل الخروج
                        </button>
                    </div>
                </div>
            )}

            <div 
                onClick={() => setIsOpen(!isOpen)}
                className="flex items-center gap-3 cursor-pointer group hover:bg-white/5 p-2 rounded transition-colors"
            >
                <div className="w-[34px] h-[34px] rounded-full bg-[#334155] flex items-center justify-center text-white font-bold text-xs">
                    {user?.name ? user.name.substring(0, 2) : '??'}
                </div>
                <div className="flex-1 min-w-0">
                    <p className="text-sm font-semibold text-[#c2c7d0] group-hover:text-white truncate leading-tight">{user?.name || 'مستخدم'}</p>
                </div>
                <svg className={`w-4 h-4 text-[#c2c7d0] group-hover:text-white transition-transform ${isOpen ? 'rotate-180' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
    );
};

export default function AuthenticatedLayout({ user, header, children }) {
    const { url } = usePage();
    const [sidebarOpen, setSidebarOpen] = useState(typeof window !== 'undefined' ? window.innerWidth >= 1280 : false);
    
    useEffect(() => {
        if (window.innerWidth < 1280) {
            setSidebarOpen(false);
        }
    }, [url]);

    const [openMenus, setOpenMenus] = useState({
        sales: url.includes('/invoices') && (url.includes('sale') || !url.includes('purchase') && !url.includes('work')),
        purchases: url.includes('/invoices') && url.includes('purchase'),
        contacts: url.includes('/contacts'),
        inventory: url.includes('/items') || url.includes('/warehouses') || url.includes('/item-categories') || url.includes('/units'),
        cash: url.includes('/vouchers') || url.includes('/cash-register'),
        accounting: url.includes('/accounts') || url.includes('/journal') || url.includes('/fixed-assets') || url.includes('/journal-entries'),
        hr: url.includes('/hr'),
        logistics: url.includes('/logistics'),
        cost_centers_group: url.includes('/cost-centers') || url.includes('/reports/cost-center-cashflow'),
    });

    const toggleMenu = (menu) => {
        setOpenMenus(prev => {
            const nextState = {};
            Object.keys(prev).forEach(key => {
                nextState[key] = key === menu ? !prev[menu] : false;
            });
            return nextState;
        });
    };

    const isActive = (checkUrl) => url.startsWith(checkUrl);
    const exactActive = (checkUrl) => url === checkUrl || url === checkUrl + '/';

    const isAnyGroupOpen = Object.values(openMenus).some(value => value === true);

    const isInvoiceTypeActive = (type) => {
        if (typeof window === 'undefined') return false;
        const pathname = window.location.pathname;
        const searchParams = new URLSearchParams(window.location.search);
        
        const isMatch = pathname.includes('/invoices');
        if (!isMatch) return false;
        
        const typeParam = searchParams.get('type');
        let res = false;
        if (type === 'sale') {
            res = typeParam === 'sale' || !typeParam;
        } else {
            res = typeParam === type;
        }
        console.log(`[DEBUG] type: ${type}, pathname: ${pathname}, typeParam: ${typeParam}, result: ${res}`);
        return res;
    };

    const MenuItem = ({ href, active, icon, label, indent = false }) => {
        const shouldBeActive = active && (indent || !isAnyGroupOpen);
        
        return (
            <Link
                href={href}
                className={`flex items-center gap-3 px-3 py-2 transition-all duration-150 rounded ${
                    shouldBeActive 
                        ? indent
                            ? 'bg-blue-50 text-blue-700 font-bold' // Active child
                            : 'bg-[#2563eb] text-white font-bold' // Active parent
                        : indent
                            ? 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-semibold' // Inactive child (on white bg)
                            : 'text-[#c2c7d0] hover:bg-white/5 hover:text-white' // Inactive parent (on dark bg)
                } ${indent ? 'pr-8 text-[13px]' : 'text-sm'}`}
            >
                {icon ? (
                    <span className={`w-5 h-5 flex items-center justify-center ${shouldBeActive && !indent ? 'text-white' : indent ? (shouldBeActive ? 'text-blue-600' : 'text-slate-400') : 'text-[#c2c7d0]'}`}>{icon}</span>
                ) : indent ? (
                <span className="w-5 h-5 flex items-center justify-center">
                    <svg className={`w-2 h-2 ${shouldBeActive ? 'text-blue-500' : 'text-slate-400'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" strokeWidth="3" />
                    </svg>
                </span>
            ) : null}
            <span className={indent ? "" : "font-medium"}>{label}</span>
        </Link>
        );
    };

    const MenuGroup = ({ id, label, icon, children }) => {
        const isOpen = openMenus[id];
        const isGroupActive = (id === 'sales' && url.includes('/invoices') && (url.includes('sale') || !url.includes('purchase') && !url.includes('work'))) ||
                              (id === 'purchases' && url.includes('/invoices') && url.includes('purchase')) ||
                              (id === 'contacts' && url.includes('/contacts')) ||
                              (id === 'inventory' && (url.includes('/items') || url.includes('/warehouses') || url.includes('/item-categories') || url.includes('/units'))) ||
                              (id === 'cash' && (url.includes('/vouchers') || url.includes('/cash-register'))) ||
                              (id === 'accounting' && (url.includes('/accounts') || url.includes('/journal') || url.includes('/fixed-assets') || url.includes('/journal-entries'))) ||
                              (id === 'hr' && url.includes('/hr')) ||
                              (id === 'logistics' && url.includes('/logistics')) ||
                              (id === 'cost_centers_group' && (url.includes('/cost-centers') || url.includes('/reports/cost-center-cashflow')));

        const shouldHighlight = isAnyGroupOpen ? isOpen : isGroupActive;

        return (
            <div className="mb-1">
                <button
                    onClick={() => toggleMenu(id)}
                    className={`w-full flex items-center justify-between px-3 py-2 transition-all duration-150 rounded ${
                        shouldHighlight
                            ? 'bg-[#2563eb] text-white font-bold' 
                            : 'text-[#c2c7d0] hover:bg-white/5 hover:text-white font-semibold'
                    }`}
                >
                    <div className="flex items-center gap-3 text-sm">
                        <span className={`w-5 h-5 flex items-center justify-center ${shouldHighlight ? 'text-white' : 'text-[#c2c7d0]'}`}>{icon}</span>
                        {label}
                    </div>
                    <svg className={`w-3.5 h-3.5 transition-transform duration-300 ${isOpen ? 'rotate-180' : ''} ${shouldHighlight ? 'text-white' : 'text-[#c2c7d0]'}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div className={`overflow-hidden transition-all duration-300 ease-in-out ${isOpen ? 'max-h-96 opacity-100 mt-1.5 mb-2 bg-white rounded-xl shadow-inner py-1.5 px-1 border border-slate-200' : 'max-h-0 opacity-0'}`}>
                    {children}
                </div>
            </div>
        );
    };

    return (
        <div className="h-screen w-full flex overflow-hidden bg-[#f4f6f9]" dir="rtl">
            
            {/* Mobile Sidebar Overlay */}
            {sidebarOpen && (
                <div 
                    className="fixed inset-0 z-40 bg-gray-900/60 backdrop-blur-sm lg:hidden transition-opacity"
                    onClick={() => setSidebarOpen(false)}
                ></div>
            )}

            {/* Right Sidebar */}
            <aside className={`fixed lg:static shrink-0 inset-y-0 right-0 z-50 w-72 bg-[#1e293b] border-l border-[#334155] flex flex-col shadow-2xl lg:shadow-none transition-all duration-500 ease-in-out transform ${sidebarOpen ? 'translate-x-0 lg:mr-0' : 'translate-x-full lg:translate-x-0 lg:-mr-72'}`}>
                
                {/* Logo Area */}
                <div className="py-3 flex items-center justify-center border-b border-[#334155] bg-[#1e293b] sticky top-0 z-10">
                    <Link href={route('dashboard')} className="flex items-center gap-3 px-5 group">
                        <div className="w-14 h-14 rounded-full overflow-hidden flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
                            <ApplicationLogo className="w-14 h-14 object-cover" />
                        </div>
                        <div className="flex flex-col">
                            <span className="text-white font-black text-[19px] tracking-tight leading-tight">التفاؤل العربية</span>
                            <span className="text-[11px] text-slate-300 font-bold tracking-wider mt-0.5 uppercase">Arab Optimism</span>
                        </div>
                    </Link>
                </div>

                {/* Navigation Links */}
                <div className="flex-1 overflow-y-auto py-4 px-3 flex flex-col gap-1.5 custom-scrollbar bg-[#1e293b]">
                    
                    <MenuItem 
                        href={route('dashboard')} 
                        active={isActive('/dashboard')} 
                        label="القائمة الرئيسية" 
                        icon={<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>}
                    />

                    {user?.role !== 'storekeeper' && (
                        <>
                            <div className="px-3 pt-4 pb-2 text-[11px] font-bold text-[#c2c7d0]/60 uppercase tracking-widest">العمليات التشغيلية</div>

                            <MenuItem 
                                href={route('invoices.index', {type: 'work_order'})} 
                                active={isInvoiceTypeActive('work_order')} 
                                label="أوامر الشغل" 
                                icon={<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m9 9h.01" /></svg>}
                            />

                            <MenuGroup 
                                id="sales" 
                                label="دورة المبيعات" 
                                icon={<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>}
                            >
                                <MenuItem indent href={route('invoices.index', {type: 'sale_quotation'})} active={isInvoiceTypeActive('sale_quotation')} label="عروض الأسعار" />
                                <MenuItem indent href={route('invoices.index', {type: 'sale_order'})} active={isInvoiceTypeActive('sale_order')} label="أوامر البيع" />
                                <MenuItem indent href={route('invoices.index', {type: 'sale'})} active={isInvoiceTypeActive('sale')} label="فواتير المبيعات" />
                                <MenuItem indent href={route('invoices.index', {type: 'sale_return'})} active={isInvoiceTypeActive('sale_return')} label="مردودات المبيعات" />
                            </MenuGroup>

                            <MenuGroup 
                                id="purchases" 
                                label="دورة المشتريات" 
                                icon={<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>}
                            >
                                <MenuItem indent href={route('invoices.index', {type: 'purchase_quotation'})} active={isInvoiceTypeActive('purchase_quotation')} label="طلبات الشراء" />
                                <MenuItem indent href={route('invoices.index', {type: 'purchase_order'})} active={isInvoiceTypeActive('purchase_order')} label="أوامر الشراء" />
                                <MenuItem indent href={route('invoices.index', {type: 'purchase'})} active={isInvoiceTypeActive('purchase')} label="فواتير المشتريات" />
                                <MenuItem indent href={route('invoices.index', {type: 'purchase_return'})} active={isInvoiceTypeActive('purchase_return')} label="مردودات المشتريات" />
                            </MenuGroup>
                        </>
                    )}

                    <MenuGroup 
                        id="inventory" 
                        label="إدارة المستودعات والمخزون" 
                        icon={<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>}
                    >
                        <MenuItem indent href={route('items.index')} active={isActive('/items')} label="دليل المنتجات والمخزون" />
                        <MenuItem indent href={route('item-categories.index')} active={isActive('/item-categories')} label="مجموعات الأصناف" />
                        <MenuItem indent href={route('units.index')} active={isActive('/units')} label="وحدات القياس" />
                        {user?.role === 'admin' && (
                            <>
                                <MenuItem indent href={route('invoices.index', {type: 'goods_receipt'})} active={isInvoiceTypeActive('goods_receipt')} label="تسوية المستودع (إضافة بضاعة)" />
                                <MenuItem indent href={route('invoices.index', {type: 'goods_issue'})} active={isInvoiceTypeActive('goods_issue')} label="تسوية المستودع (إضافة تالف)" />
                            </>
                        )}
                    </MenuGroup>

                    {user?.role !== 'storekeeper' && (
                        <>
                            <div className="px-3 pt-4 pb-2 text-[11px] font-bold text-[#c2c7d0]/60 uppercase tracking-widest">الإدارة</div>

                            <MenuGroup 
                                id="hr" 
                                label="الموارد البشرية" 
                                icon={<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>}
                            >
                                <MenuItem indent href={route('hr.employees')} active={isActive('/hr/employees')} label="الموظفين" />
                                <MenuItem indent href={route('hr.advances')} active={isActive('/hr/advances')} label="السلف والعهد" />
                                <MenuItem indent href={route('hr.payroll')} active={isActive('/hr/payroll')} label="مسير الرواتب" />
                                <MenuItem indent href={route('hr.government-expenses')} active={isActive('/hr/government-expenses')} label="المصروفات الحكومية" />
                            </MenuGroup>
                            
                            <MenuGroup 
                                id="logistics" 
                                label="النقل والأسطول" 
                                icon={<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 11-2 0 1 1 0 012 0zm9 0a1 1 0 11-2 0 1 1 0 012 0z" /></svg>}
                            >
                                <MenuItem indent href={route('logistics.dashboard')} active={isActive('/logistics/dashboard')} label="لوحة الأسطول" />
                                <MenuItem indent href={route('logistics.vehicles.index')} active={isActive('/logistics/vehicles') && !isActive('/logistics/vehicles/assignments')} label="إدارة الشاحنات" />
                                <MenuItem indent href={route('logistics.trips.index')} active={isActive('/logistics/trips') && !isActive('/logistics/trips/monthly-billing')} label="سجل الرحلات" />
                                <MenuItem indent href={route('logistics.trips.monthly-billing')} active={isActive('/logistics/trips/monthly-billing')} label="الفوترة الشهرية للرحلات" />
                                <MenuItem indent href={route('logistics.workshop.index')} active={isActive('/logistics/workshop')} label="الصيانة والورشة" />
                            </MenuGroup>

                            <MenuGroup 
                                id="contacts" 
                                label="جهات الاتصال" 
                                icon={<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5V4H2v16h5m10 0v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5m10 0H7m6-6a3 3 0 11-6 0 3 3 0 016 0z" /></svg>}
                            >
                                <MenuItem indent href={route('contacts.index')} active={isActive('/contacts')} label="دليل جهات الاتصال" />
                            </MenuGroup>

                            <div className="px-3 pt-4 pb-2 text-[11px] font-bold text-[#c2c7d0]/60 uppercase tracking-widest">المالية</div>

                            <MenuGroup 
                                id="cash" 
                                label="إدارة الصندوق" 
                                icon={<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>}
                            >
                                <MenuItem indent href={route('vouchers.index', {type: 'receipt'})} active={isActive('/vouchers') && url.includes('type=receipt')} label="سندات القبض" />
                                <MenuItem indent href={route('vouchers.index', {type: 'payment'})} active={isActive('/vouchers') && url.includes('type=payment')} label="سندات الصرف" />
                                <MenuItem indent href={route('vouchers.cash-register')} active={isActive('/vouchers/cash-register')} label="حركة ورصيد الصندوق" />
                            </MenuGroup>

                            <MenuGroup 
                                 id="cost_centers_group" 
                                 label="مراكز التكلفة" 
                                 icon={<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>}
                            >
                                 <MenuItem indent href={route('cost-centers.index')} active={isActive('/cost-centers')} label="دليل مراكز التكلفة" />
                                 {/* <MenuItem indent href={route('reports.costCenterCashflow')} active={isActive('/reports/cost-center-cashflow')} label="تقرير حركة النقدية" /> */}
                            </MenuGroup>

                            <MenuGroup 
                                id="accounting" 
                                label="الحسابات والمالية" 
                                icon={<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>}
                            >
                                <MenuItem indent href={route('journal.entries.index')} active={isActive('/journal-entries')} label="القيود اليومية" />
                                <MenuItem indent href={route('fixed-assets.index')} active={isActive('/fixed-assets')} label="الأصول الثابتة" />
                                <MenuItem indent href={route('accounts.index')} active={isActive('/accounts')} label="شجرة الحسابات" />
                            </MenuGroup>

                            <MenuItem 
                                href={route('reports.index')} 
                                active={isActive('/reports') && !url.includes('/reports/cost-center-cashflow')} 
                                label="التقارير المالية" 
                                icon={<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>}
                            />
                        </>
                    )}

                    <div className="mt-auto pt-6 border-t border-gray-100 flex flex-col gap-1">
                        {user?.role === 'admin' && (
                            <>
                                <MenuItem 
                                    href={route('settings.index')} 
                                    active={isActive('/settings') && !isActive('/users')} 
                                    label="الإعدادات" 
                                    icon={<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>}
                                />
                                <MenuItem 
                                    href={route('users.index')} 
                                    active={isActive('/users')} 
                                    label="إدارة المستخدمين" 
                                    icon={<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>}
                                />
                            </>
                        )}
                    </div>

                </div>

                <UserMenu user={user} />
            </aside>

            {/* Main Content Area */}
            <div className="flex-1 flex flex-col min-w-0 bg-[#f8fafc]">
                
                {/* Top Desktop/Mobile Navbar */}
                <header className="h-[4.5rem] bg-white/80 backdrop-blur-xl border-b border-gray-100 flex items-center justify-between px-4 sm:px-8 z-30 sticky top-0 shadow-[0_2px_15px_rgba(0,0,0,0.02)]">
                    
                    <div className="flex items-center gap-4">
                        {/* Toggle Button */}
                        <button 
                            onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="p-2.5 rounded-xl text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition-all active:scale-90"
                        >
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div className="text-xl font-black text-slate-800 tracking-tight ml-4">
                            {header || ''}
                        </div>
                    </div>

                    {/* Top Right Quick Actions */}
                    <div className="flex items-center gap-3">
                        <div className="hidden sm:flex items-center gap-2 bg-emerald-50 px-4 py-2 rounded-2xl border border-emerald-100 transition-all hover:bg-emerald-100/50">
                            <span className="w-2 h-2 bg-emerald-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                            <span className="text-[11px] font-black text-emerald-700 uppercase tracking-wider">Online Mode</span>
                        </div>
                        
                        <button className="p-2.5 rounded-xl text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all relative">
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span className="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-full border-2 border-white"></span>
                        </button>
                    </div>
                </header>

                {/* Main scrollable content view */}
                <main className="flex-1 overflow-x-hidden overflow-y-auto relative pb-12">
                    <div className="animate-in fade-in slide-in-from-bottom-4 duration-700 ease-out">
                        {children}
                    </div>
                </main>
            </div>
            
            <style>{`
                .custom-scrollbar::-webkit-scrollbar {
                    width: 5px;
                }
                .custom-scrollbar::-webkit-scrollbar-track {
                    background: transparent;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background-color: #cbd5e1;
                    border-radius: 20px;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                    background-color: #94a3b8;
                }
            `}</style>
        </div>
    );
}
