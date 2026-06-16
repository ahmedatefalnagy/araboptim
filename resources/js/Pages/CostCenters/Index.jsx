import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, costCenters, flash }) {
    const [isEditing, setIsEditing] = useState(false);

    const generateCode = () => {
        const nextNum = costCenters.length + 1;
        return 'CC-' + String(nextNum).padStart(3, '0');
    };
    
    const { data, setData, post, put, delete: destroy, reset, processing, errors } = useForm({
        id: '',
        code: generateCode(),
        name: '',
        description: '',
        parent_id: '',
        is_active: true
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (isEditing) {
            put(route('cost-centers.update', data.id), {
                onSuccess: () => {
                    reset({ ...data, code: generateCode() });
                    setIsEditing(false);
                }
            });
        } else {
            post(route('cost-centers.store'), {
                onSuccess: () => reset({ id: '', code: generateCode(), name: '', description: '', parent_id: '', is_active: true })
            });
        }
    };

    const handleEdit = (cc) => {
        setIsEditing(true);
        setData({
            id: cc.id,
            code: cc.code,
            name: cc.name,
            description: cc.description || '',
            parent_id: cc.parent_id || '',
            is_active: cc.is_active
        });
    };

    const handleDelete = (id) => {
        if(confirm('هل أنت متأكد من حذف مركز التكلفة هذا؟')) {
            destroy(route('cost-centers.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">مراكز التكلفة</h2>}
        >
            <Head title="مراكز التكلفة" />

            <div className="py-12 bg-gray-50 min-h-screen" dir="rtl">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">
                    
                    <div className="w-full md:w-1/3">
                        <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <h3 className="text-lg font-bold text-gray-900 mb-4 pb-2 border-b">
                                {isEditing ? 'تعديل بيانات مركز تكلفة' : 'إنشاء مركز تكلفة جديد'}
                            </h3>

                            {flash?.success && <div className="mb-4 text-green-700 bg-green-50 p-3 rounded-lg text-sm">{flash.success}</div>}

                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div>
                                    <label className="block text-sm font-semibold mb-1">رمز المركز / الكود</label>
                                    <div className="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 font-mono font-bold text-blue-700 flex items-center gap-2">
                                        <svg className="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                                        {data.code}
                                        <span className="text-xs text-gray-400 font-normal mr-auto">تلقائي</span>
                                    </div>
                                </div>
                                <div>
                                    <label className="block text-sm font-semibold mb-1">اسم المركز (مشروع / فرع) <span className="text-red-500">*</span></label>
                                    <input 
                                        type="text" 
                                        className="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                        value={data.name}
                                        onChange={e => setData('name', e.target.value)}
                                        required 
                                    />
                                    {errors.name && <div className="text-red-500 text-xs mt-1">{errors.name}</div>}
                                </div>
                                <div>
                                    <label className="block text-sm font-semibold mb-1">مركز رئيسي (أب)</label>
                                    <select 
                                        className="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                        value={data.parent_id}
                                        onChange={e => setData('parent_id', e.target.value)}
                                    >
                                        <option value="">بدون مستوى أب (رئيسي)</option>
                                        {costCenters.filter(c => c.id !== data.id).map(c => (
                                            <option key={c.id} value={c.id}>[{c.code}] {c.name}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm font-semibold mb-1">وصف الميزانية / ملاحظات</label>
                                    <textarea 
                                        className="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                        value={data.description}
                                        onChange={e => setData('description', e.target.value)}
                                    />
                                </div>
                                
                                <div className="flex gap-2 pt-2">
                                    <button 
                                        type="submit" 
                                        disabled={processing}
                                        className="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-700 disabled:opacity-50"
                                    >
                                        {isEditing ? 'حفظ التعديلات' : 'إضافة المركز'}
                                    </button>
                                    {isEditing && (
                                        <button 
                                            type="button" 
                                            onClick={() => { reset(); setIsEditing(false); }}
                                            className="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold hover:bg-gray-300"
                                        >
                                            إلغاء
                                        </button>
                                    )}
                                </div>
                            </form>
                        </div>
                    </div>

                    <div className="w-full md:w-2/3">
                        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div className="overflow-x-auto">
                                <table className="w-full text-right text-sm">
                                    <thead className="bg-gray-50 text-gray-700 border-b">
                                        <tr>
                                            <th className="px-6 py-4 font-semibold">الكود</th>
                                            <th className="px-6 py-4 font-semibold">اسم المركز / المشروع</th>
                                            <th className="px-6 py-4 font-semibold">التبعية</th>
                                            <th className="px-6 py-4 font-semibold text-center">إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100">
                                        {costCenters.length > 0 ? costCenters.map(cc => (
                                            <tr key={cc.id} className="hover:bg-gray-50">
                                                <td className="px-6 py-4 font-mono font-bold text-gray-600">{cc.code}</td>
                                                <td className="px-6 py-4 font-bold text-blue-900">{cc.name}</td>
                                                <td className="px-6 py-4 text-gray-500">{cc.parent ? cc.parent.name : '-- رئيسي --'}</td>
                                                <td className="px-6 py-4 text-center">
                                                    <div className="flex items-center justify-center gap-3">
                                                        <button onClick={() => handleEdit(cc)} className="text-blue-600 font-bold hover:underline">تعديل</button>
                                                        <button onClick={() => handleDelete(cc.id)} className="text-red-500 font-bold hover:underline">حذف</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        )) : (
                                            <tr>
                                                <td colSpan="4" className="px-6 py-12 text-center text-gray-500 font-bold">لا يوجد مراكز تكلفة حالياً.</td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </AuthenticatedLayout>
    );
}
