import { Head, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';
import Modal from '@/Components/Modal';

export default function Index({ auth, assets, paymentAccounts, accDepAccounts, expenseAccounts, parentAssetAccounts }) {
    const { props } = usePage();
    const flash = props.flash || {};

    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [isDepModalOpen, setIsDepModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [selectedAsset, setSelectedAsset] = useState(null);

    const { data: addData, setData: setAddData, post: postAdd, processing: processingAdd, errors: addErrors, reset: resetAdd } = useForm({
        parent_account_id: '',
        name: '',
        depreciation_rate: '',
        purchase_date: new Date().toISOString().split('T')[0],
        purchase_amount: '',
        payment_account_id: '',
    });

    const { data: editData, setData: setEditData, put: putEdit, processing: processingEdit, errors: editErrors, reset: resetEdit } = useForm({
        name: '',
        depreciation_rate: '',
    });

    const { data: depData, setData: setDepData, post: postDep, processing: processingDep, errors: depErrors, reset: resetDep } = useForm({
        asset_account_id: '',
        expense_account_id: '',
        acc_dep_account_id: '',
        depreciation_amount: '',
        entry_date: new Date().toISOString().split('T')[0],
        description: '',
    });

    const handleAddAsset = (e) => {
        e.preventDefault();
        postAdd(route('fixed-assets.store'), {
            onSuccess: () => {
                setIsAddModalOpen(false);
                resetAdd();
            }
        });
    };

    const handleOpenEditModal = (asset) => {
        setSelectedAsset(asset);
        setEditData({
            name: asset.name,
            depreciation_rate: asset.rate,
        });
        setIsEditModalOpen(true);
    };

    const handleEditAsset = (e) => {
        e.preventDefault();
        putEdit(route('fixed-assets.update', selectedAsset.id), {
            onSuccess: () => {
                setIsEditModalOpen(false);
                resetEdit();
            }
        });
    };

    const handleOpenDepModal = (asset) => {
        setSelectedAsset(asset);
        
        // Auto-select accumulated depreciation account based on asset group
        let suggestedAccDep = '';
        const match = accDepAccounts.find(a => {
            const normAsset = asset.name.replace(/ال| /g, '');
            const normAcc = a.name.replace(/ال| /g, '');
            return normAcc.includes(normAsset) || normAsset.includes(normAcc);
        });
        if (match) suggestedAccDep = match.id;

        setDepData({
            asset_account_id: asset.id,
            expense_account_id: '',
            acc_dep_account_id: suggestedAccDep,
            depreciation_amount: asset.suggested_depreciation.toFixed(2),
            entry_date: new Date().getFullYear() + '-12-31',
            description: 'إثبات قيد الإهلاك السنوي لـ: ' + asset.name,
        });
        setIsDepModalOpen(true);
    };

    const handleDepreciate = (e) => {
        e.preventDefault();
        postDep(route('fixed-assets.depreciate'), {
            onSuccess: () => {
                setIsDepModalOpen(false);
                resetDep();
            }
        });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800">إدارة الأصول الثابتة</h2>}>
            <Head title="الأصول الثابتة" />

            <div className="min-h-screen bg-gray-100" dir="rtl">
                <div className="mx-auto max-w-7xl p-6">
                    <div className="mb-8 flex justify-between items-center">
                        <div>
                            <h1 className="text-2xl font-black text-slate-900">إدارة الأصول الثابتة</h1>
                            <p className="mt-1 text-sm text-slate-500 font-medium">سجل الأصول، الشراء، وإثبات الإهلاك آلياً (حسب معايير SOCPA / IFRS)</p>
                        </div>
                        <button
                            onClick={() => setIsAddModalOpen(true)}
                            className="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold transition flex items-center gap-2"
                        >
                            ➕ شراء أصل جديد
                        </button>
                    </div>

                    {flash.success && (
                        <div className="mb-4 rounded-xl bg-green-100 px-4 py-3 text-green-800 font-bold border border-green-200">
                            {flash.success}
                        </div>
                    )}
                    {flash.error && (
                        <div className="mb-4 rounded-xl bg-red-100 px-4 py-3 text-red-800 font-bold border border-red-200">
                            {flash.error}
                        </div>
                    )}

                    <div className="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
                        <div className="overflow-x-auto">
                            <table className="min-w-full">
                                <thead className="bg-slate-50 border-b border-gray-200">
                                    <tr>
                                        <th className="px-4 py-4 text-right text-sm font-semibold text-gray-600">الكود</th>
                                        <th className="px-4 py-4 text-right text-sm font-semibold text-gray-600">اسم الأصل</th>
                                        <th className="px-4 py-4 text-center text-sm font-semibold text-gray-600">تكلفة الأصل</th>
                                        <th className="px-4 py-4 text-center text-sm font-semibold text-gray-600">مجمع الإهلاك</th>
                                        <th className="px-4 py-4 text-center text-sm font-semibold text-gray-600">صافي القيمة الدفترية</th>
                                        <th className="px-4 py-4 text-center text-sm font-semibold text-gray-600">نسبة الإهلاك</th>
                                        <th className="px-4 py-4 text-center text-sm font-semibold text-gray-600">الإهلاك المستحق</th>
                                        <th className="px-4 py-4 text-center text-sm font-semibold text-gray-600">إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {assets.length === 0 ? (
                                        <tr>
                                            <td colSpan="8" className="px-4 py-8 text-center text-gray-500 font-medium">لا توجد أصول ثابتة مسجلة في النظام.</td>
                                        </tr>
                                    ) : assets.map((asset) => (
                                        <tr key={asset.id} className="hover:bg-slate-50 transition">
                                            <td className="px-4 py-3 text-sm font-mono text-blue-600 font-bold">{asset.code}</td>
                                            <td className="px-4 py-3 text-sm font-bold text-gray-800">{asset.name}</td>
                                            <td className="px-4 py-3 text-center text-sm">{asset.opening_asset.toLocaleString()}</td>
                                            <td className="px-4 py-3 text-center text-sm text-red-600">({asset.opening_acc_dep.toLocaleString()})</td>
                                            <td className="px-4 py-3 text-center text-sm font-bold text-green-700">{asset.nbv_opening.toLocaleString()}</td>
                                            <td className="px-4 py-3 text-center text-sm">
                                                <span className="bg-blue-100 text-blue-800 px-2 py-1 rounded-md font-bold">{asset.rate}%</span>
                                            </td>
                                            <td className="px-4 py-3 text-center text-sm font-bold">{asset.suggested_depreciation.toLocaleString()}</td>
                                            <td className="px-4 py-3 text-center">
                                                <div className="flex gap-2 justify-center">
                                                    <button
                                                        onClick={() => handleOpenDepModal(asset)}
                                                        disabled={asset.nbv_opening <= 0}
                                                        className={`px-4 py-1.5 rounded-lg text-xs font-bold transition ${asset.nbv_opening > 0 ? 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100' : 'bg-gray-100 text-gray-400 cursor-not-allowed'}`}
                                                    >
                                                        إثبات الإهلاك
                                                    </button>
                                                    <button
                                                        onClick={() => handleOpenEditModal(asset)}
                                                        className="px-3 py-1.5 rounded-lg text-xs font-bold transition bg-gray-50 text-gray-700 hover:bg-gray-200"
                                                        title="تعديل الأصل"
                                                    >
                                                        ✏️
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {/* Modal إضافة أصل */}
            <Modal show={isAddModalOpen} onClose={() => setIsAddModalOpen(false)}>
                <div className="p-6" dir="rtl">
                    <h2 className="text-xl font-bold text-gray-900 mb-4">شراء أصل ثابت جديد</h2>
                    <p className="text-sm text-gray-600 mb-6">سيقوم النظام بإنشاء حساب للأصل آلياً وإثبات قيد الشراء.</p>

                    <form onSubmit={handleAddAsset} className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium mb-1">المجموعة الرئيسية (مثال: السيارات)</label>
                            <select
                                value={addData.parent_account_id}
                                onChange={e => setAddData('parent_account_id', e.target.value)}
                                className="w-full rounded-xl border-gray-300 shadow-sm"
                                required
                            >
                                <option value="">اختر المجموعة</option>
                                {parentAssetAccounts.map(a => <option key={a.id} value={a.id}>{a.code} - {a.name}</option>)}
                            </select>
                            {addErrors.parent_account_id && <p className="text-red-500 text-xs mt-1">{addErrors.parent_account_id}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium mb-1">اسم الأصل (البيان)</label>
                            <input
                                type="text"
                                value={addData.name}
                                onChange={e => setAddData('name', e.target.value)}
                                className="w-full rounded-xl border-gray-300 shadow-sm"
                                placeholder="مثال: سيارة تويوتا كامري 2024"
                                required
                            />
                            {addErrors.name && <p className="text-red-500 text-xs mt-1">{addErrors.name}</p>}
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium mb-1">نسبة الإهلاك (%)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    value={addData.depreciation_rate}
                                    onChange={e => setAddData('depreciation_rate', e.target.value)}
                                    className="w-full rounded-xl border-gray-300 shadow-sm"
                                    placeholder="مثال: 15"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium mb-1">تاريخ الشراء</label>
                                <input
                                    type="date"
                                    value={addData.purchase_date}
                                    onChange={e => setAddData('purchase_date', e.target.value)}
                                    className="w-full rounded-xl border-gray-300 shadow-sm"
                                    required
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium mb-1">تكلفة الشراء (بدون الضريبة إن وجدت)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    value={addData.purchase_amount}
                                    onChange={e => setAddData('purchase_amount', e.target.value)}
                                    className="w-full rounded-xl border-gray-300 shadow-sm"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium mb-1">دفع من حساب</label>
                                <select
                                    value={addData.payment_account_id}
                                    onChange={e => setAddData('payment_account_id', e.target.value)}
                                    className="w-full rounded-xl border-gray-300 shadow-sm"
                                    required
                                >
                                    <option value="">اختر حساب الدفع</option>
                                    {paymentAccounts.map(a => <option key={a.id} value={a.id}>{a.code} - {a.name}</option>)}
                                </select>
                            </div>
                        </div>

                        <div className="mt-6 flex justify-end gap-3">
                            <button type="button" onClick={() => setIsAddModalOpen(false)} className="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium">إلغاء</button>
                            <button type="submit" disabled={processingAdd} className="px-4 py-2 bg-blue-600 text-white rounded-xl font-medium disabled:opacity-50">
                                {processingAdd ? 'جاري الحفظ...' : 'حفظ الأصل وإثبات الشراء'}
                            </button>
                        </div>
                    </form>
                </div>
            </Modal>

            {/* Modal تعديل الأصل */}
            <Modal show={isEditModalOpen} onClose={() => setIsEditModalOpen(false)}>
                <div className="p-6" dir="rtl">
                    <h2 className="text-xl font-bold text-gray-900 mb-4">تعديل بيانات الأصل</h2>
                    <p className="text-sm text-gray-600 mb-6">الأصل: <span className="font-bold text-blue-600">{selectedAsset?.code}</span></p>

                    <form onSubmit={handleEditAsset} className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium mb-1">اسم الأصل</label>
                            <input
                                type="text"
                                value={editData.name}
                                onChange={e => setEditData('name', e.target.value)}
                                className="w-full rounded-xl border-gray-300 shadow-sm"
                                required
                            />
                            {editErrors.name && <p className="text-red-500 text-xs mt-1">{editErrors.name}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium mb-1">نسبة الإهلاك الجديدة (%)</label>
                            <input
                                type="number"
                                step="0.01"
                                value={editData.depreciation_rate}
                                onChange={e => setEditData('depreciation_rate', e.target.value)}
                                className="w-full rounded-xl border-gray-300 shadow-sm"
                                required
                            />
                            <p className="text-[10px] text-gray-400 mt-1">تغيير النسبة هو تغيير في التقدير المحاسبي، وسيتم تطبيقه على القيمة الدفترية المتبقية.</p>
                            {editErrors.depreciation_rate && <p className="text-red-500 text-xs mt-1">{editErrors.depreciation_rate}</p>}
                        </div>

                        <div className="mt-6 flex justify-end gap-3">
                            <button type="button" onClick={() => setIsEditModalOpen(false)} className="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium">إلغاء</button>
                            <button type="submit" disabled={processingEdit} className="px-4 py-2 bg-blue-600 text-white rounded-xl font-medium disabled:opacity-50">
                                {processingEdit ? 'جاري الحفظ...' : 'حفظ التعديلات'}
                            </button>
                        </div>
                    </form>
                </div>
            </Modal>

            {/* Modal إثبات الإهلاك */}
            <Modal show={isDepModalOpen} onClose={() => setIsDepModalOpen(false)}>
                <div className="p-6" dir="rtl">
                    <h2 className="text-xl font-bold text-gray-900 mb-4">إثبات قيد الإهلاك السنوي</h2>
                    <p className="text-sm text-gray-600 mb-6">الأصل: <span className="font-bold text-blue-600">{selectedAsset?.name}</span></p>

                    <form onSubmit={handleDepreciate} className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium mb-1">تاريخ قيد الإهلاك</label>
                                <input
                                    type="date"
                                    value={depData.entry_date}
                                    onChange={e => setDepData('entry_date', e.target.value)}
                                    className="w-full rounded-xl border-gray-300 shadow-sm"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium mb-1">مبلغ الإهلاك</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    value={depData.depreciation_amount}
                                    onChange={e => setDepData('depreciation_amount', e.target.value)}
                                    className="w-full rounded-xl border-gray-300 shadow-sm bg-yellow-50 font-bold"
                                    required
                                />
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm font-medium mb-1">حساب مصروف الإهلاك (مدين)</label>
                            <select
                                value={depData.expense_account_id}
                                onChange={e => setDepData('expense_account_id', e.target.value)}
                                className="w-full rounded-xl border-gray-300 shadow-sm"
                                required
                            >
                                <option value="">اختر حساب المصروف</option>
                                {expenseAccounts.map(a => <option key={a.id} value={a.id}>{a.code} - {a.name}</option>)}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium mb-1">حساب مجمع الإهلاك (دائن)</label>
                            <select
                                value={depData.acc_dep_account_id}
                                onChange={e => setDepData('acc_dep_account_id', e.target.value)}
                                className="w-full rounded-xl border-gray-300 shadow-sm"
                                required
                            >
                                <option value="">اختر مجمع الإهلاك</option>
                                {accDepAccounts.map(a => <option key={a.id} value={a.id}>{a.code} - {a.name}</option>)}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium mb-1">البيان / الوصف</label>
                            <input
                                type="text"
                                value={depData.description}
                                onChange={e => setDepData('description', e.target.value)}
                                className="w-full rounded-xl border-gray-300 shadow-sm"
                                required
                            />
                        </div>

                        <div className="mt-6 flex justify-end gap-3">
                            <button type="button" onClick={() => setIsDepModalOpen(false)} className="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium">إلغاء</button>
                            <button type="submit" disabled={processingDep} className="px-4 py-2 bg-indigo-600 text-white rounded-xl font-medium disabled:opacity-50">
                                {processingDep ? 'جاري الإثبات...' : 'إثبات قيد الإهلاك آلياً'}
                            </button>
                        </div>
                    </form>
                </div>
            </Modal>

        </AuthenticatedLayout>
    );
}
