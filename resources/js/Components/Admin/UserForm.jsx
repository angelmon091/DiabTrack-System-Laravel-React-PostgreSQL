import Checkbox from '../../Components/Checkbox';
import FormError from '../../Components/FormError';
import FormInput from '../../Components/FormInput';
import SubmitButton from '../../Components/SubmitButton';

export default function UserForm({ form, roles, indexUrl, submitLabel, passwordRequired = false, selfEditing = false }) {
    function toggleRole(roleId) {
        form.setData('roles', form.data.roles.includes(roleId)
            ? form.data.roles.filter((id) => id !== roleId)
            : [...form.data.roles, roleId]);
    }
    return <div className="mx-auto max-w-4xl rounded-3xl border border-slate-100 bg-white p-6 shadow-sm sm:p-8">
        <div className="grid gap-5 md:grid-cols-2">
            <FormInput id="name" name="name" label="Nombre completo" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} error={form.errors.name} required />
            <FormInput id="email" name="email" type="email" label="Correo electrónico" value={form.data.email} onChange={(event) => form.setData('email', event.target.value)} error={form.errors.email} required />
            <FormInput id="password" name="password" type="password" label={passwordRequired ? 'Contraseña de acceso' : 'Nueva contraseña'} value={form.data.password} onChange={(event) => form.setData('password', event.target.value)} error={form.errors.password} required={passwordRequired} autoComplete="new-password" />
            <FormInput id="password_confirmation" name="password_confirmation" type="password" label={passwordRequired ? 'Confirmar contraseña' : 'Confirmar nueva contraseña'} value={form.data.password_confirmation} onChange={(event) => form.setData('password_confirmation', event.target.value)} required={passwordRequired} autoComplete="new-password" />
        </div>
        {!passwordRequired && <p className="mt-3 rounded-2xl bg-blue-50 p-4 text-sm text-blue-700">Deja ambos campos de contraseña vacíos para conservar la contraseña actual.</p>}
        <div className="mt-8 border-t pt-6"><h2 className="text-lg font-bold text-slate-900">Configuración de privilegios</h2><div className="mt-4 rounded-2xl border border-slate-200 p-4"><Checkbox id="is_admin" label="Acceso administrativo completo" checked={form.data.is_admin} disabled={selfEditing} onChange={(event) => form.setData('is_admin', event.target.checked)} /><p className="mt-2 text-sm text-slate-500">{selfEditing ? 'Por seguridad, no puedes revocar tus propios permisos administrativos.' : 'Permite gestionar configuraciones globales del sistema DiabTrack.'}</p></div></div>
        <fieldset className="mt-6"><legend className="text-sm font-bold text-slate-700">Roles del sistema</legend><div className="mt-3 grid gap-3 md:grid-cols-3">{roles.length ? roles.map((role) => <label key={role.id} htmlFor={`role_${role.id}`} className={`cursor-pointer rounded-2xl border p-4 ${form.data.roles.includes(role.id) ? 'border-cyan-500 bg-cyan-50' : 'border-slate-200'}`}><input id={`role_${role.id}`} type="checkbox" checked={form.data.roles.includes(role.id)} onChange={() => toggleRole(role.id)} className="mr-2 rounded border-cyan-500/30 text-cyan-600" /><strong className="capitalize text-slate-800">{role.name}</strong>{role.description && <span className="mt-1 block text-xs text-slate-500">{role.description}</span>}</label>) : <p className="text-sm italic text-slate-500">No hay roles adicionales definidos.</p>}</div><FormError message={form.errors.roles} /></fieldset>
        <div className="mt-8 flex flex-col-reverse gap-3 border-t pt-6 sm:flex-row sm:justify-end"><a href={indexUrl} className="rounded-2xl border px-5 py-3 text-center text-sm font-semibold text-slate-600">Cancelar</a><SubmitButton processing={form.processing} className="sm:w-auto sm:px-8">{submitLabel}</SubmitButton></div>
    </div>;
}
