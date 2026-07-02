import React, { useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, warehouses }) {
    const { flash } = usePage().props;
    const [editingWarehouse, setEditingWarehouse] = useState(null);

    const { data, setData, post, put, delete: destroy, processing, reset, errors } = useForm({
        name: '',
        code: '',
        location: '',
        is_active: true,
    });

    const submit = (e) => {
        e.preventDefault();
        if (editingWarehouse) {
            put(route('warehouses.update', editingWarehouse.id), {
                onSuccess: () => {
                    setEditingWarehouse(null);
                    reset();
                }
            });
        } else {
            post(route('warehouses.store'), {
                onSuccess: () => reset()
            });
        }
    };

    const handleEdit = (wh) => {
        setEditingWarehouse(wh);
        setData({
            name: wh.name,
            code: wh.code,
            location: wh.location || '',
            is_active: !!wh.is_active,
        });
    };

    const handleCancelEdit = () => {
        setEditingWarehouse(null);
        reset();
    };

    const handleDelete = (id) => {
        if (confirm('هل أنت متأكد من رغبتك في حذف هذا المستودع؟')) {
            destroy(route('warehouses.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">إدارة المستودعات</h2>}
        >
            <Head title="المستودعات" />

            <div className="py-12 bg-gray-50/50 min-h-screen" dir="rtl">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    
                    {/* Header Section */}
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold tracking-tight text-gray-900 bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                            المستودعات والمخازن
                        </h1>
                        <p className="mt-2 text-sm text-gray-600">
                            تهيئة وإدارة المستودعات لتقسيم المخزون ومراقبة حركات الوارد والمنصرف والتحويلات.
                        </p>
                    </div>

                    {flash?.message && (
                        <div className="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center shadow-sm">
                            <svg className="h-5 w-5 text-emerald-400 ml-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                            </svg>
                            <p className="text-sm font-medium text-emerald-800">{flash.message}</p>
                        </div>
                    )}

                    {flash?.error && (
                        <div className="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-100 flex items-center shadow-sm">
                            <svg className="h-5 w-5 text-rose-400 ml-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                            </svg>
                            <p className="text-sm font-medium text-rose-800">{flash.error}</p>
                        </div>
                    )}

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {/* Form Column (1 Col) */}
                        <div className="lg:col-span-1">
                            <form onSubmit={submit} className="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden sticky top-6">
                                <div className="p-6">
                                    <h3 className="text-xl font-bold text-gray-900 mb-6">
                                        {editingWarehouse ? 'تعديل بيانات المستودع' : 'إضافة مستودع جديد'}
                                    </h3>

                                    <div className="space-y-5">
                                        <div>
                                            <label className="block text-sm font-semibold text-gray-800 mb-2">اسم المستودع <span className="text-red-500">*</span></label>
                                            <input
                                                type="text"
                                                required
                                                className="w-full rounded-xl border-gray-200 shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                                                value={data.name}
                                                onChange={e => setData('name', e.target.value)}
                                                placeholder="مثال: المستودع الرئيسي، مخزن جدة"
                                            />
                                            {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
                                        </div>

                                        <div>
                                            <label className="block text-sm font-semibold text-gray-800 mb-2">رمز المستودع (Code) <span className="text-red-500">*</span></label>
                                            <input
                                                type="text"
                                                required
                                                className="w-full rounded-xl border-gray-200 shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono"
                                                value={data.code}
                                                onChange={e => setData('code', e.target.value)}
                                                placeholder="مثال: MAIN, WH-JED"
                                            />
                                            {errors.code && <p className="text-red-500 text-xs mt-1">{errors.code}</p>}
                                        </div>

                                        <div>
                                            <label className="block text-sm font-semibold text-gray-800 mb-2">العنوان / الموقع</label>
                                            <input
                                                type="text"
                                                className="w-full rounded-xl border-gray-200 shadow-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                                                value={data.location}
                                                onChange={e => setData('location', e.target.value)}
                                                placeholder="مثال: حي الشفاء - الرياض"
                                            />
                                            {errors.location && <p className="text-red-500 text-xs mt-1">{errors.location}</p>}
                                        </div>

                                        <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                                            <input
                                                id="is_active"
                                                type="checkbox"
                                                className="h-4.5 w-4.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                checked={data.is_active}
                                                onChange={e => setData('is_active', e.target.checked)}
                                            />
                                            <label htmlFor="is_active" className="text-sm font-semibold text-gray-700 select-none cursor-pointer">المستودع نشط ومتاح للاستخدام</label>
                                        </div>
                                    </div>
                                </div>

                                <div className="p-6 bg-gray-50 border-t border-gray-100 flex gap-3">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:shadow-lg hover:shadow-blue-500/20 text-white py-2.5 rounded-xl font-bold transition-all disabled:opacity-50 text-sm"
                                    >
                                        {processing ? 'جاري الحفظ...' : (editingWarehouse ? 'حفظ التعديلات' : 'إضافة المستودع')}
                                    </button>
                                    {editingWarehouse && (
                                        <button
                                            type="button"
                                            onClick={handleCancelEdit}
                                            className="px-4 py-2.5 border border-gray-200 text-gray-600 hover:bg-gray-100 rounded-xl font-bold transition-all text-sm"
                                        >
                                            إلغاء
                                        </button>
                                    )}
                                </div>
                            </form>
                        </div>

                        {/* List Column (2 Cols) */}
                        <div className="lg:col-span-2">
                            <div className="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                                <div className="p-6 border-b border-gray-100 bg-gray-50/50">
                                    <h3 className="font-bold text-gray-800 text-lg">المستودعات المسجلة</h3>
                                </div>
                                
                                <div className="overflow-x-auto">
                                    <table className="w-full text-right text-sm">
                                        <thead className="bg-gray-50 text-gray-500 border-b border-gray-200">
                                            <tr>
                                                <th className="px-6 py-4 font-bold">الرمز</th>
                                                <th className="px-6 py-4 font-bold">الاسم</th>
                                                <th className="px-6 py-4 font-bold">الموقع</th>
                                                <th className="px-6 py-4 font-bold text-center">أصناف مسجلة</th>
                                                <th className="px-6 py-4 font-bold text-center">الحالة</th>
                                                <th className="px-6 py-4 font-bold text-center w-36">العمليات</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-100">
                                            {warehouses.length === 0 ? (
                                                <tr>
                                                    <td colSpan="6" className="text-center py-12 text-gray-400 font-medium">
                                                        لا توجد مستودعات مسجلة حالياً.
                                                    </td>
                                                </tr>
                                            ) : (
                                                warehouses.map(wh => (
                                                    <tr key={wh.id} className="hover:bg-slate-50/50 transition-colors">
                                                        <td className="px-6 py-4 font-mono font-bold text-gray-900">{wh.code}</td>
                                                        <td className="px-6 py-4 font-bold text-gray-800">{wh.name}</td>
                                                        <td className="px-6 py-4 text-gray-600">{wh.location || '-'}</td>
                                                        <td className="px-6 py-4 text-center font-mono font-bold text-gray-700">{wh.stocks_count || 0}</td>
                                                        <td className="px-6 py-4 text-center">
                                                            <span className={`inline-flex px-2.5 py-1 rounded-full text-xs font-bold ${wh.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-gray-100 text-gray-600 border border-gray-200'}`}>
                                                                {wh.is_active ? 'نشط' : 'معطل'}
                                                            </span>
                                                        </td>
                                                        <td className="px-6 py-4 text-center flex justify-center gap-2">
                                                            <button
                                                                onClick={() => handleEdit(wh)}
                                                                className="text-blue-600 hover:bg-blue-50 p-2 rounded-lg transition-colors"
                                                                title="تعديل"
                                                            >
                                                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                            </button>
                                                            <button
                                                                onClick={() => handleDelete(wh.id)}
                                                                className="text-rose-600 hover:bg-rose-50 p-2 rounded-lg transition-colors"
                                                                title="حذف"
                                                            >
                                                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </AuthenticatedLayout>
    );
}
