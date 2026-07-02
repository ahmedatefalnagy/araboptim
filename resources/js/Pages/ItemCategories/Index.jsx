import React, { useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, categories }) {
    const { flash } = usePage().props;
    const [editingCategory, setEditingCategory] = useState(null);

    const { data, setData, post, put, delete: destroy, processing, reset, errors } = useForm({
        name: '',
        description: '',
    });

    const submit = (e) => {
        e.preventDefault();
        if (editingCategory) {
            put(route('item-categories.update', editingCategory.id), {
                onSuccess: () => {
                    setEditingCategory(null);
                    reset();
                }
            });
        } else {
            post(route('item-categories.store'), {
                onSuccess: () => reset()
            });
        }
    };

    const handleEdit = (category) => {
        setEditingCategory(category);
        setData({
            name: category.name,
            description: category.description || '',
        });
    };

    const handleCancelEdit = () => {
        setEditingCategory(null);
        reset();
    };

    const handleDelete = (id) => {
        if (confirm('هل أنت متأكد من رغبتك في حذف هذه المجموعة؟')) {
            destroy(route('item-categories.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">إدارة مجموعات الأصناف</h2>}
        >
            <Head title="مجموعات الأصناف" />

            <div className="py-12 bg-gray-50/50 min-h-screen">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    
                    {/* Header Section */}
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold tracking-tight text-gray-900 bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                            مجموعات الأصناف والتصنيفات
                        </h1>
                        <p className="mt-2 text-sm text-gray-600">
                            تنظيم وتصنيف المنتجات والخدمات في مجموعات لتسهيل البحث وإعداد التقارير.
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
                                        {editingCategory ? 'تعديل المجموعة' : 'إضافة مجموعة جديدة'}
                                    </h3>

                                    <div className="space-y-5">
                                        <div>
                                            <label className="block text-sm font-semibold text-gray-800 mb-2">اسم المجموعة <span className="text-red-500">*</span></label>
                                            <input
                                                type="text"
                                                className="w-full rounded-xl border-gray-200 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50/50 transition-colors"
                                                value={data.name}
                                                onChange={(e) => setData('name', e.target.value)}
                                                placeholder="مثال: قطع غيار، إلكترونيات"
                                                required
                                            />
                                            {errors.name && <p className="mt-2 text-sm text-red-600">{errors.name}</p>}
                                        </div>

                                        <div>
                                            <label className="block text-sm font-semibold text-gray-800 mb-2">الوصف</label>
                                            <textarea
                                                className="w-full rounded-xl border-gray-200 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50/50 transition-colors"
                                                value={data.description}
                                                onChange={(e) => setData('description', e.target.value)}
                                                placeholder="اكتب وصفاً تعريفياً للمجموعة هنا..."
                                                rows="4"
                                            />
                                            {errors.description && <p className="mt-2 text-sm text-red-600">{errors.description}</p>}
                                        </div>

                                        <div className="pt-2 flex gap-3">
                                            <button
                                                type="submit"
                                                disabled={processing}
                                                className="flex-1 inline-flex justify-center items-center px-6 py-3 text-sm font-medium rounded-xl shadow-md text-white bg-gradient-to-br from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200"
                                            >
                                                {editingCategory ? 'تعديل' : 'حفظ'}
                                            </button>
                                            
                                            {editingCategory && (
                                                <button
                                                    type="button"
                                                    onClick={handleCancelEdit}
                                                    className="inline-flex justify-center items-center px-6 py-3 text-sm font-medium rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition-all duration-200"
                                                >
                                                    إلغاء
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {/* Categories Table (2 Cols) */}
                        <div className="lg:col-span-2">
                            <div className="bg-white/80 backdrop-blur-xl shrink-0 rounded-2xl shadow-xl border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-2xl">
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200/50">
                                        <thead className="bg-gradient-to-r from-gray-50 to-white">
                                            <tr>
                                                <th scope="col" className="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                    اسم المجموعة
                                                </th>
                                                <th scope="col" className="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                    الوصف
                                                </th>
                                                <th scope="col" className="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                    عدد الأصناف
                                                </th>
                                                <th scope="col" className="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                                    إجراءات
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-100">
                                            {categories.length === 0 ? (
                                                <tr>
                                                    <td colSpan="4" className="px-6 py-12 text-center text-gray-500">
                                                        <div className="flex flex-col items-center justify-center space-y-3">
                                                            <div className="p-4 bg-gray-50 rounded-full">
                                                                <svg className="w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                                </svg>
                                                            </div>
                                                            <span className="text-lg font-medium text-gray-900">لا يوجد مجموعات حالياً</span>
                                                            <span className="text-sm">ابدأ بإضافة أول مجموعة جديدة من النموذج الجانبي.</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ) : (
                                                categories.map((category) => (
                                                    <tr key={category.id} className="hover:bg-indigo-50/30 transition-colors duration-150 group">
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <div className="text-sm font-bold text-gray-900">{category.name}</div>
                                                        </td>
                                                        <td className="px-6 py-4">
                                                            <div className="text-xs text-gray-500 max-w-xs truncate">{category.description || '---'}</div>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-700">
                                                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                                                {category.items_count} صنف
                                                            </span>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                                            <div className="flex items-center justify-end space-x-3 space-x-reverse opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                                <button
                                                                    onClick={() => handleEdit(category)}
                                                                    className="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-2 rounded-lg hover:bg-indigo-100 transition-colors"
                                                                >
                                                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                    </svg>
                                                                </button>
                                                                <button
                                                                    onClick={() => handleDelete(category.id)}
                                                                    disabled={category.items_count > 0}
                                                                    className={`p-2 rounded-lg transition-colors ${
                                                                        category.items_count > 0 
                                                                            ? 'text-gray-300 bg-gray-50 cursor-not-allowed' 
                                                                            : 'text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100'
                                                                    }`}
                                                                    title={category.items_count > 0 ? "لا يمكن حذف مجموعة تحتوي على أصناف" : "حذف المجموعة"}
                                                                >
                                                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                    </svg>
                                                                </button>
                                                            </div>
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
