import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, items }) {
    const { flash, errors } = usePage().props;

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">إدارة الأصناف (المستودع الرئيسي)</h2>}
        >
            <Head title="الأصناف" />

            <div className="py-12 bg-gray-50/50 min-h-screen">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    
                    {/* Header Action Section */}
                    <div className="flex justify-between items-center mb-8">
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight text-gray-900 bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                                دليل الأصناف والمنتجات
                            </h1>
                            <p className="mt-2 text-sm text-gray-600">
                                إدارة شاملة لجميع منتجاتك، تتبع الكميات بشكل حي، وتسعير مرن.
                            </p>
                        </div>
                        <div className="flex gap-4">
                            <Link
                                href={route('items.create')}
                                className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl shadow-lg text-white bg-gradient-to-br from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:-translate-y-1"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 ml-2 -mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                                </svg>
                                إضافة صنف جديد
                            </Link>
                        </div>
                    </div>

                    {(flash?.message || flash?.success) && (
                        <div className="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center shadow-sm">
                            <svg className="h-5 w-5 text-emerald-400 ml-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                            </svg>
                            <p className="text-sm font-medium text-emerald-800">{flash.message || flash.success}</p>
                        </div>
                    )}

                    {(flash?.error || errors?.message) && (
                        <div className="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-center shadow-sm">
                            <svg className="h-5 w-5 text-red-400 ml-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                            </svg>
                            <p className="text-sm font-medium text-red-800">{flash.error || errors.message}</p>
                        </div>
                    )}

                    <div className="bg-white/80 backdrop-blur-xl shrink-0 rounded-2xl shadow-xl border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-2xl">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200/50">
                                <thead className="bg-gradient-to-r from-gray-50 to-white">
                                    <tr>
                                        <th scope="col" className="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            الرمز/الباركود
                                        </th>
                                        <th scope="col" className="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            اسم الصنف
                                        </th>
                                        <th scope="col" className="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            النوع والتصنيف
                                        </th>
                                        <th scope="col" className="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            سعر التكلفة
                                        </th>
                                        <th scope="col" className="px-6 py-4 text-center text-xs font-bold text-indigo-500 uppercase tracking-wider">
                                            سعر البيع
                                        </th>
                                        <th scope="col" className="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            الحالة
                                        </th>
                                        <th scope="col" className="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            إجراءات
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-100">
                                    {items.length === 0 ? (
                                        <tr>
                                            <td colSpan="7" className="px-6 py-12 text-center text-gray-500">
                                                <div className="flex flex-col items-center justify-center space-y-3">
                                                    <div className="p-4 bg-gray-50 rounded-full">
                                                        <svg className="w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                        </svg>
                                                    </div>
                                                    <span className="text-lg font-medium text-gray-900">لا يوجد أصناف حالياً</span>
                                                    <span className="text-sm">ابدأ بإنشاء أول منتج لك لإدارة مخزونك.</span>
                                                </div>
                                            </td>
                                        </tr>
                                    ) : (
                                        items.map((item) => (
                                            <tr key={item.id} className="hover:bg-indigo-50/30 transition-colors duration-150 group">
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="flex flex-col">
                                                        <span className="text-sm font-semibold text-gray-700">{item.sku || 'N/A'}</span>
                                                        <span className="text-xs text-gray-400 font-mono mt-1">{item.barcode || '---'}</span>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="text-sm font-bold text-gray-900">{item.name}</div>
                                                    {item.description && (
                                                        <div className="text-xs text-gray-500 mt-1 truncate max-w-xs">{item.description}</div>
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="flex flex-col gap-1 items-start">
                                                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${item.type === 'product' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'}`}>
                                                            {item.type === 'product' ? 'منتج مخزني' : 'خدمة'}
                                                        </span>
                                                        <span className="text-xs text-gray-500 flex items-center">
                                                            <svg className="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                                            {item.category?.name || 'بدون تصنيف'} ({item.unit?.short_name || 'وحدة'})
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-600">
                                                    {parseFloat(item.cost_price).toFixed(2)} ر.س
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-center text-sm font-black text-indigo-600 bg-indigo-50/10">
                                                    {parseFloat(item.price).toFixed(2)} ر.س
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-center">
                                                    <span className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${
                                                        item.is_active 
                                                            ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' 
                                                            : 'bg-red-100 text-red-800 border border-red-200'
                                                    }`}>
                                                        <span className={`w-1.5 h-1.5 rounded-full ml-1.5 ${item.is_active ? 'bg-emerald-500' : 'bg-red-500'}`}></span>
                                                        {item.is_active ? 'نشط' : 'غير نشط'}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                                    <div className="flex items-center justify-end space-x-3 space-x-reverse">
                                                        <Link href={route('items.edit', item.id)} className="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-2 rounded-lg hover:bg-indigo-100 transition-colors" title="تعديل">
                                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </Link>
                                                        <Link 
                                                            href={route('items.destroy', item.id)} 
                                                            method="delete" 
                                                            as="button" 
                                                            className="text-red-600 hover:text-red-900 bg-red-50 p-2 rounded-lg hover:bg-red-100 transition-colors"
                                                            title="حذف"
                                                            onClick={(e) => {
                                                                if(!confirm('هل أنت متأكد من رغبتك في حذف هذا الصنف؟')) {
                                                                    e.preventDefault();
                                                                }
                                                            }}
                                                        >
                                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </Link>
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
        </AuthenticatedLayout>
    );
}
