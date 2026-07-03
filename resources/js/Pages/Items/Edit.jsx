import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Edit({ auth, item, categories = [], units = [] }) {
    const { data, setData, put, processing, errors } = useForm({
        name: item.name,
        sku: item.sku || '',
        barcode: item.barcode || '',
        type: item.type,
        price: item.price || 0,
        cost_price: item.cost_price || 0,
        tax_rate: item.tax_rate || 15,
        unit_id: item.unit_id || (units[0]?.id || ''),
        category_id: item.category_id || (categories[0]?.id || ''),
        track_inventory: item.track_inventory !== undefined ? !!item.track_inventory : true,
        alert_quantity: item.alert_quantity || 0,
        is_active: item.is_active !== undefined ? !!item.is_active : true,
        description: item.description || '',
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
                            
                            {errors?.message && (
                                <div className="bg-red-50 border-r-4 border-red-500 p-4 rounded-xl text-red-800 font-bold mb-4">
                                    {errors.message}
                                </div>
                            )}
                            
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
                                    <label className="block text-sm font-medium text-gray-700 mb-1">الباركود (Barcode)</label>
                                    <input
                                        type="text"
                                        className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-left"
                                        value={data.barcode}
                                        onChange={e => setData('barcode', e.target.value)}
                                        dir="ltr"
                                    />
                                    {errors.barcode && <div className="text-red-500 text-sm mt-1">{errors.barcode}</div>}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">نوع المادة *</label>
                                    <div className="flex gap-4 mt-2">
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

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">المجموعة التصنيفية *</label>
                                    <select
                                        required
                                        className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        value={data.category_id}
                                        onChange={e => setData('category_id', e.target.value)}
                                    >
                                        <option value="">== اختر المجموعة ==</option>
                                        {categories.map(cat => (
                                            <option key={cat.id} value={cat.id}>{cat.name}</option>
                                        ))}
                                    </select>
                                    {errors.category_id && <div className="text-red-500 text-sm mt-1">{errors.category_id}</div>}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">وحدة القياس *</label>
                                    <select
                                        required
                                        className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        value={data.unit_id}
                                        onChange={e => setData('unit_id', e.target.value)}
                                    >
                                        <option value="">== اختر الوحدة ==</option>
                                        {units.map(u => (
                                            <option key={u.id} value={u.id}>{u.name} ({u.short_name})</option>
                                        ))}
                                    </select>
                                    {errors.unit_id && <div className="text-red-500 text-sm mt-1">{errors.unit_id}</div>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 p-6 bg-blue-50/50 rounded-xl border border-blue-100">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">سعر البيع الافتراضي *</label>
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
                                    <label className="block text-sm font-medium text-gray-700 mb-1">تكلفة الشراء الافتراضية *</label>
                                    <div className="relative">
                                        <input
                                            type="number" step="0.01" min="0" required
                                            className="w-full rounded-xl border-blue-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono"
                                            value={data.cost_price}
                                            onChange={e => setData('cost_price', e.target.value)}
                                        />
                                        <div className="absolute left-3 top-2 text-gray-500 text-sm">SAR</div>
                                    </div>
                                    {errors.cost_price && <div className="text-red-500 text-sm mt-1">{errors.cost_price}</div>}
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

                            {data.type === 'product' && (
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-amber-50/30 rounded-xl border border-amber-100">
                                    <div className="flex items-center mt-2">
                                        <input
                                            type="checkbox"
                                            id="track_inventory"
                                            checked={data.track_inventory}
                                            onChange={e => setData('track_inventory', e.target.checked)}
                                            className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                        <label htmlFor="track_inventory" className="mr-2 block text-sm font-bold text-gray-900">
                                            تتبع المخزون والكميات
                                        </label>
                                    </div>
                                    {data.track_inventory && (
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">حد الطلب الأدنى (الكمية الحرجة)</label>
                                            <input
                                                type="number"
                                                min="0"
                                                className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                value={data.alert_quantity}
                                                onChange={e => setData('alert_quantity', e.target.value)}
                                            />
                                        </div>
                                    )}
                                </div>
                            )}
                            
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">الوصف والمواصفات</label>
                                <textarea
                                    className="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    value={data.description}
                                    onChange={e => setData('description', e.target.value)}
                                    rows="3"
                                />
                            </div>

                            <div className="flex items-center">
                                <input
                                    type="checkbox"
                                    id="is_active"
                                    checked={data.is_active}
                                    onChange={e => setData('is_active', e.target.checked)}
                                    className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <label htmlFor="is_active" className="mr-2 block text-sm font-bold text-gray-900">
                                    نشط ومتاح للاستخدام في الفواتير
                                </label>
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
