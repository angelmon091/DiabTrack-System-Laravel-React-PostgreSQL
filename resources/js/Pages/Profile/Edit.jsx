import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

import Alert from '../../Components/Alert';
import FormError from '../../Components/FormError';
import FormInput from '../../Components/FormInput';
import FormSelect from '../../Components/FormSelect';
import Modal from '../../Components/Modal';
import SubmitButton from '../../Components/SubmitButton';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout';

export default function Edit({ profile, updateUrl, passwordUrl, destroyUrl, linkedUsers, pendingEmailChange, timezones }) {
    const { errors: pageErrors = {} } = usePage().props;
    const profileForm = useForm({ name: profile.name, email: profile.email, timezone: profile.timezone, current_password: '', avatar: null });
    const passwordForm = useForm('updatePassword', { current_password: '', password: '', password_confirmation: '' });
    const deleteForm = useForm('userDeletion', { password: '' });
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [unlinkUser, setUnlinkUser] = useState(null);
    const emailChanged = profileForm.data.email !== profile.email;

    const passwordErrors = pageErrors.updatePassword ?? passwordForm.errors;
    const deletionErrors = pageErrors.userDeletion ?? deleteForm.errors;

    useEffect(() => { if (deletionErrors.password) setDeleteOpen(true); }, [deletionErrors.password]);

    const submitProfile = (event) => { event.preventDefault(); profileForm.patch(updateUrl, { forceFormData: true, preserveScroll: true, onSuccess: () => profileForm.setData('current_password', '') }); };
    const submitPassword = (event) => { event.preventDefault(); passwordForm.put(passwordUrl, { preserveScroll: true, onError: (errors) => passwordForm.setError(errors.updatePassword ?? errors), onSuccess: () => passwordForm.reset() }); };
    const deleteAccount = () => deleteForm.delete(destroyUrl, { preserveScroll: true, onError: (errors) => deleteForm.setError(errors.userDeletion ?? errors) });
    const unlink = () => router.delete(unlinkUser.unlinkUrl, { preserveScroll: true, onFinish: () => setUnlinkUser(null) });

    return <AuthenticatedLayout>
        <Head title="Configuración de perfil" />
        <section className="mx-auto max-w-4xl space-y-6" data-testid="profile-edit">
            <div><h1 className="text-3xl font-extrabold text-slate-900">Configuración de <span className="text-cyan-600">Cuenta</span></h1><p className="mt-2 text-slate-500">Gestiona tu información personal y seguridad de forma segura</p></div>
            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><h2 className="text-xl font-bold text-slate-900">Información del perfil</h2><p className="mt-1 text-sm text-slate-500">Actualiza los datos básicos de tu cuenta y tu dirección de correo electrónico.</p>
                <form onSubmit={submitProfile} className="mt-6 space-y-5" noValidate>
                    <div className="flex items-center gap-4"><div className="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full bg-cyan-600 text-xl font-bold text-white">{profile.avatarUrl ? <img src={profile.avatarUrl} alt="Avatar actual" className="h-full w-full object-cover" /> : profile.name.charAt(0).toUpperCase()}</div><div className="flex-1"><label htmlFor="avatar" className="mb-1.5 block text-sm font-semibold text-slate-500">Foto de perfil</label><input id="avatar" type="file" accept="image/jpeg,image/png,image/webp" onChange={(event) => profileForm.setData('avatar', event.target.files[0] ?? null)} className="block w-full text-sm text-slate-600" /><p className="mt-1 text-xs text-slate-400">Recomendado: 150x150. Máximo: 5 MB.</p><FormError message={profileForm.errors.avatar} /></div></div>
                    <FormInput id="profile_name" label="Nombre completo" maxLength={255} value={profileForm.data.name} onChange={(event) => profileForm.setData('name', event.target.value)} error={profileForm.errors.name} required />
                    <FormInput id="profile_email" type="email" label="Correo electrónico" maxLength={255} value={profileForm.data.email} onChange={(event) => profileForm.setData('email', event.target.value)} error={profileForm.errors.email} required />
                    {(emailChanged || profileForm.errors.current_password) && <FormInput id="profile_current_password" type="password" label="Confirma con tu contraseña actual" placeholder="Requerido para cambiar el correo" value={profileForm.data.current_password} onChange={(event) => profileForm.setData('current_password', event.target.value)} error={profileForm.errors.current_password} />}
                    {pendingEmailChange && <Alert>Solicitud pendiente: {pendingEmailChange.newEmail}. Confirma el cambio desde el enlace enviado a ese correo.</Alert>}
                    <FormSelect id="timezone" label="Zona horaria" value={profileForm.data.timezone} onChange={(event) => profileForm.setData('timezone', event.target.value)} error={profileForm.errors.timezone}>{timezones.map((timezone) => <option key={timezone.value} value={timezone.value}>{timezone.label}</option>)}</FormSelect>
                    <SubmitButton processing={profileForm.processing}>Guardar cambios</SubmitButton>
                </form>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><h2 className="text-xl font-bold text-slate-900">Actualizar contraseña</h2><p className="mt-1 text-sm text-slate-500">Utiliza una contraseña larga y aleatoria para mantener la seguridad.</p><form onSubmit={submitPassword} className="mt-6 space-y-4" noValidate><FormInput id="password_current" type="password" label="Contraseña actual" value={passwordForm.data.current_password} onChange={(event) => passwordForm.setData('current_password', event.target.value)} error={passwordErrors.current_password} /><FormInput id="password_new" type="password" label="Nueva contraseña" value={passwordForm.data.password} onChange={(event) => passwordForm.setData('password', event.target.value)} error={passwordErrors.password} /><FormInput id="password_confirmation" type="password" label="Confirmar contraseña" value={passwordForm.data.password_confirmation} onChange={(event) => passwordForm.setData('password_confirmation', event.target.value)} error={passwordErrors.password_confirmation} /><SubmitButton processing={passwordForm.processing}>Guardar contraseña</SubmitButton></form></section>

            {linkedUsers.length > 0 && <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><h2 className="text-xl font-bold text-slate-900">Personas vinculadas</h2><p className="mt-1 text-sm text-slate-500">Médicos y cuidadores con acceso a tus datos.</p><div className="mt-5 space-y-3">{linkedUsers.map((user) => <div key={user.id} className="flex items-center gap-3 rounded-2xl bg-slate-50 p-4"><div className="flex h-11 w-11 items-center justify-center overflow-hidden rounded-full bg-cyan-100 font-bold text-cyan-700">{user.avatarUrl ? <img src={user.avatarUrl} alt={user.name} className="h-full w-full object-cover" /> : user.name.charAt(0)}</div><div className="min-w-0 flex-1"><strong className="block text-sm text-slate-800">{user.name}</strong><span className="text-xs text-slate-500">{user.roleLabel} · {user.email}</span></div><button type="button" onClick={() => setUnlinkUser(user)} className="rounded-full border border-red-200 px-3 py-2 text-xs font-semibold text-red-600">Desvincular</button></div>)}</div></section>}

            <section className="rounded-3xl border border-red-200 bg-white p-6 shadow-sm"><h2 className="text-xl font-bold text-slate-900">Eliminar cuenta</h2><p className="mt-1 text-sm text-slate-500">Esta acción elimina permanentemente la cuenta y sus datos asociados.</p><button type="button" onClick={() => setDeleteOpen(true)} className="mt-5 rounded-full border border-red-300 px-4 py-2 text-sm font-semibold text-red-600">Eliminar mi cuenta</button></section>
        </section>

        <Modal open={Boolean(unlinkUser)} title="Desvincular persona" onClose={() => setUnlinkUser(null)} actions={<><button type="button" onClick={() => setUnlinkUser(null)} className="rounded-xl border px-4 py-2">Cancelar</button><button type="button" onClick={unlink} className="rounded-xl bg-red-600 px-4 py-2 text-white">Desvincular</button></>}>{unlinkUser && <p>¿Desvincular a {unlinkUser.name}? Ya no tendrá acceso a tus datos.</p>}</Modal>
        <Modal open={deleteOpen} title="¿Eliminar permanentemente tu cuenta?" onClose={() => setDeleteOpen(false)} actions={<><button type="button" onClick={() => setDeleteOpen(false)} className="rounded-xl border px-4 py-2">Cancelar</button><button type="button" onClick={deleteAccount} disabled={deleteForm.processing} className="rounded-xl bg-red-600 px-4 py-2 text-white disabled:opacity-60">Eliminar de forma permanente</button></>}><p className="mb-4 text-sm text-slate-600">Introduce tu contraseña para confirmar esta acción irreversible.</p><FormInput id="delete_password" type="password" label="Contraseña de confirmación" value={deleteForm.data.password} onChange={(event) => deleteForm.setData('password', event.target.value)} error={deletionErrors.password} /></Modal>
    </AuthenticatedLayout>;
}
