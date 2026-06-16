import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Create({ auth, type, employees, paymentMethods, expenseAccounts, allAccounts }) {
    const { default_date } = usePage().props;
    const generateVoucherNo = () => {
        const prefix = {
            expense: 'PV-',
            advance: 'ADV-',
            petty_cash_issue: 'PCI-',
            petty_cash_receipt: 'PCR-',
            receipt: 'RV-',
            payment: 'PV-'
        }[type];
        return prefix + Math.floor(1000 + Math.random() * 9000);
    };

    const { data, setData, post, processing, errors } = useForm({
        type: type,
        voucher_no: generateVoucherNo(),
        date: default_date,
        amount: '',
        contact_id: '',
        expense_account_id: '',
        payment_account_id: '',
        debit_account_id: '',
        credit_account_id: '',
        description: '',
        debit_description: '',
        credit_description: '',
        attachment: null,
    });

    const titles = {
        expense: 'سند صرف مصروف مباشر',
        advance: 'صرف سلفة لموظف',
        petty_cash_issue: 'صرف عهدة مالية لموظف',
        petty_cash_receipt: 'تسوية (استعاضة) عهدة موظف',
        receipt: 'سند قبض رسمي',
        payment: 'سند صرف رسمي',
    };

    const themes = {
        receipt: { 
            bg: 'bg-emerald-50', 
            border: 'border-emerald-200', 
            text: 'text-emerald-900', 
            btn: 'bg-emerald-600 hover:bg-emerald-700',
            accent: 'emerald'
        },
        payment: { 
            bg: 'bg-rose-50', 
            border: 'border-rose-200', 
            text: 'text-rose-900', 
            btn: 'bg-rose-600 hover:bg-rose-700',
            accent: 'rose'
        },
        default: { 
            bg: 'bg-blue-50', 
            border: 'border-blue-200', 
            text: 'text-blue-900', 
            btn: 'bg-blue-600 hover:bg-blue-700',
            accent: 'blue'
        }
    };

    const theme = themes[type] || themes.default;

    const submit = (e) => {
        e.preventDefault();
        post(route('vouchers.store'));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <>
            <Head title={titles[type]} />

            <div className="min-h-screen bg-gray-50 pb-12" dir="rtl">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-8">
                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">{titles[type]}</h1>
                            <p className="mt-1 text-sm text-gray-600">التوثيق المالي والسندات الرسمية</p>
                        </div>
                        <Link href={route('vouchers.index', { type })} className="text-gray-600 hover:text-gray-900">
                            &larr; القائمة السابقة
                        </Link>
                    </div>

                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <form onSubmit={submit} className="space-y-6">
                            
                            <input type="hidden" value={data.type} />
                            
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">رقم السند *</label>
                                    <input
                                        type="text" required
                                        className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono"
                                        value={data.voucher_no}
                                        onChange={e => setData('voucher_no', e.target.value)}
                                    />
                                    {errors.voucher_no && <div className="text-red-500 text-sm mt-1">{errors.voucher_no}</div>}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">تاريخ العملية *</label>
                                    <input
                                        type="date" required
                                        className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        value={data.date}
                                        onChange={e => setData('date', e.target.value)}
                                    />
                                    {errors.date && <div className="text-red-500 text-sm mt-1">{errors.date}</div>}
                                </div>
                            </div>

                            <div className={`p-6 ${theme.bg} rounded-xl border ${theme.border}`}>
                                <label className={`block text-sm font-medium ${theme.text} mb-2`}>المبلغ المراد إثباته *</label>
                                <div className="relative">
                                    <input
                                        type="number" step="0.01" min="0" required
                                        className={`w-full rounded-xl border-gray-200 shadow-sm font-mono text-2xl ${theme.text} bg-white`}
                                        value={data.amount}
                                        onChange={e => setData('amount', e.target.value)}
                                    />
                                    <div className="absolute left-4 top-4 text-gray-400 font-bold">SAR</div>
                                </div>
                                {errors.amount && <div className="text-red-500 text-sm mt-1">{errors.amount}</div>}
                            </div>

                            <div className="grid grid-cols-1 gap-6 p-6 bg-gray-50 rounded-xl border border-gray-200">
                                
                                {/* Professional Receipt/Payment Logic */}
                                {['receipt', 'payment'].includes(type) ? (
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block text-sm font-bold text-gray-800 mb-2">
                                                {type === 'receipt' ? 'استلام المبالغ في (حساب البنك / الصندوق) - مدين' : 'صرف المبالغ إلى (حساب الطرف الآخر) - مدين' } *
                                            </label>
                                            <select
                                                required
                                                className="w-full rounded-xl border-gray-300 focus:ring-blue-500"
                                                value={data.debit_account_id}
                                                onChange={e => setData('debit_account_id', e.target.value)}
                                            >
                                                <option value="">-- اختر الحساب المدين --</option>
                                                {(type === 'receipt' ? paymentMethods : allAccounts).map(acc => (
                                                    <option key={acc.id} value={acc.id}>{acc.code} - {acc.name}</option>
                                                ))}
                                            </select>
                                            {errors.debit_account_id && <div className="text-red-500 text-sm mt-1">{errors.debit_account_id}</div>}
                                        </div>
                                        <div>
                                            <label className="block text-sm font-bold text-gray-800 mb-2">
                                                {type === 'receipt' ? 'تحصيل من (حساب المصدر) - دائن' : 'تم الصرف من (حساب البنك / الصندوق) - دائن' } *
                                            </label>
                                            <select
                                                required
                                                className="w-full rounded-xl border-gray-300 focus:ring-blue-500"
                                                value={data.credit_account_id}
                                                onChange={e => setData('credit_account_id', e.target.value)}
                                            >
                                                <option value="">-- اختر الحساب الدائن --</option>
                                                {(type === 'receipt' ? allAccounts : paymentMethods).map(acc => (
                                                    <option key={acc.id} value={acc.id}>{acc.code} - {acc.name}</option>
                                                ))}
                                            </select>
                                            {errors.credit_account_id && <div className="text-red-500 text-sm mt-1">{errors.credit_account_id}</div>}
                                        </div>
                                    </div>
                                ) : (
                                    <>
                                        {['advance', 'petty_cash_issue', 'petty_cash_receipt'].includes(type) && (
                                            <div>
                                                <label className="block text-sm font-bold text-gray-800 mb-2">الموظف المعني *</label>
                                                <select
                                                    required
                                                    className="w-full rounded-xl border-gray-300 shadow-sm"
                                                    value={data.contact_id}
                                                    onChange={e => setData('contact_id', e.target.value)}
                                                >
                                                    <option value="">-- اختر الموظف --</option>
                                                    {employees.map(emp => (
                                                        <option key={emp.id} value={emp.id}>{emp.name}</option>
                                                    ))}
                                                </select>
                                                {errors.contact_id && <div className="text-red-500 text-sm mt-1">{errors.contact_id}</div>}
                                            </div>
                                        )}

                                        {['expense', 'petty_cash_receipt'].includes(type) && (
                                            <div className="mt-4">
                                                <label className="block text-sm font-bold text-red-800 mb-2">حساب المصروف (المدين) *</label>
                                                <select
                                                    required
                                                    className="w-full rounded-xl border-red-200"
                                                    value={data.expense_account_id}
                                                    onChange={e => setData('expense_account_id', e.target.value)}
                                                >
                                                    <option value="">-- اختر الحساب المدين --</option>
                                                    {expenseAccounts.map(acc => (
                                                        <option key={acc.id} value={acc.id}>{acc.code} - {acc.name}</option>
                                                    ))}
                                                </select>
                                                {errors.expense_account_id && <div className="text-red-500 text-sm mt-1">{errors.expense_account_id}</div>}
                                            </div>
                                        )}

                                        {['expense', 'advance', 'petty_cash_issue'].includes(type) && (
                                            <div className="mt-4">
                                                <label className="block text-sm font-bold text-green-800 mb-2">حساب الصرف (الدائن) *</label>
                                                <select
                                                    required
                                                    className="w-full rounded-xl border-green-200"
                                                    value={data.payment_account_id}
                                                    onChange={e => setData('payment_account_id', e.target.value)}
                                                >
                                                    <option value="">-- اختر وسيلة الدفع (الدائن) --</option>
                                                    {paymentMethods.map(acc => (
                                                        <option key={acc.id} value={acc.id}>{acc.code} - {acc.name}</option>
                                                    ))}
                                                </select>
                                                {errors.payment_account_id && <div className="text-red-500 text-sm mt-1">{errors.payment_account_id}</div>}
                                            </div>
                                        )}
                                    </>
                                )}
                            </div>
                            
                            <div className="space-y-4 pt-6 border-t border-gray-100">
                                <h3 className="text-sm font-black text-gray-400 uppercase tracking-widest mb-2 border-b-2 border-gray-100 pb-1 flex items-center gap-2">
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                    معلومات البيان والقيود المحاسبية
                                </h3>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">البيان العام للسند (رأس القيد)</label>
                                    <textarea
                                        required
                                        rows="1"
                                        className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                        value={data.description}
                                        onChange={e => setData('description', e.target.value)}
                                        placeholder="اكتب شرحاً عاماً للسند..."
                                    />
                                    {errors.description && <div className="text-red-500 text-sm mt-1">{errors.description}</div>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">نسخة السند الموقع والمختوم (مرفق)</label>
                                    <input
                                        type="file"
                                        accept=".pdf,image/*"
                                        className="w-full text-sm text-gray-500 file:ml-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                        onChange={e => setData('attachment', e.target.files[0])}
                                    />
                                    {errors.attachment && <div className="text-red-500 text-sm mt-1">{errors.attachment}</div>}
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="p-3 bg-red-50/50 rounded-xl border border-red-100">
                                        <label className="block text-[11px] font-black text-red-700 mb-1 uppercase tracking-wider">بيان سطر المدين / Debit Statement</label>
                                        <input
                                            type="text"
                                            className="w-full rounded-lg border-red-200 text-sm focus:ring-red-500 focus:border-red-500"
                                            value={data.debit_description}
                                            onChange={e => setData('debit_description', e.target.value)}
                                            placeholder="شرح طرف المدين..."
                                        />
                                    </div>
                                    <div className="p-3 bg-green-50/50 rounded-xl border border-green-100">
                                        <label className="block text-[11px] font-black text-green-700 mb-1 uppercase tracking-wider">بيان سطر الدائن / Credit Statement</label>
                                        <input
                                            type="text"
                                            className="w-full rounded-lg border-green-200 text-sm focus:ring-green-500 focus:border-green-500"
                                            value={data.credit_description}
                                            onChange={e => setData('credit_description', e.target.value)}
                                            placeholder="شرح طرف الدائن..."
                                        />
                                    </div>
                                </div>
                            </div>

                            <div className="pt-4 border-t border-gray-100 flex justify-end gap-3">
                                <Link 
                                    href={route('vouchers.index', { type })}
                                    className="px-6 py-3 rounded-xl border border-gray-300 bg-white text-gray-700 font-bold hover:bg-gray-50"
                                >
                                    إلغاء
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className={`${theme.btn} text-white px-8 py-3 rounded-xl font-bold shadow-sm disabled:opacity-50`}
                                >
                                    التوثيق وترحيل القيد
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </>
        </AuthenticatedLayout>
    );
}
