import { Head, useForm, usePage, router } from '@inertiajs/react';
import BackButton from '@/Components/BackButton';
import { useMemo, useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

const AccountRow = ({ account, level, onEdit, onAddChild, onRemove }) => {
    const [isExpanded, setIsExpanded] = useState(false);
    const hasChildren = account.children && account.children.length > 0;

    return (
        <>
            <tr className="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                <td className="px-4 py-3 text-sm font-medium text-gray-800">
                    <div 
                        className="flex items-center gap-2 cursor-pointer"
                        style={{ paddingRight: `${level * 20}px` }}
                        onClick={() => hasChildren && setIsExpanded(!isExpanded)}
                    >
                        {hasChildren ? (
                            <span className="text-gray-400 w-4 text-center">
                                {isExpanded ? '▼' : '◀'}
                            </span>
                        ) : (
                            <span className="w-4"></span>
                        )}
                        
                        <span className="text-lg">
                            {!account.is_postable ? '📁' : '📄'}
                        </span>
                        
                        <span className={!account.is_postable ? 'font-bold' : ''}>
                            {account.code} - {account.name}
                        </span>
                    </div>
                </td>
                <td className="px-4 py-3 text-sm">{account.account_type?.name || '-'}</td>
                <td className="px-4 py-3 text-sm">
                    <span className={`px-2 py-1 rounded-full text-xs ${account.is_postable ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-800'}`}>
                        {account.is_postable ? 'تشغيلي' : 'تجميعي'}
                    </span>
                </td>
                <td className="px-4 py-3 text-sm">
                    <span className={`px-2 py-1 rounded-full text-xs ${account.is_active ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'}`}>
                        {account.is_active ? 'نشط' : 'غير نشط'}
                    </span>
                </td>
                <td className="px-4 py-3 text-sm">
                    <div className="flex gap-2">
                        <button 
                            onClick={(e) => { e.stopPropagation(); onEdit(account); }}
                            className="bg-gray-100 hover:bg-blue-100 text-blue-600 px-3 py-1 rounded-lg text-xs"
                            title="تعديل الحساب"
                        >
                            ✏️ تعديل
                        </button>
                        {!account.is_postable && (
                            <button 
                                onClick={(e) => { e.stopPropagation(); onAddChild(account); }}
                                className="bg-gray-100 hover:bg-green-100 text-green-600 px-3 py-1 rounded-lg text-xs"
                                title="إضافة حساب فرعي"
                            >
                                ➕ إضافة فرعي
                            </button>
                        )}
                        {account.parent_id !== null && account.is_postable && (
                            <button 
                                onClick={(e) => { 
                                    e.stopPropagation(); 
                                    onRemove(account);
                                }}
                                className="bg-gray-100 hover:bg-red-100 text-red-600 px-3 py-1 rounded-lg text-xs"
                                title="حذف الحساب"
                            >
                                🗑️ حذف
                            </button>
                        )}
                    </div>
                </td>
            </tr>
            {isExpanded && hasChildren && account.children.map(child => (
                <AccountRow 
                    key={child.id} 
                    account={child} 
                    level={level + 1} 
                    onEdit={onEdit} 
                    onAddChild={onAddChild} 
                    onRemove={onRemove}
                />
            ))}
        </>
    );
};

export default function Index({ auth, accounts, accountTypes, parents }) {
    const { props } = usePage();
    const flash = props.flash || {};

    const [editingAccount, setEditingAccount] = useState(null);

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        parent_id: '',
        code: '',
        name: '',
        account_type_id: '',
        is_postable: true,
        is_active: true,
        report_group: '',
        depreciation_rate: '',
    });

    const buildTree = (flatAccounts, parentId = null) => {
        return flatAccounts
            .filter(acc => acc.parent_id === parentId)
            .sort((a, b) => a.code.localeCompare(b.code))
            .map(acc => ({
                ...acc,
                children: buildTree(flatAccounts, acc.id)
            }));
    };

    const treeData = useMemo(() => buildTree(accounts), [accounts]);

    useEffect(() => {
        if (data.parent_id && !editingAccount) {
            fetch(route('accounts.next-code') + '?parent_id=' + data.parent_id)
                .then(res => res.json())
                .then(json => {
                    if (json.code) {
                        setData('code', json.code);
                    }
                })
                .catch(err => console.error('Error fetching next code:', err));
        }
    }, [data.parent_id, editingAccount]);

    const handleEdit = (account) => {
        setEditingAccount(account);
        clearErrors();
        setData({
            parent_id: account.parent_id || '',
            code: account.code || '',
            name: account.name || '',
            account_type_id: account.account_type?.id || '',
            is_postable: account.is_postable,
            is_active: account.is_active,
            report_group: account.report_group || '',
            depreciation_rate: account.depreciation_rate || '',
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const handleAddChild = (parentAccount) => {
        handleCancelEdit();
        setData({
            ...data,
            parent_id: parentAccount.id,
            account_type_id: parentAccount.account_type?.id || '',
            report_group: parentAccount.report_group || '',
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const handleRemove = (account) => {
        if(confirm(`هل أنت متأكد من حذف الحساب "${account.name}"؟`)) {
            router.delete(route('accounts.destroy', account.id), {
                preserveScroll: true,
                onError: (err) => {
                    if (confirm('تنبيه: هذا الحساب مرتبط بعمليات محاسبية أو يحتوي على فروع. حذف الحساب سيؤدي لحذف كل ما هو مرتبط به بشكل نهائي. هل تريد الاستمرار في الحذف القسري؟')) {
                        router.delete(route('accounts.destroy', account.id) + '?force=true', {
                            preserveScroll: true,
                        });
                    }
                }
            });
        }
    };

    const handleCancelEdit = () => {
        setEditingAccount(null);
        clearErrors();
        reset();
        setData({
            parent_id: '',
            code: '',
            name: '',
            account_type_id: '',
            is_postable: true,
            is_active: true,
            report_group: '',
            depreciation_rate: '',
        });
    };

    const submit = (e) => {
        e.preventDefault();

        if (editingAccount) {
            put(route('accounts.update', editingAccount.id), {
                onSuccess: () => handleCancelEdit(),
            });
        } else {
            post(route('accounts.store'), {
                onSuccess: () => handleCancelEdit(),
            });
        }
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-semibold text-xl text-gray-800">شجرة الحسابات</h2>}>
            <Head title="شجرة الحسابات" />

            <div className="min-h-screen bg-gray-100" dir="rtl">
                <div className="mx-auto max-w-7xl p-6">
                    <div className="mb-8 flex justify-between items-center">
                        <div className="flex-1">
                            <h1 className="text-2xl font-black text-slate-900">شجرة الحسابات</h1>
                            <p className="mt-1 text-sm text-slate-500 font-medium">إدارة الهيكل التنظيمي والمالي للحسابات</p>
                        </div>
                        <div className="flex items-center gap-4">
                            {editingAccount && (
                                <button 
                                    onClick={handleCancelEdit}
                                    className="bg-rose-50 text-rose-600 px-5 py-2 rounded-xl text-sm font-bold hover:bg-rose-100 transition"
                                >
                                    إلغاء التعديل
                                </button>
                            )}
                            <BackButton />
                        </div>
                    </div>

                    {flash.success && (
                        <div className="mb-4 rounded-xl bg-green-100 px-4 py-3 text-green-800">
                            {flash.success}
                        </div>
                    )}

                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        {/* نموذج الإضافة / التعديل */}
                        <div className="rounded-2xl bg-white p-6 shadow-sm lg:col-span-1 h-fit sticky top-6">
                            <h2 className={`mb-4 text-lg font-semibold ${editingAccount ? 'text-blue-600' : 'text-gray-900'}`}>
                                {editingAccount ? '✏️ تعديل بيانات الحساب' : '➕ إضافة حساب جديد'}
                            </h2>

                            <form onSubmit={submit} className="space-y-4">
                                <div>
                                    <label className="mb-1 block text-sm font-medium">الحساب الأب</label>
                                    <select
                                        value={data.parent_id}
                                        onChange={(e) => {
                                            const pid = e.target.value;
                                            const parent = parents.find(p => p.id == pid);
                                            setData({
                                                ...data,
                                                parent_id: pid,
                                                account_type_id: parent?.account_type_id || '',
                                                report_group: parent?.report_group || '',
                                            });
                                        }}
                                        className="w-full rounded-xl border border-gray-300 px-3 py-2 bg-blue-50/50"
                                        required
                                    >
                                        <option value="">اختر الحساب الأب (إلزامي)</option>
                                        {parents.map((parent) => (
                                            <option key={parent.id} value={parent.id} disabled={editingAccount && parent.id === editingAccount.id}>
                                                {parent.code} - {parent.name}
                                            </option>
                                        ))}
                                    </select>
                                    <p className="mt-1 text-[10px] text-gray-400">لا يمكن إنشاء حسابات في المستوى الأول، يجب الاختيار من الحسابات الرئيسية الحالية.</p>
                                    {errors.parent_id && <div className="mt-1 text-sm text-red-600">{errors.parent_id}</div>}
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium">كود الحساب</label>
                                    <input
                                        type="text"
                                        value={data.code}
                                        onChange={(e) => setData('code', e.target.value)}
                                        className="w-full rounded-xl border border-gray-300 px-3 py-2"
                                    />
                                    {errors.code && <div className="mt-1 text-sm text-red-600">{errors.code}</div>}
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium">اسم الحساب</label>
                                    <input
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        className="w-full rounded-xl border border-gray-300 px-3 py-2"
                                    />
                                    {errors.name && <div className="mt-1 text-sm text-red-600">{errors.name}</div>}
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium">نوع الحساب (المحاسبي)</label>
                                    <select
                                        value={data.account_type_id}
                                        onChange={(e) => setData('account_type_id', e.target.value)}
                                        className="w-full rounded-xl border border-gray-300 px-3 py-2"
                                    >
                                        <option value="">اختر نوع الحساب</option>
                                        {accountTypes.map((type) => (
                                            <option key={type.id} value={type.id}>
                                                {type.name}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.account_type_id && (
                                        <div className="mt-1 text-sm text-red-600">{errors.account_type_id}</div>
                                    )}
                                </div>

                                <div>
                                    <label className="mb-1 block text-sm font-medium">المجموعة المالية</label>
                                    <input
                                        type="text"
                                        value={data.report_group}
                                        onChange={(e) => setData('report_group', e.target.value)}
                                        className="w-full rounded-xl border border-gray-300 px-3 py-2"
                                        placeholder="مثل current_assets"
                                    />
                                </div>

                                {data.parent_id && parents.find(p => p.id == data.parent_id)?.code?.startsWith('12') && !parents.find(p => p.id == data.parent_id)?.code?.startsWith('124') && (
                                    <div>
                                        <label className="mb-1 block text-sm font-medium">نسبة الإهلاك (إن وجد)</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            value={data.depreciation_rate}
                                            onChange={(e) => setData('depreciation_rate', e.target.value)}
                                            className="w-full rounded-xl border border-gray-300 px-3 py-2"
                                            placeholder="مثال: 15 للإهلاك 15%"
                                        />
                                        {errors.depreciation_rate && <div className="mt-1 text-sm text-red-600">{errors.depreciation_rate}</div>}
                                    </div>
                                )}

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="mb-1 block text-sm font-medium">طبيعة الحساب</label>
                                        <select
                                            value={data.is_postable ? '1' : '0'}
                                            onChange={(e) => setData('is_postable', e.target.value === '1')}
                                            className="w-full rounded-xl border border-gray-300 px-3 py-2 bg-yellow-50 focus:ring-yellow-500"
                                        >
                                            <option value="1">تشغيلي</option>
                                            <option value="0">تجميعي (مجلد)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label className="mb-1 block text-sm font-medium">الحالة</label>
                                        <select
                                            value={data.is_active ? '1' : '0'}
                                            onChange={(e) => setData('is_active', e.target.value === '1')}
                                            className="w-full rounded-xl border border-gray-300 px-3 py-2"
                                        >
                                            <option value="1">نشط</option>
                                            <option value="0">غير نشط</option>
                                        </select>
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className={`w-full rounded-xl px-4 py-3 text-white disabled:opacity-50 transition ${editingAccount ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-800 hover:bg-gray-900'}`}
                                >
                                    {processing ? 'جاري الحفظ...' : (editingAccount ? 'حفظ التعديلات' : 'إضافة الحساب')}
                                </button>
                            </form>
                        </div>

                        {/* الشجرة */}
                        <div className="rounded-2xl bg-white p-0 shadow-sm lg:col-span-2 overflow-hidden border border-gray-100">
                            
                            <div className="overflow-x-auto">
                                <table className="min-w-full">
                                    <thead>
                                        <tr className="bg-gray-50 text-right border-b border-gray-200">
                                            <th className="px-4 py-3 text-sm font-semibold text-gray-700 w-1/2">تسلسل الحساب</th>
                                            <th className="px-4 py-3 text-sm font-semibold text-gray-700">النوع</th>
                                            <th className="px-4 py-3 text-sm font-semibold text-gray-700">الطبيعة</th>
                                            <th className="px-4 py-3 text-sm font-semibold text-gray-700">الحالة</th>
                                            <th className="px-4 py-3 text-sm font-semibold text-gray-700">إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {treeData.length > 0 ? treeData.map(node => (
                                            <AccountRow 
                                                key={node.id} 
                                                account={node} 
                                                level={0} 
                                                onEdit={handleEdit} 
                                                onAddChild={handleAddChild}
                                                onRemove={handleRemove}
                                            />
                                        )) : (
                                            <tr>
                                                <td colSpan="5" className="px-4 py-8 text-center text-gray-500">
                                                    لا توجد حسابات بعد. قم بإضافة حسابك الأول.
                                                </td>
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
