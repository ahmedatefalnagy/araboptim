import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, settings, fiscalYears }) {
    const { data, setData, put, processing, errors, reset } = useForm({
        company_name: settings.company_name || '',
        company_name_en: settings.company_name_en || '',
        company_address: settings.company_address || '',
        company_phone: settings.company_phone || '',
        company_fax: settings.company_fax || '',
        company_email: settings.company_email || '',
        company_vat_no: settings.company_vat_no || '',
        company_commercial_record: settings.company_commercial_record || '',
        bank_name: settings.bank_name || '',
        account_number: settings.account_number || '',
        iban: settings.iban || '',
        default_fiscal_year_id: settings.default_fiscal_year_id || '',
        enable_advances: settings.enable_advances === '1' || settings.enable_advances === true,
        enable_vouchers: settings.enable_vouchers === '1' || settings.enable_vouchers === true,
        enable_invoices: settings.enable_invoices === '1' || settings.enable_invoices === true,
        enable_financial_statements: settings.enable_financial_statements === '1' || settings.enable_financial_statements === true,
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('settings.update'), {
            ...data,
            enable_advances: data.enable_advances ? '1' : '0',
            enable_vouchers: data.enable_vouchers ? '1' : '0',
            enable_invoices: data.enable_invoices ? '1' : '0',
            enable_financial_statements: data.enable_financial_statements ? '1' : '0',
        });
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="إعدادات الشركة" />

            <div className="min-h-screen bg-gray-50" dir="rtl">
                <div className="mx-auto max-w-4xl p-6">
                    <div className="mb-6">
                        <h1 className="text-2xl font-bold text-gray-900">إعدادات الشركة</h1>
                        <p className="mt-1 text-sm text-gray-600">بيانات الشركة والسنة المالية الافتراضية</p>
                    </div>

                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <form onSubmit={submit} className="space-y-8">
                            <div>
                                <h3 className="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">بيانات الشركة</h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="md:col-span-2">
                                        <label className="block text-sm font-medium text-gray-700 mb-1">اسم الشركة (عربي)</label>
                                        <input
                                            type="text"
                                            className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                            value={data.company_name}
                                            onChange={e => setData('company_name', e.target.value)}
                                            placeholder="اسم الشركة بالعربي..."
                                        />
                                    </div>

                                    <div className="md:col-span-2">
                                        <label className="block text-sm font-medium text-gray-700 mb-1">اسم الشركة (إنجليزي)</label>
                                        <input
                                            type="text"
                                            dir="ltr"
                                            className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                            value={data.company_name_en}
                                            onChange={e => setData('company_name_en', e.target.value)}
                                            placeholder="Company name in English..."
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                                        <input
                                            type="text"
                                            dir="ltr"
                                            className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                            value={data.company_email}
                                            onChange={e => setData('company_email', e.target.value)}
                                            placeholder="email@example.com"
                                        />
                                    </div>

                                    <div className="md:col-span-2">
                                        <label className="block text-sm font-medium text-gray-700 mb-1">العنوان</label>
                                        <textarea
                                            className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                            rows="2"
                                            value={data.company_address}
                                            onChange={e => setData('company_address', e.target.value)}
                                            placeholder="عنوان الشركة..."
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">الهاتف</label>
                                        <input
                                            type="text"
                                            className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                            value={data.company_phone}
                                            onChange={e => setData('company_phone', e.target.value)}
                                            placeholder="الهاتف..."
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">التليفون</label>
                                        <input
                                            type="text"
                                            className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                            value={data.company_fax}
                                            onChange={e => setData('company_fax', e.target.value)}
                                            placeholder="التليفون..."
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">الرقم الضريبي</label>
                                        <input
                                            type="text"
                                            className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                            value={data.company_vat_no}
                                            onChange={e => setData('company_vat_no', e.target.value)}
                                            placeholder="الرقم الضريبي..."
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">رقم السجل التجاري</label>
                                        <input
                                            type="text"
                                            className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                            value={data.company_commercial_record}
                                            onChange={e => setData('company_commercial_record', e.target.value)}
                                            placeholder="رقم السجل التجاري..."
                                        />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 className="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">بيانات البنك</h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">اسم البنك</label>
                                        <input
                                            type="text"
                                            className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                            value={data.bank_name}
                                            onChange={e => setData('bank_name', e.target.value)}
                                            placeholder="اسم البنك..."
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">رقم الحساب</label>
                                        <input
                                            type="text"
                                            className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                            value={data.account_number}
                                            onChange={e => setData('account_number', e.target.value)}
                                            placeholder="رقم الحساب..."
                                        />
                                    </div>

                                    <div className="md:col-span-2">
                                        <label className="block text-sm font-medium text-gray-700 mb-1">الآيبان (IBAN)</label>
                                        <input
                                            type="text"
                                            className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                            value={data.iban}
                                            onChange={e => setData('iban', e.target.value)}
                                            placeholder="SA..."
                                        />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 className="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">السنة المالية</h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="md:col-span-2">
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            السنة المالية الافتراضية <span className="text-red-500">*</span>
                                        </label>
                                        <select
                                            required
                                            className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm bg-blue-50"
                                            value={data.default_fiscal_year_id}
                                            onChange={e => setData('default_fiscal_year_id', e.target.value)}
                                        >
                                            <option value="">-- اختر السنة المالية --</option>
                                            {fiscalYears.map(y => (
                                                <option key={y.id} value={y.id}>{y.name}</option>
                                            ))}
                                        </select>
                                        <p className="mt-1 text-xs text-gray-500">
                                            سيتم تعيين جميع تواريخ الادخال لهذه السنة المالية افتراضياً
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 className="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">تفعيل الأقسام</h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <label className="flex items-center gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                                        <input
                                            type="checkbox"
                                            checked={data.enable_advances}
                                            onChange={e => setData('enable_advances', e.target.checked)}
                                            className="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <div>
                                            <span className="font-bold text-gray-700">العهد والسلف</span>
                                            <p className="text-xs text-gray-500 mt-1">إدارة عهد الموظفين والسلف</p>
                                        </div>
                                    </label>

                                    <label className="flex items-center gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                                        <input
                                            type="checkbox"
                                            checked={data.enable_vouchers}
                                            onChange={e => setData('enable_vouchers', e.target.checked)}
                                            className="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <div>
                                            <span className="font-bold text-gray-700">السندات</span>
                                            <p className="text-xs text-gray-500 mt-1">سندات القبض والصرف ومصروفات</p>
                                        </div>
                                    </label>

                                    <label className="flex items-center gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                                        <input
                                            type="checkbox"
                                            checked={data.enable_invoices}
                                            onChange={e => setData('enable_invoices', e.target.checked)}
                                            className="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <div>
                                            <span className="font-bold text-gray-700">الفواتير</span>
                                            <p className="text-xs text-gray-500 mt-1">فواتير المبيعات والمشتريات</p>
                                        </div>
                                    </label>

                                    <label className="flex items-center gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                                        <input
                                            type="checkbox"
                                            checked={data.enable_financial_statements}
                                            onChange={e => setData('enable_financial_statements', e.target.checked)}
                                            className="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <div>
                                            <span className="font-bold text-gray-700">القوائم المالية</span>
                                            <p className="text-xs text-gray-500 mt-1">الميزانية وقائمة الدخل</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div className="pt-4 border-t border-gray-100">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold shadow-sm hover:bg-blue-700 disabled:opacity-50 transition"
                                >
                                    {processing ? 'جاري الحفظ...' : 'حفظ الإعدادات'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
