import { Head, useForm, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';
import PageHeader from '@/Components/PageHeader';
import DataTable from '@/Components/DataTable';
import Modal from '@/Components/Modal';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';

export default function Users({ auth, users, errors: propErrors, flash }) {
    const [showModal, setShowModal] = useState(false);
    const [editingUser, setEditingUser] = useState(null);

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        name: '',
        email: '',
        password: '',
        role: 'accountant',
    });

    const openCreateModal = () => {
        setEditingUser(null);
        clearErrors();
        reset();
        setShowModal(true);
    };

    const openEditModal = (user) => {
        setEditingUser(user);
        clearErrors();
        setData({
            name: user.name,
            email: user.email,
            password: '', 
            role: user.role,
        });
        setShowModal(true);
    };

    const submit = (e) => {
        e.preventDefault();
        if (editingUser) {
            put(route('users.update', editingUser.id), {
                onSuccess: () => {
                    setShowModal(false);
                    reset();
                }
            });
        } else {
            post(route('users.store'), {
                onSuccess: () => {
                    setShowModal(false);
                    reset();
                }
            });
        }
    };

    const deleteUser = (user) => {
        if (confirm(`هل أنت متأكد من حذف المستخدم ${user.name}؟`)) {
            router.delete(route('users.destroy', user.id));
        }
    };

    const getRoleBadge = (role) => {
        switch (role) {
            case 'admin':
                return <span className="bg-rose-100 text-rose-700 px-3 py-1 rounded-full text-xs font-bold">مدير النظام</span>;
            case 'accountant':
                return <span className="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">محاسب</span>;
            case 'storekeeper':
                return <span className="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold">أمين مستودع</span>;
            default:
                return <span className="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold">{role}</span>;
        }
    };

    const columns = [
        { 
            key: 'name', 
            label: 'الاسم بالكامل', 
            render: (row) => <span className="font-bold text-slate-900">{row.name}</span> 
        },
        { 
            key: 'email', 
            label: 'البريد الإلكتروني', 
            render: (row) => <span className="text-slate-600 font-mono">{row.email}</span> 
        },
        { 
            key: 'role', 
            label: 'الدور والترخيص', 
            render: (row) => getRoleBadge(row.role) 
        },
        { 
            key: 'actions', 
            label: 'الإجراءات', 
            align: 'center', 
            render: (row) => (
                <div className="flex justify-center gap-2">
                    <button
                        onClick={() => openEditModal(row)}
                        className="px-3 py-1.5 text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-all"
                    >
                        تعديل
                    </button>
                    {row.id !== auth.user.id && (
                        <button
                            onClick={() => deleteUser(row)}
                            className="px-3 py-1.5 text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg transition-all"
                        >
                            حذف
                        </button>
                    )}
                </div>
            )
        }
    ];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="إدارة المستخدمين والصلاحيات" />

            <div className="min-h-screen bg-slate-50 py-6" dir="rtl">
                <div className="mx-auto max-w-[1400px] px-4">
                    
                    <PageHeader 
                        title="إدارة المستخدمين والصلاحيات" 
                        subtitle="إضافة وتعديل بيانات مستخدمي النظام وتحديد صلاحياتهم"
                        actions={[
                            <button 
                                onClick={openCreateModal}
                                className="inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold rounded-lg transition-colors"
                            >
                                <span className="ml-2">➕</span> إضافة مستخدم جديد
                            </button>
                        ]}
                    />

                    {flash?.success && (
                        <div className="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-6 py-4 rounded-xl font-bold shadow-sm">
                            {flash.success}
                        </div>
                    )}
                    
                    {propErrors?.message && (
                        <div className="mb-6 bg-rose-50 border border-rose-200 text-rose-800 px-6 py-4 rounded-xl font-bold shadow-sm">
                            {propErrors.message}
                        </div>
                    )}

                    <DataTable 
                        columns={columns} 
                        data={users} 
                    />
                </div>
            </div>

            {/* Create/Edit Modal (Matching Add Contact style in SettleAdvance) */}
            <Modal show={showModal} onClose={() => setShowModal(false)} maxWidth="md">
                <form onSubmit={submit} className="p-6" dir="rtl">
                    <h2 className="text-lg font-bold text-gray-900 mb-4 pb-2 border-b">
                        {editingUser ? `تعديل بيانات المستخدم: ${editingUser.name}` : 'إضافة مستخدم جديد'}
                    </h2>
                    
                    <div className="space-y-4">
                        <div>
                            <InputLabel value="الاسم بالكامل *" />
                            <TextInput 
                                className="mt-1 block w-full" 
                                value={data.name} 
                                onChange={e => setData('name', e.target.value)} 
                                required 
                                placeholder="مثال: أحمد العتيبي"
                            />
                            <InputError message={errors.name} className="mt-2" />
                        </div>

                        <div>
                            <InputLabel value="البريد الإلكتروني *" />
                            <TextInput 
                                type="email"
                                className="mt-1 block w-full font-mono" 
                                value={data.email} 
                                onChange={e => setData('email', e.target.value)} 
                                required 
                                placeholder="example@domain.com"
                            />
                            <InputError message={errors.email} className="mt-2" />
                        </div>

                        <div>
                            <InputLabel value={`كلمة المرور ${editingUser ? '(اتركها فارغة لعدم التغيير)' : '*'}`} />
                            <TextInput 
                                type="password"
                                className="mt-1 block w-full" 
                                value={data.password} 
                                onChange={e => setData('password', e.target.value)} 
                                required={!editingUser} 
                                placeholder="••••••••"
                            />
                            <InputError message={errors.password} className="mt-2" />
                        </div>

                        <div>
                            <InputLabel value="الدور والصلاحية *" />
                            <select 
                                required 
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" 
                                value={data.role} 
                                onChange={e => setData('role', e.target.value)}
                            >
                                <option value="admin">مدير النظام (كامل الصلاحيات)</option>
                                <option value="accountant">محاسب (إدارة الفواتير والقيود والتقارير)</option>
                                <option value="storekeeper">أمين مستودع (المستودع والمخزون فقط)</option>
                            </select>
                            <InputError message={errors.role} className="mt-2" />
                        </div>
                    </div>

                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton onClick={() => setShowModal(false)}>
                            إلغاء
                        </SecondaryButton>
                        <PrimaryButton disabled={processing}>
                            {editingUser ? 'حفظ التغييرات' : 'إنشاء المستخدم'}
                        </PrimaryButton>
                    </div>
                </form>
            </Modal>
        </AuthenticatedLayout>
    );
}
