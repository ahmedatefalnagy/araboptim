import { Head, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

const statusColors = { draft: 'bg-gray-100 text-gray-700', approved: 'bg-blue-100 text-blue-800', paid: 'bg-emerald-100 text-emerald-800' };
const statusLabels = { draft: 'مسودة', approved: 'معتمد', paid: 'مصروف' };

export default function Payroll({ auth, payrolls, employees, paymentAccounts, flash }) {
    const [showForm, setShowForm] = useState(false);
    const { default_date } = usePage().props;
    const { data, setData, post, processing, errors, reset } = useForm({
        employee_id: '', month: default_date.slice(0, 7),
        payment_date: default_date,
        basic_salary: '', housing_allowance: '', transport_allowance: '',
        other_allowances: '', overtime_amount: 0,
        gosi_employee: 0, gosi_employer: 0,
        advance_deduction: 0, other_deductions: 0, 
        payment_account_id: '',
        notes: ''
    });

    const handleEmployeeChange = (empId) => {
        setData('employee_id', empId);
        const emp = employees.find(e => e.id == empId);
        if (emp) {
            setData(d => ({ ...d, employee_id: empId, basic_salary: emp.basic_salary, housing_allowance: emp.housing_allowance, transport_allowance: emp.transport_allowance, other_allowances: emp.other_allowances }));
        }
    };

    const gross = [data.basic_salary, data.housing_allowance, data.transport_allowance, data.other_allowances, data.overtime_amount].reduce((a, b) => a + parseFloat(b || 0), 0);
    const deductions = [data.gosi_employee, data.advance_deduction, data.other_deductions].reduce((a, b) => a + parseFloat(b || 0), 0);
    const net = gross - deductions;

    const submit = (e) => {
        e.preventDefault();
        post(route('hr.payroll.store'), { onSuccess: () => { reset(); setShowForm(false); } });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl">الرواتب</h2>}>
            <Head title="مسير الرواتب" />
            <div className="py-10 bg-gray-50 min-h-screen" dir="rtl">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center mb-6">
                        <div><h1 className="text-2xl font-extrabold text-gray-900">مسير الرواتب</h1><p className="text-sm text-gray-500 mt-1">إصدار وإدارة رواتب الموظفين مع حساب التأمينات والخصومات تلقائياً</p></div>
                        <button onClick={() => setShowForm(!showForm)} className="bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-emerald-700 flex items-center gap-2 shadow-sm">
                            <span>+</span> إصدار راتب
                        </button>
                    </div>

                    {flash?.success && <div className="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl">{flash.success}</div>}

                    {showForm && (
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                            <h3 className="text-lg font-bold mb-4 border-b pb-2">إصدار كشف راتب</h3>
                            <form onSubmit={submit}>
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                    <div className="md:col-span-2"><label className="text-xs font-bold text-gray-600 mb-1 block">الموظف *</label>
                                        <select className="w-full rounded-lg border-gray-300 text-sm" value={data.employee_id} onChange={e => handleEmployeeChange(e.target.value)} required>
                                            <option value="">-- اختر الموظف --</option>
                                            {employees.map(e => <option key={e.id} value={e.id}>[{e.employee_no}] {e.name}</option>)}
                                        </select></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">الشهر *</label><input type="month" className="w-full rounded-lg border-gray-300 text-sm" value={data.month} onChange={e => setData('month', e.target.value)} required /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">تاريخ الصرف *</label><input type="date" className="w-full rounded-lg border-gray-300 text-sm" value={data.payment_date} onChange={e => setData('payment_date', e.target.value)} required /></div>
                                    <div className="md:col-span-2">
                                        <label className="text-xs font-bold text-gray-600 mb-1 block">حساب الصرف *</label>
                                        <select className="w-full rounded-lg border-gray-300 text-sm" value={data.payment_account_id} onChange={e => setData('payment_account_id', e.target.value)} required>
                                            <option value="">-- اختر حساب الصرف (البنك / الصندوق) --</option>
                                            {paymentAccounts.map(acc => <option key={acc.id} value={acc.id}>[{acc.code}] {acc.name}</option>)}
                                        </select>
                                        {errors.payment_account_id && <p className="text-red-500 text-xs mt-1">{errors.payment_account_id}</p>}
                                    </div>
                                </div>
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-3 bg-blue-50 p-4 rounded-xl mb-4">
                                    <p className="col-span-full text-xs font-bold text-blue-700 mb-1">المستحقات</p>
                                    <div><label className="text-xs text-gray-600 mb-1 block">الراتب الأساسي</label><input type="number" className="w-full rounded-lg border-gray-300 text-sm" value={data.basic_salary} onChange={e => setData('basic_salary', e.target.value)} min="0" /></div>
                                    <div><label className="text-xs text-gray-600 mb-1 block">بدل سكن</label><input type="number" className="w-full rounded-lg border-gray-300 text-sm" value={data.housing_allowance} onChange={e => setData('housing_allowance', e.target.value)} min="0" /></div>
                                    <div><label className="text-xs text-gray-600 mb-1 block">بدل مواصلات</label><input type="number" className="w-full rounded-lg border-gray-300 text-sm" value={data.transport_allowance} onChange={e => setData('transport_allowance', e.target.value)} min="0" /></div>
                                    <div><label className="text-xs text-gray-600 mb-1 block">بدلات أخرى</label><input type="number" className="w-full rounded-lg border-gray-300 text-sm" value={data.other_allowances} onChange={e => setData('other_allowances', e.target.value)} min="0" /></div>
                                    <div><label className="text-xs text-gray-600 mb-1 block">إضافي / أوفرتايم</label><input type="number" className="w-full rounded-lg border-gray-300 text-sm" value={data.overtime_amount} onChange={e => setData('overtime_amount', e.target.value)} min="0" /></div>
                                    <div className="col-span-1 flex items-end"><div className="p-3 bg-blue-100 rounded-lg w-full text-center"><p className="text-xs text-blue-600">الإجمالي قبل الخصم</p><p className="text-lg font-black text-blue-800">{gross.toFixed(2)} ر.س</p></div></div>
                                </div>
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-3 bg-red-50 p-4 rounded-xl mb-4">
                                    <p className="col-span-full text-xs font-bold text-red-700 mb-1">الخصومات</p>
                                    <div><label className="text-xs text-gray-600 mb-1 block">تأمينات الموظف (9%)</label><input type="number" className="w-full rounded-lg border-gray-300 text-sm" value={data.gosi_employee} onChange={e => setData('gosi_employee', e.target.value)} min="0" /></div>
                                    <div><label className="text-xs text-gray-600 mb-1 block">تأمينات صاحب العمل</label><input type="number" className="w-full rounded-lg border-gray-300 text-sm" value={data.gosi_employer} onChange={e => setData('gosi_employer', e.target.value)} min="0" /></div>
                                    <div><label className="text-xs text-gray-600 mb-1 block">خصم سلفة</label><input type="number" className="w-full rounded-lg border-gray-300 text-sm" value={data.advance_deduction} onChange={e => setData('advance_deduction', e.target.value)} min="0" /></div>
                                    <div><label className="text-xs text-gray-600 mb-1 block">خصومات أخرى</label><input type="number" className="w-full rounded-lg border-gray-300 text-sm" value={data.other_deductions} onChange={e => setData('other_deductions', e.target.value)} min="0" /></div>
                                </div>
                                <div className="flex items-center justify-between bg-emerald-50 px-6 py-4 rounded-xl mb-4 gap-4">
                                    <div className="flex-1">
                                        <label className="text-xs font-bold text-gray-600 mb-1 block">البيان / الوصف (يظهر في القيد المحاسبي)</label>
                                        <input className="w-full rounded-lg border-gray-300 text-sm" value={data.notes} onChange={e => setData('notes', e.target.value)} placeholder="مثلاً: راتب شهر أبريل 2025" />
                                    </div>
                                    <div className="text-right">
                                        <span className="text-lg font-bold text-gray-700 block">صافي الراتب المستحق:</span>
                                        <span className="text-3xl font-black text-emerald-700">{net.toFixed(2)} ر.س</span>
                                    </div>
                                </div>
                                <div className="flex gap-3 justify-end border-t pt-4">
                                    <button type="button" onClick={() => { setShowForm(false); reset(); }} className="px-5 py-2 bg-gray-100 rounded-lg font-bold">إلغاء</button>
                                    <button type="submit" disabled={processing} className="px-6 py-2 bg-emerald-600 text-white rounded-lg font-bold hover:bg-emerald-700 disabled:opacity-50">إصدار</button>
                                </div>
                            </form>
                        </div>
                    )}

                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <table className="w-full text-right text-sm">
                            <thead className="bg-gray-50 border-b">
                                <tr>
                                    <th className="px-5 py-4 font-semibold">الموظف</th>
                                    <th className="px-5 py-4 font-semibold">الشهر</th>
                                    <th className="px-5 py-4 font-semibold text-center">الإجمالي</th>
                                    <th className="px-5 py-4 font-semibold text-center">خصومات</th>
                                    <th className="px-5 py-4 font-semibold text-center">الصافي</th>
                                    <th className="px-5 py-4 font-semibold text-center">الحالة</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {payrolls.length > 0 ? payrolls.map(p => (
                                    <tr key={p.id} className="hover:bg-gray-50">
                                        <td className="px-5 py-4 font-bold">{p.employee?.name}</td>
                                        <td className="px-5 py-4 text-gray-600">{p.month}</td>
                                        <td className="px-5 py-4 text-center font-mono">{parseFloat(p.gross_salary).toFixed(2)}</td>
                                        <td className="px-5 py-4 text-center font-mono text-red-600">{(parseFloat(p.gosi_employee) + parseFloat(p.advance_deduction) + parseFloat(p.other_deductions)).toFixed(2)}</td>
                                        <td className="px-5 py-4 text-center font-mono font-black text-emerald-700">{parseFloat(p.net_salary).toFixed(2)}</td>
                                        <td className="px-5 py-4 text-center"><span className={`text-xs font-bold px-3 py-1 rounded-full ${statusColors[p.status]}`}>{statusLabels[p.status]}</span></td>
                                    </tr>
                                )) : (
                                    <tr><td colSpan="6" className="py-12 text-center text-gray-400 font-bold">لا يوجد كشوف رواتب.</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
