import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Edit({ auth,  item  }) {
    const { data, setData, put, processing, errors } = useForm({
        name: item.name,
        sku: item.sku || '',
        type: item.type,
        price: item.price,
        tax_rate: item.tax_rate,
        description: item.description || '',
        is_active: item.is_active,
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('items.update', item.id));
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <>
            <Head title="تعديل بيانات الصنف" />

            <div className="min-h-screen bg-gray-50 pb-12" dir="rtl">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-8">
                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">تعديل: {item.name}</h1>
                        </div>
                        <Link href={route('items.index')} className="text-gray-600 hover:text-gray-900">
                            &larr; إغلاق ورجوع
                        </Link>
                    </div>

                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <form onSubmit={submit} className="space-y-6">
                            
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-sm font-bold text-gray-900 mb-1">اسم الصنف / الخدمة *</label>
                                    <input
                                        type="text"
                                        required
                                        className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        value={data.name}
                                        onChange={e => setData('name', e.target.value)}
                                    />
                                    {errors.name && <div className="text-red-500 text-sm mt-1">{errors.name}</div>}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">رمز SKU</label>
                                    <input
                                        type="text"
                                        className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-left"
                                        value={data.sku}
                                        onChange={e => setData('sku', e.target.value)}
                                        dir="ltr"
                                    />
                                    {errors.sku && <div className="text-red-500 text-sm mt-1">{errors.sku}</div>}
                                </div>
                            </div>
                            
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">نوع المادة *</label>
                                    <div className="flex gap-4">
                                        <label className="flex items-center">
                                            <input type="radio" value="product" checked={data.type === 'product'} onChange={e => setData('type', e.target.value)} className="text-blue-600 focus:ring-blue-500" />
                                            <span className="mr-2 text-sm text-gray-800">منتج ملموس</span>
                                        </label>
                                        <label className="flex items-center">
                                            <input type="radio" value="service" checked={data.type === 'service'} onChange={e => setData('type', e.target.value)} className="text-blue-600 focus:ring-blue-500" />
                                            <span className="mr-2 text-sm text-gray-800">خدمة</span>
                                        </label>
                                    </div>
                                    {errors.type && <div className="text-red-500 text-sm mt-1">{errors.type}</div>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-blue-50/50 rounded-xl border border-blue-100">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">سعر الوحدة الافتراضي *</label>
                                    <div className="relative">
                                        <input
                                            type="number" step="0.01" min="0" required
                                            className="w-full rounded-xl border-blue-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono"
                                            value={data.price}
                                            onChange={e => setData('price', e.target.value)}
                                        />
                                        <div className="absolute left-3 top-2 text-gray-500 text-sm">SAR</div>
                                    </div>
                                    {errors.price && <div className="text-red-500 text-sm mt-1">{errors.price}</div>}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">نسبة ضريبة القيمة المضافة *</label>
                                    <select
                                        required
                                        className="w-full rounded-xl border-blue-200 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        value={data.tax_rate}
                                        onChange={e => setData('tax_rate', e.target.value)}
                                    >
                                        <option value="15.00">خاضع للضريبة الأساسية (15%)</option>
                                        <option value="0.00">صنف صفري / معفى (0%)</option>
                                    </select>
                                    {errors.tax_rate && <div className="text-red-500 text-sm mt-1">{errors.tax_rate}</div>}
                                </div>
                            </div>
                            
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">الوصف والمواصفات</label>
                                <textarea
                                    className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    value={data.description}
                                    onChange={e => setData('description', e.target.value)}
                                    rows="3"
                                />
                            </div>

                            <div className="pt-4 border-t border-gray-100 flex justify-end gap-3">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 shadow-sm disabled:opacity-50"
                                >
                                    حفظ التعديلات
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
