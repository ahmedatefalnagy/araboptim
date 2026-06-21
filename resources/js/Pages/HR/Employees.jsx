import { Head, useForm, Link, usePage, router } from '@inertiajs/react';
import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

const statusColors = { active: 'bg-emerald-100 text-emerald-800', suspended: 'bg-yellow-100 text-yellow-800', terminated: 'bg-red-100 text-red-800' };
const statusLabels = { active: 'نشط', suspended: 'معطل', terminated: 'منتهي الخدمة' };

export default function Employees({ auth, employees, flash }) {
    const [showForm, setShowForm] = useState(false);
    const [editTarget, setEditTarget] = useState(null);
    const [viewTarget, setViewTarget] = useState(null);

    const { default_date, errors: globalErrors } = usePage().props;

    const formatDate = (dateStr) => {
        if (!dateStr) return '';
        return dateStr.substring(0, 10);
    };

    const { data, setData, post, put, processing, errors, reset } = useForm({
        _method: '',
        employee_no: '', name: '', name_en: '', nationality: '', birth_date: '',
        iqama_no: '', operation_card_no: '', driver_card_no: '', transport_license_no: '',
        iqama_expiry: '', license_expiry: '', authorization_expiry: '', work_card_expiry: '', driver_card_expiry: '', transport_license_expiry: '',
        national_id: '', passport_no: '', passport_expiry: '',
        job_title: '', is_driver: false, department: '', hire_date: default_date,
        basic_salary: '', commission: '0', housing_allowance: '', transport_allowance: '', other_allowances: '',
        bank_name: '', account_no: '', iban: '', phone: '', address: '', email: '', status: 'active',
        license_copy: null, iqama_copy: null, document_file: null,
        authorization_copy: null, operation_card_copy: null, driver_card_copy: null, combined_documents_pdf: null,
    });

    const handleEdit = (emp) => {
        setEditTarget(emp.id);
        setShowForm(true);
        setData({ 
            _method: 'put',
            ...emp, 
            birth_date: formatDate(emp.birth_date),
            iqama_expiry: formatDate(emp.iqama_expiry), 
            license_expiry: formatDate(emp.license_expiry),
            authorization_expiry: formatDate(emp.authorization_expiry),
            work_card_expiry: formatDate(emp.work_card_expiry),
            driver_card_expiry: formatDate(emp.driver_card_expiry),
            transport_license_expiry: formatDate(emp.transport_license_expiry),
            passport_expiry: formatDate(emp.passport_expiry),
            is_driver: !!emp.is_driver,
            phone: emp.phone || '',
            address: emp.address || '',
            email: emp.email || '',
            account_no: emp.account_no || '',
            operation_card_no: emp.operation_card_no || '',
            driver_card_no: emp.driver_card_no || '',
            transport_license_no: emp.transport_license_no || '',
            commission: emp.commission || '0',
            license_copy: null,
            iqama_copy: null,
            document_file: null,
            authorization_copy: null,
            operation_card_copy: null,
            driver_card_copy: null,
            combined_documents_pdf: null,
        });
    };

    const submit = (e) => {
        e.preventDefault();
        if (editTarget) {
            post(route('hr.employees.update', editTarget), { 
                forceFormData: true,
                onSuccess: () => { reset(); setShowForm(false); setEditTarget(null); } 
            });
        } else {
            post(route('hr.employees.store'), { 
                forceFormData: true,
                onSuccess: () => { reset(); setShowForm(false); } 
            });
        }
    };

    const totalSalary = (emp) => (parseFloat(emp.basic_salary || 0) + parseFloat(emp.housing_allowance || 0) + parseFloat(emp.transport_allowance || 0) + parseFloat(emp.other_allowances || 0)).toFixed(2);

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl">الموظفين</h2>}>
            <Head title="الموظفين" />
            <div className="py-10 bg-gray-50 min-h-screen" dir="rtl">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="flex justify-between items-center mb-6">
                        <div>
                            <h1 className="text-2xl font-extrabold text-gray-900">سجل الموظفين</h1>
                            <p className="text-sm text-gray-500 mt-1">إدارة بيانات الموظفين والإقامات والرواتب الأساسية</p>
                        </div>
                        <button onClick={() => { setShowForm(!showForm); setEditTarget(null); reset(); }}
                            className="bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-blue-700 shadow-sm flex items-center gap-2">
                            <span className="text-lg">+</span> إضافة موظف
                        </button>
                    </div>

                    {flash?.success && <div className="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl">{flash.success}</div>}
                    {flash?.error && <div className="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">{flash.error}</div>}

                    {globalErrors && Object.keys(globalErrors).length > 0 && (
                        <div className="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
                            <ul className="list-disc list-inside text-xs font-bold">
                                {Object.values(globalErrors).map((err, idx) => <li key={idx}>{err}</li>)}
                            </ul>
                        </div>
                    )}

                    {/* Form */}
                    {showForm && (
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                            <h3 className="text-lg font-bold mb-4 text-gray-800 border-b pb-2">{editTarget ? 'تعديل بيانات موظف' : 'إضافة موظف جديد'}</h3>
                            <form onSubmit={submit}>
                                {/* Group 1: General Info */}
                                <h4 className="text-xs font-black text-blue-700 mb-3 border-b border-blue-50 pb-1">البيانات الشخصية والوظيفية</h4>
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                    <div>
                                        <label className="text-xs font-bold text-gray-600 mb-1 block">الرقم الوظيفي</label>
                                        <input className="w-full rounded-lg border-gray-300 bg-gray-100 text-sm text-gray-500 font-mono" value={data.employee_no || 'يتم التوليد تلقائياً'} readOnly disabled />
                                        <p className="text-[10px] text-gray-400 mt-0.5">توليد تلقائي بواسطة النظام</p>
                                    </div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">الاسم بالعربي *</label><input className="w-full rounded-lg border-gray-300 text-sm" value={data.name} onChange={e => setData('name', e.target.value)} required /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">الجنسية *</label><input className="w-full rounded-lg border-gray-300 text-sm" value={data.nationality} onChange={e => setData('nationality', e.target.value)} required placeholder="مثال: سعودي، مصري..." /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">تاريخ الميلاد</label><input type="date" className="w-full rounded-lg border-gray-300 text-sm" value={data.birth_date} onChange={e => setData('birth_date', e.target.value)} /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">رقم الهاتف</label><input className="w-full rounded-lg border-gray-300 text-sm text-left" dir="ltr" value={data.phone} onChange={e => setData('phone', e.target.value)} placeholder="05xxxxxxxx" /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">البريد الإلكتروني</label><input type="email" className="w-full rounded-lg border-gray-300 text-sm text-left" dir="ltr" value={data.email} onChange={e => setData('email', e.target.value)} placeholder="name@domain.com" /></div>
                                    <div className="md:col-span-2"><label className="text-xs font-bold text-gray-600 mb-1 block">العنوان السكني</label><input className="w-full rounded-lg border-gray-300 text-sm" value={data.address} onChange={e => setData('address', e.target.value)} placeholder="المدينة، الحي، الشارع..." /></div>
                                    <div>
                                        <label className="text-xs font-bold text-gray-600 mb-1 block">المسمى الوظيفي *</label>
                                        <input className="w-full rounded-lg border-gray-300 text-sm mb-2" value={data.job_title} onChange={e => setData('job_title', e.target.value)} required placeholder="مثال: سائق تريلا، محاسب..." />
                                        <div className="flex items-center gap-2">
                                            <input type="checkbox" id="is_driver" className="rounded text-blue-600" checked={data.is_driver} onChange={e => setData('is_driver', e.target.checked)} />
                                            <label htmlFor="is_driver" className="text-xs font-black text-indigo-700">هذا الموظف "سائق" (سيظهر في قسم النقل)</label>
                                        </div>
                                    </div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">تاريخ التعيين *</label><input type="date" className="w-full rounded-lg border-gray-300 text-sm" value={data.hire_date} onChange={e => setData('hire_date', e.target.value)} required /></div>
                                    <div>
                                        <label className="text-xs font-bold text-gray-600 mb-1 block">الحالة</label>
                                        <select className="w-full rounded-lg border-gray-300 text-sm" value={data.status} onChange={e => setData('status', e.target.value)}>
                                            <option value="active">نشط</option>
                                            <option value="suspended">معطل</option>
                                            <option value="terminated">منتهي الخدمة</option>
                                        </select>
                                    </div>
                                </div>

                                {/* Group 2: Financial Details */}
                                <h4 className="text-xs font-black text-blue-700 mb-3 border-b border-blue-50 pb-1">البيانات المالية</h4>
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">الراتب الأساسي (SAR)</label><input type="number" className="w-full rounded-lg border-gray-300 text-sm" value={data.basic_salary} onChange={e => setData('basic_salary', e.target.value)} placeholder="0.00" min="0" step="0.01" /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">العمولة (SAR)</label><input type="number" className="w-full rounded-lg border-gray-300 text-sm" value={data.commission} onChange={e => setData('commission', e.target.value)} placeholder="0.00" min="0" step="0.01" /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">البنك</label><input className="w-full rounded-lg border-gray-300 text-sm" value={data.bank_name} onChange={e => setData('bank_name', e.target.value)} placeholder="اسم البنك..." /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">رقم الحساب</label><input className="w-full rounded-lg border-gray-300 text-sm" value={data.account_no} onChange={e => setData('account_no', e.target.value)} placeholder="رقم الحساب البنكي..." /></div>
                                    <div className="md:col-span-2"><label className="text-xs font-bold text-gray-600 mb-1 block">الآيبان IBAN</label><input className="w-full rounded-lg border-gray-300 text-sm text-left" dir="ltr" value={data.iban} onChange={e => setData('iban', e.target.value)} placeholder="SAxxxxxxxxxxxxxxxxxxxxxxxx" /></div>
                                </div>

                                {/* Group 3: Document Details & Expiry */}
                                <h4 className="text-xs font-black text-blue-700 mb-3 border-b border-blue-50 pb-1">تفاصيل البطاقات والتراخيص والتواريخ</h4>
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">رقم الإقامة</label><input className="w-full rounded-lg border-gray-300 text-sm" value={data.iqama_no} onChange={e => setData('iqama_no', e.target.value)} /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">تاريخ انتهاء الإقامة</label><input type="date" className="w-full rounded-lg border-gray-300 text-sm" value={data.iqama_expiry} onChange={e => setData('iqama_expiry', e.target.value)} /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">رقم كارت التشغيل</label><input className="w-full rounded-lg border-gray-300 text-sm" value={data.operation_card_no} onChange={e => setData('operation_card_no', e.target.value)} /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">رقم بطاقة السائق</label><input className="w-full rounded-lg border-gray-300 text-sm" value={data.driver_card_no} onChange={e => setData('driver_card_no', e.target.value)} /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">رقم ترخيص النقل</label><input className="w-full rounded-lg border-gray-300 text-sm" value={data.transport_license_no} onChange={e => setData('transport_license_no', e.target.value)} /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">تاريخ انتهاء الرخصة</label><input type="date" className="w-full rounded-lg border-gray-300 text-sm" value={data.license_expiry} onChange={e => setData('license_expiry', e.target.value)} /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">تاريخ انتهاء التفويض</label><input type="date" className="w-full rounded-lg border-gray-300 text-sm" value={data.authorization_expiry} onChange={e => setData('authorization_expiry', e.target.value)} /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">تاريخ انتهاء كارت العمل</label><input type="date" className="w-full rounded-lg border-gray-300 text-sm" value={data.work_card_expiry} onChange={e => setData('work_card_expiry', e.target.value)} /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">تاريخ انتهاء بطاقة السائق</label><input type="date" className="w-full rounded-lg border-gray-300 text-sm" value={data.driver_card_expiry} onChange={e => setData('driver_card_expiry', e.target.value)} /></div>
                                    <div><label className="text-xs font-bold text-gray-600 mb-1 block">تاريخ انتهاء ترخيص النقل</label><input type="date" className="w-full rounded-lg border-gray-300 text-sm" value={data.transport_license_expiry} onChange={e => setData('transport_license_expiry', e.target.value)} /></div>
                                </div>

                                {/* Group 4: File Uploads */}
                                <h4 className="text-xs font-black text-blue-700 mb-3 border-b border-blue-50 pb-1">المرفقات والملفات</h4>
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <label className="text-xs font-bold text-gray-600 mb-1 block">صورة الإقامة</label>
                                        <input type="file" className="w-full text-xs" accept="image/*,.pdf" onChange={e => setData('iqama_copy', e.target.files[0])} />
                                    </div>
                                    <div>
                                        <label className="text-xs font-bold text-gray-600 mb-1 block">صورة الرخصة</label>
                                        <input type="file" className="w-full text-xs" accept="image/*,.pdf" onChange={e => setData('license_copy', e.target.files[0])} />
                                    </div>
                                    <div>
                                        <label className="text-xs font-bold text-gray-600 mb-1 block">صورة التفويض</label>
                                        <input type="file" className="w-full text-xs" accept="image/*,.pdf" onChange={e => setData('authorization_copy', e.target.files[0])} />
                                    </div>
                                    <div>
                                        <label className="text-xs font-bold text-gray-600 mb-1 block">صورة كارت التشغيل</label>
                                        <input type="file" className="w-full text-xs" accept="image/*,.pdf" onChange={e => setData('operation_card_copy', e.target.files[0])} />
                                    </div>
                                    <div>
                                        <label className="text-xs font-bold text-gray-600 mb-1 block">صورة بطاقة السائق</label>
                                        <input type="file" className="w-full text-xs" accept="image/*,.pdf" onChange={e => setData('driver_card_copy', e.target.files[0])} />
                                    </div>
                                    <div>
                                        <label className="text-xs font-bold text-gray-600 mb-1 block">ملف بي دي اف مجمع (PDF)</label>
                                        <input type="file" className="w-full text-xs" accept=".pdf" onChange={e => setData('combined_documents_pdf', e.target.files[0])} />
                                    </div>
                                    <div>
                                        <label className="text-xs font-bold text-gray-600 mb-1 block">ملف الموظف العام (PDF)</label>
                                        <input type="file" className="w-full text-xs" accept=".pdf" onChange={e => setData('document_file', e.target.files[0])} />
                                    </div>
                                </div>
                                <div className="flex gap-3 justify-end border-t pt-4">
                                    <button type="button" onClick={() => { setShowForm(false); reset(); setEditTarget(null); }} className="px-5 py-2.5 bg-gray-100 rounded-lg font-bold hover:bg-gray-200">إلغاء</button>
                                    <button type="submit" disabled={processing} className="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 disabled:opacity-50">{editTarget ? 'حفظ التعديلات' : 'إضافة الموظف'}</button>
                                </div>
                            </form>
                        </div>
                    )}

                    {/* Table */}
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <table className="w-full text-right text-sm">
                            <thead className="bg-gray-50 border-b">
                                <tr>
                                    <th className="px-5 py-4 font-semibold text-gray-700">رقم الموظف</th>
                                    <th className="px-5 py-4 font-semibold text-gray-700">الاسم / التواصل</th>
                                    <th className="px-5 py-4 font-semibold text-gray-700">المسمى والمدينة</th>
                                    <th className="px-5 py-4 font-semibold text-gray-700">الوثائق</th>
                                    <th className="px-5 py-4 font-semibold text-gray-700 text-center">إجمالي الراتب</th>
                                    <th className="px-5 py-4 font-semibold text-gray-700 text-center">الحالة</th>
                                    <th className="px-5 py-4 font-semibold text-gray-700 text-center">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {employees.length > 0 ? employees.map(emp => {
                                    return (
                                        <tr key={emp.id} className="hover:bg-gray-50">
                                            <td className="px-5 py-4 font-mono text-gray-600">{emp.employee_no}</td>
                                            <td className="px-5 py-4 font-bold text-gray-900">
                                                <div>{emp.name}</div>
                                                <div className="text-xs text-gray-400 font-normal mt-0.5">{emp.phone || emp.email || '---'}</div>
                                                {emp.is_driver && <span className="mt-1 inline-block text-[9px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded font-black italic">DRV 🚚</span>}
                                            </td>
                                            <td className="px-5 py-4">
                                                <div className="text-gray-800 font-medium">{emp.job_title}</div>
                                                <div className="text-xs text-gray-400 font-normal mt-0.5">{emp.address || '---'}</div>
                                            </td>
                                            <td className="px-5 py-4">
                                                <div className="flex flex-wrap gap-1.5 max-w-xs">
                                                    {emp.iqama_copy && <a href={`/storage/${emp.iqama_copy}`} target="_blank" className="text-[9px] bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded font-black border border-indigo-100 italic">إقامة 📄</a>}
                                                    {emp.license_copy && <a href={`/storage/${emp.license_copy}`} target="_blank" className="text-[9px] bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded font-black border border-emerald-100 italic">رخصة 📄</a>}
                                                    {emp.authorization_copy && <a href={`/storage/${emp.authorization_copy}`} target="_blank" className="text-[9px] bg-sky-50 text-sky-700 px-2 py-0.5 rounded font-black border border-sky-100 italic">تفويض 📄</a>}
                                                    {emp.operation_card_copy && <a href={`/storage/${emp.operation_card_copy}`} target="_blank" className="text-[9px] bg-amber-50 text-amber-700 px-2 py-0.5 rounded font-black border border-amber-100 italic">تشغيل 📄</a>}
                                                    {emp.driver_card_copy && <a href={`/storage/${emp.driver_card_copy}`} target="_blank" className="text-[9px] bg-purple-50 text-purple-700 px-2 py-0.5 rounded font-black border border-purple-100 italic">بطاقة سائق 📄</a>}
                                                    {emp.combined_documents_pdf && <a href={`/storage/${emp.combined_documents_pdf}`} target="_blank" className="text-[9px] bg-orange-50 text-orange-700 px-2 py-0.5 rounded font-black border border-orange-100 italic">ملف مجمع 📂</a>}
                                                    {emp.document_file && <a href={`/storage/${emp.document_file}`} target="_blank" className="text-[9px] bg-rose-50 text-rose-700 px-2 py-0.5 rounded font-black border border-rose-100 italic">ملف عام 📂</a>}
                                                </div>
                                            </td>
                                            <td className="px-5 py-4 text-center font-mono font-bold">
                                                <div className="text-blue-700">{totalSalary(emp)} ر.س</div>
                                                {parseFloat(emp.commission || 0) > 0 && <div className="text-[10px] text-gray-500 font-normal">عمولة: {emp.commission} ر.س</div>}
                                            </td>
                                            <td className="px-5 py-4 text-center">
                                                <span className={`text-xs font-bold px-3 py-1 rounded-full ${statusColors[emp.status]}`}>{statusLabels[emp.status]}</span>
                                            </td>
                                            <td className="px-5 py-4 text-center">
                                                <div className="flex gap-3 justify-center items-center">
                                                    <button onClick={() => setViewTarget(emp)} className="text-emerald-600 hover:text-emerald-800 hover:underline text-xs font-bold">عرض</button>
                                                    <span className="text-gray-300">|</span>
                                                    <button onClick={() => handleEdit(emp)} className="text-blue-600 hover:text-blue-800 hover:underline text-xs font-bold">تعديل</button>
                                                    <span className="text-gray-300">|</span>
                                                    <button onClick={() => {
                                                        router.post(route('hr.employees.toggle-status', emp.id));
                                                    }} className="text-amber-600 hover:text-amber-800 hover:underline text-xs font-bold">
                                                        {emp.status === 'active' ? 'تعطيل' : 'تنشيط'}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                }) : (
                                    <tr><td colSpan="7" className="py-12 text-center text-gray-400 font-bold">لا يوجد موظفين مسجلين حالياً.</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* View Details Modal */}
            {viewTarget && (
                <div className="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm flex justify-center items-center z-50 p-4 transition-all duration-300" dir="rtl">
                    <div className="bg-white rounded-2xl shadow-2xl border border-gray-100 max-w-4xl w-full max-h-[90vh] overflow-y-auto transform scale-100 transition-all duration-300">
                        {/* Modal Header */}
                        <div className="flex justify-between items-center p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-t-2xl">
                            <div>
                                <h3 className="text-xl font-extrabold text-gray-950 flex items-center gap-2">
                                    <span className="text-blue-600 font-mono">[{viewTarget.employee_no}]</span>
                                    <span>{viewTarget.name}</span>
                                </h3>
                                {viewTarget.name_en && (
                                    <p className="text-sm text-gray-500 mt-1 font-mono text-right">{viewTarget.name_en}</p>
                                )}
                            </div>
                            <button onClick={() => setViewTarget(null)} className="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-full">
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {/* Modal Body */}
                        <div className="p-6 space-y-8">
                            {/* 1. General Info Section */}
                            <div>
                                <h4 className="text-sm font-black text-blue-800 border-r-4 border-blue-600 pr-2 mb-4 flex items-center gap-2 bg-blue-50/50 py-1.5 rounded-l">البيانات الشخصية والوظيفية</h4>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">المسمى الوظيفي</span>
                                        <span className="text-sm font-bold text-gray-800">{viewTarget.job_title}</span>
                                        {viewTarget.is_driver ? (
                                            <span className="mr-2 inline-block text-[9px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded font-black italic">سائق 🚚</span>
                                        ) : null}
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">الجنسية</span>
                                        <span className="text-sm font-bold text-gray-800">{viewTarget.nationality}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">الحالة</span>
                                        <span className={`text-xs font-bold px-2.5 py-1 rounded-full inline-block ${statusColors[viewTarget.status]}`}>
                                            {statusLabels[viewTarget.status]}
                                        </span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">تاريخ الميلاد</span>
                                        <span className="text-sm font-mono font-bold text-gray-800">{formatDate(viewTarget.birth_date) || '---'}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">تاريخ التعيين</span>
                                        <span className="text-sm font-mono font-bold text-gray-800">{formatDate(viewTarget.hire_date) || '---'}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">القسم</span>
                                        <span className="text-sm font-bold text-gray-800">{viewTarget.department || '---'}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">رقم الجوال</span>
                                        <span className="text-sm font-mono font-bold text-gray-800 text-left block" dir="ltr">{viewTarget.phone || '---'}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">البريد الإلكتروني</span>
                                        <span className="text-sm font-mono font-bold text-gray-800 text-left block" dir="ltr">{viewTarget.email || '---'}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">العنوان السكني</span>
                                        <span className="text-sm font-bold text-gray-800">{viewTarget.address || '---'}</span>
                                    </div>
                                </div>
                            </div>

                            {/* 2. Financial Info Section */}
                            <div>
                                <h4 className="text-sm font-black text-emerald-800 border-r-4 border-emerald-600 pr-2 mb-4 flex items-center gap-2 bg-emerald-50/50 py-1.5 rounded-l">البيانات المالية والحسابات البنكية</h4>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div className="bg-emerald-50/30 p-3.5 rounded-xl border border-emerald-100">
                                        <span className="text-xs text-emerald-600 block mb-1">الراتب الأساسي</span>
                                        <span className="text-base font-mono font-extrabold text-emerald-800">
                                            {viewTarget.basic_salary ? `${parseFloat(viewTarget.basic_salary).toFixed(2)} ر.س` : '0.00 ر.س'}
                                        </span>
                                    </div>
                                    <div className="bg-emerald-50/30 p-3.5 rounded-xl border border-emerald-100">
                                        <span className="text-xs text-emerald-600 block mb-1">العمولة</span>
                                        <span className="text-base font-mono font-extrabold text-emerald-800">
                                            {viewTarget.commission ? `${parseFloat(viewTarget.commission).toFixed(2)} ر.س` : '0.00 ر.س'}
                                        </span>
                                    </div>
                                    <div className="bg-emerald-50/30 p-3.5 rounded-xl border border-emerald-100">
                                        <span className="text-xs text-emerald-600 block mb-1">إجمالي الراتب (مع البدلات)</span>
                                        <span className="text-base font-mono font-extrabold text-blue-800">{totalSalary(viewTarget)} ر.س</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">بدل السكن</span>
                                        <span className="text-sm font-mono font-bold text-gray-800">
                                            {viewTarget.housing_allowance ? `${parseFloat(viewTarget.housing_allowance).toFixed(2)} ر.س` : '0.00 ر.س'}
                                        </span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">بدل النقل</span>
                                        <span className="text-sm font-mono font-bold text-gray-800">
                                            {viewTarget.transport_allowance ? `${parseFloat(viewTarget.transport_allowance).toFixed(2)} ر.س` : '0.00 ر.س'}
                                        </span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">بدلات أخرى</span>
                                        <span className="text-sm font-mono font-bold text-gray-800">
                                            {viewTarget.other_allowances ? `${parseFloat(viewTarget.other_allowances).toFixed(2)} ر.س` : '0.00 ر.س'}
                                        </span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">البنك</span>
                                        <span className="text-sm font-bold text-gray-800">{viewTarget.bank_name || '---'}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">رقم الحساب</span>
                                        <span className="text-sm font-mono font-bold text-gray-800">{viewTarget.account_no || '---'}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">الآيبان IBAN</span>
                                        <span className="text-sm font-mono font-bold text-gray-800 text-left block" dir="ltr">{viewTarget.iban || '---'}</span>
                                    </div>
                                </div>
                            </div>

                            {/* 3. Cards & Documents expiry */}
                            <div>
                                <h4 className="text-sm font-black text-amber-800 border-r-4 border-amber-600 pr-2 mb-4 flex items-center gap-2 bg-amber-50/50 py-1.5 rounded-l">البطاقات والتراخيص وتواريخ الانتهاء</h4>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">رقم الإقامة</span>
                                        <span className="text-sm font-bold text-gray-800">{viewTarget.iqama_no || '---'}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">تاريخ انتهاء الإقامة</span>
                                        <span className="text-sm font-mono font-bold text-gray-800">{formatDate(viewTarget.iqama_expiry) || '---'}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">رقم كارت التشغيل</span>
                                        <span className="text-sm font-bold text-gray-800">{viewTarget.operation_card_no || '---'}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">رقم بطاقة السائق</span>
                                        <span className="text-sm font-bold text-gray-800">{viewTarget.driver_card_no || '---'}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">رقم ترخيص النقل</span>
                                        <span className="text-sm font-bold text-gray-800">{viewTarget.transport_license_no || '---'}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">تاريخ انتهاء الرخصة</span>
                                        <span className="text-sm font-mono font-bold text-gray-800">{formatDate(viewTarget.license_expiry) || '---'}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">تاريخ انتهاء التفويض</span>
                                        <span className="text-sm font-mono font-bold text-gray-800">{formatDate(viewTarget.authorization_expiry) || '---'}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">تاريخ انتهاء كارت العمل</span>
                                        <span className="text-sm font-mono font-bold text-gray-800">{formatDate(viewTarget.work_card_expiry) || '---'}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">تاريخ انتهاء بطاقة السائق</span>
                                        <span className="text-sm font-mono font-bold text-gray-800">{formatDate(viewTarget.driver_card_expiry) || '---'}</span>
                                    </div>
                                    <div className="bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                                        <span className="text-xs text-gray-400 block mb-1">تاريخ انتهاء ترخيص النقل</span>
                                        <span className="text-sm font-mono font-bold text-gray-800">{formatDate(viewTarget.transport_license_expiry) || '---'}</span>
                                    </div>
                                </div>
                            </div>

                            {/* 4. Document File Preview and Downloads */}
                            <div>
                                <h4 className="text-sm font-black text-indigo-800 border-r-4 border-indigo-600 pr-2 mb-4 flex items-center gap-2 bg-indigo-50/50 py-1.5 rounded-l">المرفقات والملفات المرفوعة للتحميل أو العرض</h4>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {[
                                        { label: 'صورة الإقامة', file: viewTarget.iqama_copy },
                                        { label: 'صورة الرخصة', file: viewTarget.license_copy },
                                        { label: 'صورة التفويض', file: viewTarget.authorization_copy },
                                        { label: 'صورة كارت التشغيل', file: viewTarget.operation_card_copy },
                                        { label: 'صورة بطاقة السائق', file: viewTarget.driver_card_copy },
                                        { label: 'ملف بي دي اف مجمع (PDF)', file: viewTarget.combined_documents_pdf },
                                        { label: 'ملف الموظف العام (PDF)', file: viewTarget.document_file }
                                    ].map((doc, idx) => (
                                        <div key={idx} className="flex justify-between items-center bg-gray-50 p-4 rounded-xl border border-gray-100 hover:bg-gray-100 transition-colors">
                                            <div>
                                                <span className="text-sm font-bold text-gray-800 block">{doc.label}</span>
                                                <span className="text-[10px] text-gray-400 font-mono mt-0.5 max-w-[200px] truncate block">
                                                    {doc.file ? doc.file.split('/').pop() : 'لا يوجد ملف مرفوع'}
                                                </span>
                                            </div>
                                            {doc.file ? (
                                                <div className="flex gap-2">
                                                    <a href={`/storage/${doc.file}`} target="_blank" rel="noopener noreferrer" className="bg-blue-50 text-blue-700 border border-blue-100 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-blue-100 transition-all flex items-center gap-1 shadow-sm">
                                                        <span>عرض</span>
                                                    </a>
                                                    <a href={`/storage/${doc.file}`} download className="bg-emerald-50 text-emerald-700 border border-emerald-100 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-emerald-100 transition-all flex items-center gap-1 shadow-sm">
                                                        <span>تحميل</span>
                                                    </a>
                                                </div>
                                            ) : (
                                                <span className="text-xs text-gray-400 italic">غير متوفر</span>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        {/* Modal Footer */}
                        <div className="p-6 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3 rounded-b-2xl">
                            <button onClick={() => { setViewTarget(null); handleEdit(viewTarget); }} className="px-5 py-2.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition-colors shadow-sm text-sm">
                                تعديل بيانات الموظف
                            </button>
                            <button onClick={() => setViewTarget(null)} className="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-300 transition-colors text-sm">
                                إغلاق
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
