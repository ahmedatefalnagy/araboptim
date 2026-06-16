import React from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Create({ auth, categories, units }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        sku: '',
        barcode: '',
        type: 'product',
        price: '0',
        cost_price: '0',
        tax_rate: '15',
        unit_id: units[0]?.id || '',
        category_id: categories[0]?.id || '',
        track_inventory: true,
        alert_quantity: '0',
        is_active: true,
        description: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('items.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">إضافة صنف جديد مستودعي</h2>}
        >
            <Head title="إضافة صنف جديد" />

            <div className="py-12 bg-gray-50/50 min-h-[calc(100vh-64px)]">
                <div className="max-w-5xl mx-auto sm:px-6 lg:px-8">
                    
                    <div className="mb-6 flex justify-between items-center">
                        <Link href={route('items.index')} className="text-gray-500 hover:text-indigo-600 flex items-center transition-colors text-sm font-medium">
                            <svg className="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                            </svg>
                            العودة لقائمة الأصناف
                        </Link>
                    </div>

                    <form onSubmit={submit} className="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden relative">
                        {/* Header Gradient */}
                        <div className="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                        
                        <div className="p-8">
                            <div className="mb-8 border-b border-gray-100 pb-5">
                                <h3 className="text-2xl font-bold text-gray-900">البطاقة التعريفية للصنف</h3>
                                <p className="mt-1 text-sm text-gray-500">أدخل المعطيات الأساسية لتعريف المنتج أو الخدمة في النظام</p>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                
                                {/* Basic Info Section */}
                                <div className="md:col-span-2">
                                    <label className="block text-sm font-semibold text-gray-800 mb-2">اسم الصنف <span className="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        className="w-full rounded-xl border-gray-200 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50/50 transition-colors"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="مثال: لابتوب ديل XPS 15"
                                        required
                                    />
                                    {errors.name && <p className="mt-2 text-sm text-red-600">{errors.name}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-gray-800 mb-2">رمز الصنف (SKU)</label>
                                    <input
                                        type="text"
                                        className="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono"
                                        value={data.sku}
                                        onChange={(e) => setData('sku', e.target.value)}
                                        placeholder="EX: LPT-DEL-15"
                                    />
                                    {errors.sku && <p className="mt-2 text-sm text-red-600">{errors.sku}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-gray-800 mb-2">الباركود التوافقي (Barcode)</label>
                                    <div className="relative">
                                        <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <svg className="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                        </div>
                                        <input
                                            type="text"
                                            className="w-full pr-10 rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-left"
                                            style={{direction: 'ltr'}}
                                            value={data.barcode}
                                            onChange={(e) => setData('barcode', e.target.value)}
                                            placeholder="458920193859"
                                        />
                                    </div>
                                    {errors.barcode && <p className="mt-2 text-sm text-red-600">{errors.barcode}</p>}
                                </div>

                                {/* Classifications */}
                                <div className="md:col-span-2 pt-4 mt-2 border-t border-gray-100">
                                    <h4 className="text-sm uppercase tracking-wider font-bold text-indigo-600 mb-4">التصنيفات والمخزون</h4>
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-gray-800 mb-2">نوع الصنف</label>
                                    <div className="flex gap-4">
                                        <label className={`flex-1 flex justify-center items-center px-4 py-3 border rounded-xl cursor-pointer transition-all ${data.type === 'product' ? 'bg-indigo-50 border-indigo-500 text-indigo-700 ring-1 ring-indigo-500 shadow-sm' : 'border-gray-200 text-gray-600 hover:bg-gray-50'}`}>
                                            <input type="radio" value="product" checked={data.type === 'product'} onChange={(e) => setData('type', e.target.value)} className="sr-only" />
                                            <svg className="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                            منتج مخزني
                                        </label>
                                        <label className={`flex-1 flex justify-center items-center px-4 py-3 border rounded-xl cursor-pointer transition-all ${data.type === 'service' ? 'bg-indigo-50 border-indigo-500 text-indigo-700 ring-1 ring-indigo-500 shadow-sm' : 'border-gray-200 text-gray-600 hover:bg-gray-50'}`}>
                                            <input type="radio" value="service" checked={data.type === 'service'} onChange={(e) => setData('type', e.target.value)} className="sr-only" />
                                            <svg className="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                            خدمة (بدون مخزون)
                                        </label>
                                    </div>
                                    {errors.type && <p className="mt-2 text-sm text-red-600">{errors.type}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-gray-800 mb-2">التصنيف (المجموعة)</label>
                                    <select
                                        className="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-700"
                                        value={data.category_id}
                                        onChange={(e) => setData('category_id', e.target.value)}
                                        required
                                    >
                                        <option value="" disabled>اختر التصنيف...</option>
                                        {categories.map(cat => (
                                            <option key={cat.id} value={cat.id}>{cat.name}</option>
                                        ))}
                                    </select>
                                    {errors.category_id && <p className="mt-2 text-sm text-red-600">{errors.category_id}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-gray-800 mb-2">وحدة القياس</label>
                                    <select
                                        className="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-700"
                                        value={data.unit_id}
                                        onChange={(e) => setData('unit_id', e.target.value)}
                                        required
                                    >
                                        <option value="" disabled>اختر الوحدة...</option>
                                        {units.map(unit => (
                                            <option key={unit.id} value={unit.id}>{unit.name} ({unit.short_name})</option>
                                        ))}
                                    </select>
                                    {errors.unit_id && <p className="mt-2 text-sm text-red-600">{errors.unit_id}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-gray-800 mb-2">إشعار انخفاض الكمية</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        className={`w-full rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 ${data.type === 'service' ? 'bg-gray-100 border-gray-100 text-gray-400' : 'border-gray-200'}`}
                                        value={data.alert_quantity}
                                        onChange={(e) => setData('alert_quantity', e.target.value)}
                                        disabled={data.type === 'service'}
                                    />
                                    {errors.alert_quantity && <p className="mt-2 text-sm text-red-600">{errors.alert_quantity}</p>}
                                </div>

                                {/* Pricing */}
                                <div className="md:col-span-2 pt-4 mt-2 border-t border-gray-100">
                                    <h4 className="text-sm uppercase tracking-wider font-bold text-emerald-600 mb-4">بيانات التسعير</h4>
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-gray-800 mb-2">سعر التكلفة (للوحدة)</label>
                                    <div className="relative">
                                        <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span className="text-gray-500 sm:text-sm font-bold">ر.س</span>
                                        </div>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            className="w-full pr-12 rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                            value={data.cost_price}
                                            onChange={(e) => setData('cost_price', e.target.value)}
                                            required
                                        />
                                    </div>
                                    {errors.cost_price && <p className="mt-2 text-sm text-red-600">{errors.cost_price}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-bold text-gray-800 mb-2">سعر البيع الافتراضي</label>
                                    <div className="relative">
                                        <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span className="text-gray-500 sm:text-sm font-bold">ر.س</span>
                                        </div>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            className="w-full pr-12 rounded-xl border-indigo-200 bg-indigo-50/30 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-indigo-900 font-bold"
                                            value={data.price}
                                            onChange={(e) => setData('price', e.target.value)}
                                            required
                                        />
                                    </div>
                                    {errors.price && <p className="mt-2 text-sm text-red-600">{errors.price}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-gray-800 mb-2">نسبة ضريبة القيمة المضافة (VAT)</label>
                                    <select
                                        className="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        value={data.tax_rate}
                                        onChange={(e) => setData('tax_rate', e.target.value)}
                                    >
                                        <option value="15">15%</option>
                                        <option value="0">0% (معفى)</option>
                                    </select>
                                    {errors.tax_rate && <p className="mt-2 text-sm text-red-600">{errors.tax_rate}</p>}
                                </div>

                                {/* Status Options */}
                                <div className="md:col-span-2 pt-4 mt-2 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div className="flex items-center bg-gray-50 p-4 rounded-xl">
                                        <input
                                            id="is_active"
                                            type="checkbox"
                                            className="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            checked={data.is_active}
                                            onChange={(e) => setData('is_active', e.target.checked)}
                                        />
                                        <label htmlFor="is_active" className="ml-3 block text-sm font-medium text-gray-700 mr-3 mr-3 mr-right mr-auto pr-3">
                                            الصنف نشط ومفعل البيع
                                        </label>
                                    </div>

                                    <div className="flex items-center bg-gray-50 p-4 rounded-xl">
                                        <input
                                            id="track_inventory"
                                            type="checkbox"
                                            className={`h-5 w-5 rounded ${data.type === 'service' ? 'text-gray-300 border-gray-200' : 'text-indigo-600 focus:ring-indigo-500 border-gray-300'}`}
                                            checked={data.track_inventory}
                                            onChange={(e) => setData('track_inventory', e.target.checked)}
                                            disabled={data.type === 'service'}
                                        />
                                        <label htmlFor="track_inventory" className="ml-3 block text-sm font-medium text-gray-700 mr-3 pr-3">
                                            تتبع الكمية بالمخزن
                                            {data.type === 'service' && <span className="block text-xs text-gray-400 mt-1">(غير متاح للخدمات)</span>}
                                        </label>
                                    </div>
                                </div>

                                <div className="md:col-span-2">
                                    <label className="block text-sm font-semibold text-gray-800 mb-2">وصف تفصيلي</label>
                                    <textarea
                                        rows="3"
                                        className="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        placeholder="تفاصيل إضافية عن الصنف..."
                                    ></textarea>
                                    {errors.description && <p className="mt-2 text-sm text-red-600">{errors.description}</p>}
                                </div>

                            </div>
                        </div>

                        <div className="px-8 py-5 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-4">
                            <Link
                                href={route('items.index')}
                                className="px-6 py-2.5 bg-white border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                            >
                                إلغاء
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center justify-center px-8 py-2.5 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all disabled:opacity-50"
                            >
                                {processing ? (
                                    <>
                                        <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>جاري الحفظ...</span>
                                    </>
                                ) : (
                                    'حفظ واعتماد الصنف'
                                )}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
