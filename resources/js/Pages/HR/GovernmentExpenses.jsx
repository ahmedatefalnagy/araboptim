import { Head, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

const typeLabels = { iqama_renewal: 'تجديد إقامة', work_permit: 'تصريح عمل', insurance: 'تأمين طبي/تجاري', exit_reentry: 'تأشيرة خروج وعودة', other: 'أخرى' };
const typeIcons = { iqama_renewal: '🪪', work_permit: '📋', insurance: '🏥', exit_reentry: '✈️', other: '💼' };

export default function GovernmentExpenses({ auth, expenses, employees, cashAccounts, flash }) {
    const [showForm, setShowForm] = useState(false);
    const { default_date } = usePage().props;
    const { data, setData, post, processing, errors, reset } = useForm({
        employee_id: '', type: 'iqama_renewal',
        reference_no: '', expense_date: default_date,
        expiry_date: '', amount: '', provider: '', notes: '',
        payment_account_id: ''
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('hr.government-expenses.store'), { onSuccess: () => { reset(); setShowForm(false); } });
    };

    const totalAmount = expenses.reduce((s, e) => s + parseFloat(e.amount || 0), 0);

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl">المصروفات الحكومية</h2>}>
            <Head title="المصروفات الحكومية" />
            <div className="py-10 bg-gray-50 min-h-screen" dir="rtl">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Summary Cards */}
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        {Object.entries(typeLabels).map(([key, label]) => {
                            const count = expenses.filter(e => e.type === key).length;
                            const total = expenses.filter(e => e.type === key).reduce((s, e) => s + parseFloat(e.amount || 0), 0);
                            return (
                                <div key={key} className="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                                    <div className="text-2xl mb-1">{typeIcons[key]}</div>
                                    <div className="text-xs text-gray-500 font-semibold">{label}</div>
                                    <div className="text-lg font-black text-gray-800 mt-1">{total.toFixed(2)} <span className="text-xs font-normal text-gray-400">ر.س</span></div>
                                    <div className="text-xs text-gray-400">{count} سجل</div>
                                </div>
                            );
                        })}
                    </div>

                    <div className="flex justify-between items-center mb-6">
                        <div>
                            <h1 className="text-2xl font-extrabold text-gray-900">المصروفات الحكومية والنظامية</h1>
                            <p className="text-sm text-gray-500 mt-1">تجديد إقامات، تأمين، تصاريح عمل، وغيرها — الإجمالي: <span className="font-black text-blue-700">{totalAmount.toFixed(2)} ر.س</span></p>
                        </div>
                        <button onClick={() => setShowForm(!showForm)} className="bg-purple-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-purple-700 flex items-center gap-2 shadow-sm">
                            <span>+</span> تسجيل مصروف
                        </button>
                    </div>

                    {flash?.success && <div className="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl">{flash.success}</div>}

                    {showForm && (
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                            <h3 className="text-lg font-bold mb-4 border-b pb-2">تسجيل مصروف حكومي</h3>
                            <form onSubmit={submit}>
                                <div className="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                                    <div><label className="text-xs font-bold mb-1 block">نوع المصروف *</label>
                                        <select className="w-full rounded-lg border-gray-300 text-sm" value={data.type} onChange={e => setData('type', e.target.value)}>
                                            {Object.entries(typeLabels).map(([k, v]) => <option key={k} value={k}>{typeIcons[k]} {v}</option>)}
                                        </select></div>
                                    <div><label className="text-xs font-bold mb-1 block">الموظف (إن وجد)</label>
                                        <select className="w-full rounded-lg border-gray-300 text-sm" value={data.employee_id} onChange={e => setData('employee_id', e.target.value)}>
                                            <option value="">-- غير مرتبط بموظف --</option>
                                            {employees.map(e => <option key={e.id} value={e.id}>[{e.employee_no}] {e.name}</option>)}
                                        </select></div>
                                    <div><label className="text-xs font-bold mb-1 block">الجهة / مزود الخدمة</label><input className="w-full rounded-lg border-gray-300 text-sm" value={data.provider} onChange={e => setData('provider', e.target.value)} placeholder="مثال: شركة بوبا، وزارة الداخلية" /></div>
                                    <div><label className="text-xs font-bold mb-1 block">رقم المرجع</label><input className="w-full rounded-lg border-gray-300 text-sm font-mono" value={data.reference_no} onChange={e => setData('reference_no', e.target.value)} /></div>
                                    <div><label className="text-xs font-bold mb-1 block">تاريخ الصرف *</label><input type="date" className="w-full rounded-lg border-gray-300 text-sm" value={data.expense_date} onChange={e => setData('expense_date', e.target.value)} required /></div>
                                    <div><label className="text-xs font-bold mb-1 block">تاريخ الانتهاء (الوثيقة)</label><input type="date" className="w-full rounded-lg border-gray-300 text-sm" value={data.expiry_date} onChange={e => setData('expiry_date', e.target.value)} /></div>
                                    <div><label className="text-xs font-bold mb-1 block">المبلغ (SAR) *</label><input type="number" className="w-full rounded-lg border-gray-300 text-sm" value={data.amount} onChange={e => setData('amount', e.target.value)} required min="0" /></div>
                                    <div><label className="text-xs font-bold mb-1 block text-emerald-600">حساب الصرف (بنك/خزينة/عهدة) *</label>
                                        <select className={`w-full rounded-lg border-gray-300 text-sm ${errors.payment_account_id ? 'border-red-500' : ''}`} value={data.payment_account_id} onChange={e => setData('payment_account_id', e.target.value)} required>
                                            <option value="">-- اختر حساب الدفع --</option>
                                            {cashAccounts.map(acc => <option key={acc.id} value={acc.id}>{acc.code} - {acc.name}</option>)}
                                        </select>
                                        {errors.payment_account_id && <div className="text-red-500 text-[10px] mt-1">{errors.payment_account_id}</div>}
                                    </div>
                                    <div className="md:col-span-2"><label className="text-xs font-bold mb-1 block">ملاحظات</label><input className="w-full rounded-lg border-gray-300 text-sm" value={data.notes} onChange={e => setData('notes', e.target.value)} /></div>
                                </div>
                                <div className="flex gap-3 justify-end border-t pt-4">
                                    <button type="button" onClick={() => { setShowForm(false); reset(); }} className="px-5 py-2 bg-gray-100 rounded-lg font-bold">إلغاء</button>
                                    <button type="submit" disabled={processing} className="px-6 py-2 bg-purple-600 text-white rounded-lg font-bold hover:bg-purple-700 disabled:opacity-50">حفظ</button>
                                </div>
                            </form>
                        </div>
                    )}

                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <table className="w-full text-right text-sm">
                            <thead className="bg-gray-50 border-b">
                                <tr>
                                    <th className="px-5 py-4 font-semibold">النوع</th>
                                    <th className="px-5 py-4 font-semibold">الموظف</th>
                                    <th className="px-5 py-4 font-semibold">الجهة</th>
                                    <th className="px-5 py-4 font-semibold">تاريخ الصرف</th>
                                    <th className="px-5 py-4 font-semibold">حساب الصرف</th>
                                    <th className="px-5 py-4 font-semibold">الانتهاء</th>
                                    <th className="px-5 py-4 font-semibold text-center">المبلغ</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {expenses.length > 0 ? expenses.map(exp => {
                                    const expiry = exp.expiry_date ? new Date(exp.expiry_date) : null;
                                    const daysLeft = expiry ? Math.ceil((expiry - new Date()) / (1000 * 60 * 60 * 24)) : null;
                                    return (
                                        <tr key={exp.id} className="hover:bg-gray-50">
                                            <td className="px-5 py-4">{typeIcons[exp.type]} <span className="font-semibold">{typeLabels[exp.type]}</span></td>
                                            <td className="px-5 py-4 font-bold">{exp.employee?.name || '—'}</td>
                                            <td className="px-5 py-4 text-gray-600">{exp.provider || '—'}</td>
                                            <td className="px-5 py-4 text-gray-600">{exp.expense_date}</td>
                                            <td className="px-5 py-4 text-xs font-bold text-emerald-600">{exp.payment_account?.name || '—'}</td>
                                            <td className="px-5 py-4">
                                                {daysLeft !== null ? (
                                                    <span className={`text-xs font-bold px-2 py-1 rounded ${daysLeft <= 30 ? 'bg-red-100 text-red-700' : daysLeft <= 90 ? 'bg-yellow-100 text-yellow-700' : 'text-gray-500'}`}>
                                                        {exp.expiry_date} {daysLeft <= 90 && `(${daysLeft} يوم)`}
                                                    </span>
                                                ) : '—'}
                                            </td>
                                            <td className="px-5 py-4 text-center font-mono font-black text-purple-700">{parseFloat(exp.amount).toFixed(2)} ر.س</td>
                                        </tr>
                                    );
                                }) : (
                                    <tr><td colSpan="6" className="py-12 text-center text-gray-400 font-bold">لا يوجد مصروفات حكومية.</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
