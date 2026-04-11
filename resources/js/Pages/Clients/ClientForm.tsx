import { Link } from '@inertiajs/react';

interface FormState {
    type: 'individual' | 'business';
    name: string;
    trade_name: string;
    nif: string;
    email: string;
    phone: string;
    website: string;
    address: string;
    city: string;
    province: string;
    postal_code: string;
    country: string;
    vat_exempt: boolean;
    irpf_applicable: boolean;
    irpf_rate: string;
    notes: string;
}

interface Props {
    form: {
        data: FormState;
        setData: (key: keyof FormState, value: string | boolean) => void;
        errors: Partial<Record<keyof FormState, string>>;
        processing: boolean;
    };
    onSubmit: () => void;
    submitLabel: string;
    backHref: string;
}

export default function ClientForm({ form, onSubmit, submitLabel, backHref }: Props) {
    const { data, setData, errors, processing } = form;

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        onSubmit();
    }

    return (
        <form onSubmit={handleSubmit} className="max-w-3xl">
            <div className="bg-white rounded-xl border shadow-sm divide-y">
                {/* Tipo y datos básicos */}
                <div className="p-6 space-y-5">
                    <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide">Datos básicos</h2>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">Tipo de cliente</label>
                        <div className="flex gap-4">
                            {['business', 'individual'].map((t) => (
                                <label key={t} className="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="radio"
                                        name="type"
                                        value={t}
                                        checked={data.type === t}
                                        onChange={() => setData('type', t as 'individual' | 'business')}
                                        className="text-[#1e3a5f] focus:ring-[#1e3a5f]"
                                    />
                                    <span className="text-sm">{t === 'business' ? 'Empresa' : 'Particular'}</span>
                                </label>
                            ))}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <Field label="Nombre / Razón social *" error={errors.name}>
                            <input
                                type="text"
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                                className={inputClass(!!errors.name)}
                                required
                            />
                        </Field>

                        <Field label="Nombre comercial" error={errors.trade_name}>
                            <input
                                type="text"
                                value={data.trade_name}
                                onChange={e => setData('trade_name', e.target.value)}
                                className={inputClass(false)}
                            />
                        </Field>

                        <Field label={data.type === 'business' ? 'CIF / NIF' : 'NIF / NIE'} error={errors.nif}>
                            <input
                                type="text"
                                value={data.nif}
                                onChange={e => setData('nif', e.target.value.toUpperCase())}
                                className={inputClass(!!errors.nif)}
                                placeholder="B12345678"
                            />
                        </Field>

                        <Field label="Email" error={errors.email}>
                            <input
                                type="email"
                                value={data.email}
                                onChange={e => setData('email', e.target.value)}
                                className={inputClass(!!errors.email)}
                            />
                        </Field>

                        <Field label="Teléfono" error={errors.phone}>
                            <input
                                type="tel"
                                value={data.phone}
                                onChange={e => setData('phone', e.target.value)}
                                className={inputClass(false)}
                            />
                        </Field>

                        <Field label="Web" error={errors.website}>
                            <input
                                type="url"
                                value={data.website}
                                onChange={e => setData('website', e.target.value)}
                                className={inputClass(false)}
                                placeholder="https://"
                            />
                        </Field>
                    </div>
                </div>

                {/* Dirección */}
                <div className="p-6 space-y-4">
                    <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide">Dirección fiscal</h2>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div className="sm:col-span-2">
                            <Field label="Dirección" error={errors.address}>
                                <input
                                    type="text"
                                    value={data.address}
                                    onChange={e => setData('address', e.target.value)}
                                    className={inputClass(false)}
                                    placeholder="Calle, número, piso..."
                                />
                            </Field>
                        </div>

                        <Field label="Ciudad" error={errors.city}>
                            <input
                                type="text"
                                value={data.city}
                                onChange={e => setData('city', e.target.value)}
                                className={inputClass(false)}
                            />
                        </Field>

                        <Field label="Provincia" error={errors.province}>
                            <input
                                type="text"
                                value={data.province}
                                onChange={e => setData('province', e.target.value)}
                                className={inputClass(false)}
                            />
                        </Field>

                        <Field label="Código postal" error={errors.postal_code}>
                            <input
                                type="text"
                                value={data.postal_code}
                                onChange={e => setData('postal_code', e.target.value)}
                                className={inputClass(false)}
                                maxLength={10}
                            />
                        </Field>

                        <Field label="País" error={errors.country}>
                            <input
                                type="text"
                                value={data.country}
                                onChange={e => setData('country', e.target.value.toUpperCase())}
                                className={inputClass(false)}
                                maxLength={2}
                                placeholder="ES"
                            />
                        </Field>
                    </div>
                </div>

                {/* Configuración fiscal */}
                <div className="p-6 space-y-4">
                    <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide">Configuración fiscal</h2>

                    <label className="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            checked={data.irpf_applicable}
                            onChange={e => setData('irpf_applicable', e.target.checked)}
                            className="w-4 h-4 rounded text-[#1e3a5f] focus:ring-[#1e3a5f]"
                        />
                        <span className="text-sm text-gray-700">
                            Aplicar retención IRPF en facturas a este cliente
                        </span>
                    </label>

                    {data.irpf_applicable && (
                        <Field label="% Retención IRPF" error={errors.irpf_rate}>
                            <select
                                value={data.irpf_rate}
                                onChange={e => setData('irpf_rate', e.target.value)}
                                className={inputClass(false)}
                            >
                                <option value="">Usar % de empresa</option>
                                <option value="7">7% (nuevo autónomo, 3 primeros años)</option>
                                <option value="15">15% (general)</option>
                                <option value="19">19%</option>
                            </select>
                        </Field>
                    )}

                    <label className="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            checked={data.vat_exempt}
                            onChange={e => setData('vat_exempt', e.target.checked)}
                            className="w-4 h-4 rounded text-[#1e3a5f] focus:ring-[#1e3a5f]"
                        />
                        <span className="text-sm text-gray-700">
                            Exento de IVA (operación intracomunitaria / exportación)
                        </span>
                    </label>
                </div>

                {/* Notas */}
                <div className="p-6">
                    <Field label="Notas internas" error={errors.notes}>
                        <textarea
                            value={data.notes}
                            onChange={e => setData('notes', e.target.value)}
                            rows={3}
                            className={inputClass(false) + ' resize-none'}
                            placeholder="Notas sobre este cliente (solo visibles para ti)..."
                        />
                    </Field>
                </div>
            </div>

            {/* Actions */}
            <div className="flex items-center gap-3 mt-6">
                <button
                    type="submit"
                    disabled={processing}
                    className="bg-[#1e3a5f] hover:bg-[#152843] text-white px-6 py-2.5 rounded-lg text-sm font-medium transition-colors disabled:opacity-50"
                >
                    {processing ? 'Guardando...' : submitLabel}
                </button>
                <Link href={backHref} className="text-sm text-gray-500 hover:text-gray-700">
                    Cancelar
                </Link>
            </div>
        </form>
    );
}

function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) {
    return (
        <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>
            {children}
            {error && <p className="text-red-600 text-xs mt-1">{error}</p>}
        </div>
    );
}

function inputClass(hasError: boolean): string {
    return `w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] ${
        hasError ? 'border-red-400 bg-red-50' : 'border-gray-300'
    }`;
}
