import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import BackButton from '@/Components/BackButton';

export default function Index({ auth, type, contacts, flash, search: initialSearch }) {
    const [search, setSearch] = useState(initialSearch || '');
    const titles = {
        all: 'دليل جهات الاتصال الشامل',
        customer: 'قائمة العملاء',
        supplier: 'قائمة الموردين',
        employee: 'إدارة الموظفين',
        partner: 'شركات شقيقة'
    };

    useEffect(() => {
        const timer = setTimeout(() => {
            if (search !== initialSearch) {
                router.get(route('contacts.index'), { type, search }, { 
                    preserveState: true,
                    replace: true 
                });
            }
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800">{titles[type] || titles.all}</h2>}>
            <Head title={titles[type] || titles.all} />

            <div className="min-h-screen bg-[#f8fafc] pb-12" dir="rtl">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                    
                    {/* Header Section */}
                    <div className="mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                        <div className="flex-1">
                            <h1 className="text-2xl font-black text-slate-900">{titles[type] || titles.all}</h1>
                            <p className="mt-1 text-sm text-slate-500 font-medium">إدارة كافة الكيانات والأشخاص المرتبطين بالمنشأة من مكان واحد</p>
                        </div>
                        <div className="flex items-center gap-3">
                            <Link 
                                href={route('contacts.create', { type: type === 'all' ? 'customer' : type })}
                                className="btn-primary px-6 py-2.5 shadow-xl shadow-blue-900/10"
                            >
                                + إضافة جهة جديدة
                            </Link>
                            <BackButton />
                        </div>
                    </div>

                    {/* Professional Filter & Search Bar */}
                    <div className="mb-8 bg-white rounded-[2rem] border border-slate-100 shadow-sm p-6">
                        <form 
                            onSubmit={(e) => {
                                e.preventDefault();
                                router.get(route('contacts.index'), { type, search }, { preserveState: true });
                            }}
                            className="grid grid-cols-1 md:grid-cols-12 gap-4 items-end"
                        >
                            <div className="md:col-span-4">
                                <label className="block text-sm font-bold text-slate-500 mb-2 mr-1">تصفية حسب النوع</label>
                                <div className="flex items-center gap-1 p-1 bg-slate-50 rounded-2xl border border-slate-100">
                                    {[
                                        { id: 'all', label: 'الكل' },
                                        { id: 'customer', label: 'عملاء' },
                                        { id: 'supplier', label: 'موردين' },
                                        { id: 'employee', label: 'موظفين' },
                                        { id: 'partner', label: 'شركات' }
                                    ].map((tab) => (
                                        <Link
                                            key={tab.id}
                                            href={route('contacts.index', { type: tab.id, search })}
                                            className={`flex-1 text-center py-2 rounded-xl text-xs font-black transition-all ${
                                                type === tab.id 
                                                ? 'bg-white text-blue-600 shadow-sm border border-slate-100' 
                                                : 'text-slate-400 hover:text-slate-600'
                                            }`}
                                        >
                                            {tab.label}
                                        </Link>
                                    ))}
                                </div>
                            </div>

                            <div className="md:col-span-6 relative">
                                <label className="block text-sm font-bold text-slate-500 mb-2 mr-1">البحث السريع</label>
                                <div className="relative group">
                                    <input
                                        type="text"
                                        placeholder="ابحث بالاسم، الهاتف، أو الرقم الضريبي..."
                                        className="w-full px-4 py-3 bg-slate-50 border-slate-100 rounded-2xl text-sm font-bold text-slate-700 focus:bg-white focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all"
                                        value={search}
                                        onChange={e => setSearch(e.target.value)}
                                    />
                                    {search && (
                                        <button 
                                            type="button"
                                            onClick={() => { setSearch(''); router.get(route('contacts.index'), { type, search: '' }); }}
                                            className="absolute inset-y-0 left-4 flex items-center text-slate-300 hover:text-rose-500"
                                        >
                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    )}
                                </div>
                            </div>

                            <div className="md:col-span-2">
                                <button
                                    type="submit"
                                    className="w-full bg-blue-600 hover:bg-blue-700 text-white py-3.5 rounded-2xl font-black shadow-lg shadow-blue-600/20 transition-all hover:-translate-y-0.5 active:scale-95 text-sm"
                                >
                                    بحث الآن
                                </button>
                            </div>
                        </form>
                    </div>

                    {flash?.success && (
                        <div className="mb-6 bg-emerald-50 border border-emerald-100 p-4 rounded-2xl text-emerald-800 font-bold flex items-center gap-3">
                            <span className="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                            {flash.success}
                        </div>
                    )}

                    <div className="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full text-right text-sm">
                                <thead className="bg-slate-50/50 text-slate-500 border-b border-slate-100">
                                    <tr>
                                        <th className="px-6 py-5 font-black uppercase tracking-wider">الجهة / الاسم</th>
                                        <th className="px-6 py-5 font-black uppercase tracking-wider">التواصل والضريبة</th>
                                        <th className="px-6 py-5 font-black uppercase tracking-wider">نوع الجهة</th>
                                        <th className="px-6 py-5 font-black uppercase tracking-wider">الحساب المحاسبي</th>
                                        <th className="px-6 py-5 font-black uppercase tracking-wider text-center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-50">
                                    {contacts.length > 0 ? (
                                        contacts.map(contact => (
                                            <tr key={contact.id} className="hover:bg-slate-50/50 transition-colors group">
                                                <td className="px-6 py-5">
                                                    <div className="font-black text-slate-900 text-base flex items-center gap-2">
                                                        {contact.name}
                                                        {contact.is_sub_client && contact.main_company && (
                                                            <span className="text-xs font-bold text-teal-600 bg-teal-50 px-2 py-0.5 rounded border border-teal-100">
                                                                تابع لـ: {contact.main_company.name}
                                                            </span>
                                                        )}
                                                    </div>
                                                    <div className="text-[10px] text-slate-400 font-mono mt-0.5">REF: {contact.id}</div>
                                                </td>
                                                <td className="px-6 py-5">
                                                    <div className="font-mono text-xs text-slate-600 mb-1">{contact.tax_number || '---'}</div>
                                                    <div className="text-slate-500 font-medium">{contact.phone || contact.email || '---'}</div>
                                                </td>
                                                <td className="px-6 py-5">
                                                    <div className="flex flex-wrap gap-1.5">
                                                        {contact.is_customer && <span className="bg-blue-50 text-blue-700 text-[10px] px-3 py-1 rounded-full border border-blue-100 font-black">عميل</span>}
                                                        {contact.is_supplier && <span className="bg-rose-50 text-rose-700 text-[10px] px-3 py-1 rounded-full border border-rose-100 font-black">مورد</span>}
                                                        {contact.is_related_party && <span className="bg-amber-50 text-amber-700 text-[10px] px-3 py-1 rounded-full border border-amber-100 font-black">طرف علاقة</span>}
                                                        {contact.is_main_company && <span className="bg-indigo-50 text-indigo-700 text-[10px] px-3 py-1 rounded-full border border-indigo-100 font-black">شركة رئيسية</span>}
                                                        {contact.is_sub_client && <span className="bg-teal-50 text-teal-700 text-[10px] px-3 py-1 rounded-full border border-teal-100 font-black">عميل فرعي</span>}
                                                        {contact.type === 'employee' && <span className="bg-emerald-50 text-emerald-700 text-[10px] px-3 py-1 rounded-full border border-emerald-100 font-black">موظف</span>}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-5">
                                                    <div className="flex flex-col">
                                                        <span className="text-xs font-black text-slate-800">
                                                            {contact.receivable_account?.name || contact.payable_account?.name || 'جهات الاتصال (1130)'}
                                                        </span>
                                                        <span className="text-[10px] text-blue-500 font-mono font-bold">
                                                            {contact.receivable_account?.code || contact.payable_account?.code || '1130'}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-5 text-center">
                                                    <div className="flex items-center justify-center gap-2">
                                                        <Link href={route('contacts.edit', contact.id)} className="w-9 h-9 flex items-center justify-center bg-slate-50 text-slate-400 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all" title="تعديل">
                                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                        </Link>
                                                        <Link 
                                                            href={route('contacts.destroy', contact.id)} 
                                                            method="delete" 
                                                            as="button"
                                                            className="w-9 h-9 flex items-center justify-center bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-600 rounded-xl transition-all"
                                                            onClick={e => {
                                                                if(!confirm('هل أنت متأكد من الحذف النهائي؟')) e.preventDefault();
                                                            }}
                                                            title="حذف"
                                                        >
                                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        </Link>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan="5" className="px-6 py-24 text-center">
                                                <div className="flex flex-col items-center gap-4 text-slate-300">
                                                    <div className="w-20 h-20 rounded-full bg-slate-50 flex items-center justify-center">
                                                        <svg className="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 005.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                                    </div>
                                                    <div className="font-black">لا توجد جهات اتصال مسجلة ضمن هذا التصنيف</div>
                                                    <Link href={route('contacts.create', {type: type === 'all' ? 'customer' : type})} className="text-blue-600 hover:underline font-bold text-sm">إضافة جهة اتصال الآن</Link>
                                                </div>
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
