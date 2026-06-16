import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Create({ auth, type, accounts = [], mainCompanies = [] }) {
    const { data, setData, post, processing, errors } = useForm({
        type: type,
        is_customer: type === 'customer',
        is_supplier: type === 'supplier',
        is_related_party: type === 'partner',
        is_main_company: false,
        is_sub_client: false,
        main_company_id: '',
        name: '',
        tax_number: '',
        phone: '',
        email: '',
        notes: '',
        receivable_account_id: '',
        payable_account_id: '',
    });

    const titles = {
        customer: 'إضافة عميل جديد',
        supplier: 'إضافة مورد جديد',
        employee: 'إضافة موظف جديد',
        partner: 'إضافة شركة شقيقة / طرف ذو علاقة'
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('contacts.store'));
    };

    // Filter accounts for receivable (assets 1xxx) and payable
    const receivableAccounts = accounts.filter(acc => String(acc.code).startsWith('1'));
    const payableAccounts = accounts.filter(acc => String(acc.code).startsWith('1') || String(acc.code).startsWith('2'));

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={titles[type]} />

            <div className="min-h-screen bg-[#f8fafc] pb-6" dir="rtl">
                {/* Top Bar */}
                <div className="bg-white border-b border-slate-200 sticky top-0 z-30 px-8 py-3 flex items-center justify-between shadow-sm">
                    <div className="flex items-center gap-4">
                        <div className="bg-indigo-600 p-2 rounded-xl text-white shadow-md">
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        <div>
                            <div className="flex items-center gap-3">
                                <Link href={route('contacts.index', { type })} className="text-slate-400 hover:text-indigo-600 transition-colors text-sm font-bold">جهات الاتصال</Link>
                                <span className="text-slate-300">/</span>
                                <h1 className="text-lg font-black text-slate-800 leading-none">{titles[type]}</h1>
                            </div>
                            <p className="text-[10px] text-slate-400 font-bold mt-0.5 uppercase tracking-wider">Contact Registration</p>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        <button onClick={submit} disabled={processing} className="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-md transition-all">
                            {processing ? 'جاري الحفظ...' : 'حفظ البيانات'}
                        </button>
                        <Link href={route('contacts.index', { type })} className="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-sm font-bold transition-all">
                            إلغاء
                        </Link>
                    </div>
                </div>

                <div className="max-w-[1400px] mx-auto px-8 pt-6">
                    <form onSubmit={submit}>
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">

                            {/* Column 1: الأدوار والبيانات الأساسية */}
                            <div className="space-y-6">
                                <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                                    <SectionTitle icon="🏷️" title="نوع وأدوار الجهة" />
                                    <div className="space-y-2.5">
                                        <RoleCheckbox
                                            label="عميل (Customer)"
                                            checked={data.is_customer}
                                            onChange={e => setData('is_customer', e.target.checked)}
                                            color="indigo"
                                        />
                                        <RoleCheckbox
                                            label="مورد (Supplier)"
                                            checked={data.is_supplier}
                                            onChange={e => setData('is_supplier', e.target.checked)}
                                            color="rose"
                                        />
                                        <RoleCheckbox
                                            label="طرف ذو علاقة (Related Party)"
                                            checked={data.is_related_party}
                                            onChange={e => setData('is_related_party', e.target.checked)}
                                            color="amber"
                                        />

                                        <div className="border-t border-slate-100 pt-2.5 mt-2">
                                            <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">تصنيف العملاء (للنقليات)</p>
                                            <RoleCheckbox
                                                label="شركة رئيسية (لها عملاء فرعيين)"
                                                checked={data.is_main_company}
                                                onChange={e => {
                                                    setData('is_main_company', e.target.checked);
                                                    if (e.target.checked) {
                                                        setData('is_customer', true);
                                                        setData('is_sub_client', false);
                                                        setData('main_company_id', '');
                                                    }
                                                }}
                                                color="violet"
                                            />
                                            <RoleCheckbox
                                                label="عميل فرعي (تابع لشركة رئيسية)"
                                                checked={data.is_sub_client}
                                                onChange={e => {
                                                    setData('is_sub_client', e.target.checked);
                                                    if (e.target.checked) {
                                                        setData('is_customer', true);
                                                        setData('is_main_company', false);
                                                    } else {
                                                        setData('main_company_id', '');
                                                    }
                                                }}
                                                color="teal"
                                            />
                                        </div>

                                        {data.is_sub_client && (
                                            <div className="mt-3 bg-teal-50/50 border border-teal-100 rounded-xl p-3 animate-in fade-in slide-in-from-top-2 duration-200">
                                                <CField label="الشركة الرئيسية التابع لها *" error={errors.main_company_id}>
                                                    <select
                                                        required={data.is_sub_client}
                                                        className="contact-select bg-white"
                                                        value={data.main_company_id}
                                                        onChange={e => setData('main_company_id', e.target.value)}
                                                    >
                                                        <option value="">-- اختر الشركة الرئيسية --</option>
                                                        {mainCompanies.map(c => (
                                                            <option key={c.id} value={c.id}>{c.name}</option>
                                                        ))}
                                                    </select>
                                                </CField>
                                            </div>
                                        )}
                                    </div>
                                </div>

                                {/* Info box explaining the logic */}
                                {data.is_main_company && (
                                    <div className="bg-violet-50 border border-violet-100 rounded-xl p-4 text-xs font-bold text-violet-800">
                                        <p>💡 الشركة الرئيسية هي العميل الذي يتم إصدار الفاتورة باسمه. العملاء الفرعيين يتبعون لها ويظهرون في الرحلة للتوضيح فقط.</p>
                                    </div>
                                )}
                                {data.is_sub_client && (
                                    <div className="bg-teal-50 border border-teal-100 rounded-xl p-4 text-xs font-bold text-teal-800">
                                        <p>💡 العميل الفرعي يمثل موقع الاستلام ويتبع لشركة رئيسية. يظهر في الرحلة والفاتورة للتوضيح فقط.</p>
                                    </div>
                                )}
                            </div>

                            {/* Column 2: البيانات الشخصية */}
                            <div className="space-y-6">
                                <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                                    <SectionTitle icon="📋" title="البيانات الأساسية" />
                                    <div className="space-y-3">
                                        <CField label="اسم الجهة / الشركة" required error={errors.name}>
                                            <input type="text" required autoFocus className="contact-input text-indigo-700 font-black" value={data.name} onChange={e => setData('name', e.target.value)} placeholder="أدخل الاسم الرسمي للجهة..." />
                                        </CField>

                                        <div className="grid grid-cols-2 gap-3">
                                            <CField label="الرقم الضريبي" error={errors.tax_number}>
                                                <input type="text" className="contact-input font-mono" value={data.tax_number} onChange={e => setData('tax_number', e.target.value)} placeholder="3xxxxxxxxxx00003" />
                                            </CField>
                                            <CField label="رقم الهاتف" error={errors.phone}>
                                                <input type="text" className="contact-input text-left" dir="ltr" value={data.phone} onChange={e => setData('phone', e.target.value)} placeholder="05xxxxxxxx" />
                                            </CField>
                                        </div>

                                        <CField label="البريد الإلكتروني" error={errors.email}>
                                            <input type="email" className="contact-input text-left" dir="ltr" value={data.email} onChange={e => setData('email', e.target.value)} placeholder="name@company.com" />
                                        </CField>

                                        <CField label="ملاحظات إضافية" error={errors.notes}>
                                            <textarea className="contact-input h-16 resize-none text-xs" value={data.notes} onChange={e => setData('notes', e.target.value)} placeholder="ملاحظات أو معلومات إضافية..." />
                                        </CField>
                                    </div>
                                </div>
                            </div>

                            {/* Column 3: الحسابات المحاسبية */}
                            <div className="space-y-6">
                                <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                                    <SectionTitle icon="📊" title="الربط المحاسبي" />
                                    <div className="space-y-3">
                                        <CField label="حساب المدينين (Receivable)" error={errors.receivable_account_id}>
                                            <select className="contact-select" value={data.receivable_account_id} onChange={e => setData('receivable_account_id', e.target.value)}>
                                                <option value="">
                                                    -- {data.is_customer ? 'إنشاء تلقائي تحت العملاء (1103)' : 
                                                        data.type === 'employee' ? 'إنشاء تلقائي تحت عهد الموظفين (1106)' : 
                                                        'لا يتطلب / أو سيتم التحديد يدوياً'} --
                                                </option>
                                                {receivableAccounts.map(acc => (
                                                    <option key={acc.id} value={acc.id}>{acc.code} - {acc.name}</option>
                                                ))}
                                            </select>
                                            <p className="text-[10px] text-slate-400 mt-0.5">يُترك فارغاً لإنشاء حساب فرعي تلقائياً للعميل/الموظف</p>
                                        </CField>

                                        <CField label="حساب الدائنين (Payable)" error={errors.payable_account_id}>
                                            <select className="contact-select" value={data.payable_account_id} onChange={e => setData('payable_account_id', e.target.value)}>
                                                <option value="">
                                                    -- {data.is_supplier ? 'إنشاء تلقائي تحت الموردين (2101)' : 
                                                        data.is_related_party ? 'إنشاء تلقائي تحت دائنون متنوعون (2102)' : 
                                                        'لا يتطلب / أو سيتم التحديد يدوياً'} --
                                                </option>
                                                {payableAccounts.map(acc => (
                                                    <option key={acc.id} value={acc.id}>{acc.code} - {acc.name}</option>
                                                ))}
                                            </select>
                                            <p className="text-[10px] text-slate-400 mt-0.5">يُترك فارغاً لإنشاء حساب فرعي تلقائياً للمورد/طرف ذو علاقة</p>
                                        </CField>
                                    </div>

                                    {/* Accounting summary */}
                                    <div className="mt-4 bg-slate-900 rounded-xl p-4 text-white">
                                        <p className="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2">ملخص الربط المحاسبي المتوقع</p>
                                        <div className="space-y-1.5">
                                            <div className="flex justify-between text-xs">
                                                <span className="text-slate-400">المدينين (العملاء)</span>
                                                <span className="font-bold text-emerald-400">
                                                    {data.receivable_account_id ? accounts.find(a => a.id == data.receivable_account_id)?.code : 
                                                     (data.is_customer ? '1103-xxx' : data.type === 'employee' ? '1106-xxx' : 'لا يتطلب')}
                                                </span>
                                            </div>
                                            <div className="flex justify-between text-xs">
                                                <span className="text-slate-400">الدائنين (الموردين)</span>
                                                <span className="font-bold text-amber-400">
                                                    {data.payable_account_id ? accounts.find(a => a.id == data.payable_account_id)?.code : 
                                                     (data.is_supplier ? '2101-xxx' : data.is_related_party ? '2102-xxx' : 'لا يتطلب')}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Errors Display */}
                                {Object.keys(errors).length > 0 && (
                                    <div className="bg-rose-50 border border-rose-200 rounded-xl p-4">
                                        <p className="text-xs font-bold text-rose-700 mb-2">⚠️ يرجى تصحيح الأخطاء:</p>
                                        <ul className="text-[11px] text-rose-600 space-y-1 list-disc list-inside">
                                            {Object.entries(errors).map(([key, val]) => (
                                                <li key={key}>{val}</li>
                                            ))}
                                        </ul>
                                    </div>
                                )}
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .contact-input {
                    width: 100%;
                    border: 1px solid #e2e8f0;
                    border-radius: 0.6rem;
                    padding: 0.45rem 0.75rem;
                    font-size: 0.8rem;
                    font-weight: 600;
                    color: #1e293b;
                    background-color: #fff;
                    transition: border-color 0.2s, box-shadow 0.2s;
                }
                .contact-input:focus {
                    outline: none;
                    border-color: #4f46e5;
                    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
                }
                .contact-select {
                    width: 100%;
                    border: 1px solid #e2e8f0;
                    border-radius: 0.6rem;
                    padding: 0.45rem 0.75rem;
                    font-size: 0.8rem;
                    font-weight: 600;
                    color: #1e293b;
                    background-color: #fff;
                    transition: border-color 0.2s, box-shadow 0.2s;
                    appearance: none;
                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
                    background-repeat: no-repeat;
                    background-position: left 0.5rem center;
                    background-size: 1rem;
                }
                .contact-select:focus {
                    outline: none;
                    border-color: #4f46e5;
                    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
                }
            `}} />
        </AuthenticatedLayout>
    );
}

function SectionTitle({ icon, title }) {
    return (
        <h3 className="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
            <span className="w-1.5 h-4 bg-indigo-600 rounded-full"></span>
            <span className="text-base">{icon}</span>
            {title}
        </h3>
    );
}

function CField({ label, children, required, error }) {
    return (
        <div className="flex flex-col gap-1">
            <label className="text-[11px] font-bold text-slate-500">
                {label} {required && <span className="text-rose-500">*</span>}
            </label>
            {children}
            {error && <p className="text-[10px] text-rose-600 font-bold mt-0.5">{error}</p>}
        </div>
    );
}

function RoleCheckbox({ label, checked, onChange, color }) {
    const colors = {
        indigo: { bg: 'bg-indigo-50', border: 'border-indigo-200', text: 'text-indigo-700', check: 'text-indigo-600' },
        rose: { bg: 'bg-rose-50', border: 'border-rose-200', text: 'text-rose-700', check: 'text-rose-600' },
        amber: { bg: 'bg-amber-50', border: 'border-amber-200', text: 'text-amber-700', check: 'text-amber-600' },
        violet: { bg: 'bg-violet-50', border: 'border-violet-200', text: 'text-violet-700', check: 'text-violet-600' },
        teal: { bg: 'bg-teal-50', border: 'border-teal-200', text: 'text-teal-700', check: 'text-teal-600' },
    };
    const c = colors[color] || colors.indigo;
    
    return (
        <label className={`flex items-center gap-2.5 cursor-pointer p-2 rounded-lg transition-all ${checked ? `${c.bg} ${c.border} border` : 'hover:bg-slate-50 border border-transparent'}`}>
            <input 
                type="checkbox" 
                checked={checked}
                onChange={onChange}
                className={`rounded border-slate-300 ${c.check} focus:ring-indigo-500 w-4 h-4`}
            />
            <span className={`text-xs font-bold ${checked ? c.text : 'text-slate-500'}`}>{label}</span>
        </label>
    );
}
