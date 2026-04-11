import { Head, useForm, router } from '@inertiajs/react';
import { useState, useRef } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import type { Company } from '@/types';

interface Props { company: Company; }

const templates = [
    {
        id: 'classic' as const,
        label: 'Clásica',
        desc: 'Encabezado sólido, diseño corporativo tradicional',
        preview: '▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓\n□ Logo          FACTURA\n▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓\n Emisor     │  Cliente\n─────────────────────\n Desc.  Cant. Precio\n─────────────────────\n                Total',
    },
    {
        id: 'modern' as const,
        label: 'Moderna',
        desc: 'Banda lateral de color, tipografía limpia',
        preview: '║ │ Empresa       FACTURA\n║ │ ─────────────────────\n║ │ Emisor\n║ │ ─────────────────────\n║ │ Desc.    Cant.  Precio\n║ │ ─────────────────────\n║ │               Total ▓',
    },
    {
        id: 'minimal' as const,
        label: 'Minimalista',
        desc: 'Diseño limpio en blanco, líneas sutiles',
        preview: '  FACTURA  F2025-001\n  ─────────────────\n  Emisor\n\n  Cliente\n  ─────────────────\n  Desc.    Precio\n  ─────────────────\n            TOTAL €',
    },
];

export default function SettingsIndex({ company }: Props) {
    const [activeTab, setActiveTab] = useState<'fiscal' | 'factura' | 'logo' | 'series'>('fiscal');
    const fileInputRef = useRef<HTMLInputElement>(null);

    const { data, setData, post, processing, errors } = useForm({
        name:                  company.name,
        trade_name:            company.trade_name ?? '',
        nif:                   company.nif ?? '',
        address:               company.address ?? '',
        city:                  company.city ?? '',
        province:              company.province ?? '',
        postal_code:           company.postal_code ?? '',
        country:               company.country ?? 'ES',
        phone:                 company.phone ?? '',
        email:                 company.email ?? '',
        website:               company.website ?? '',
        iban:                  company.iban ?? '',
        swift:                 company.swift ?? '',
        show_bank_details:     company.show_bank_details,
        invoice_series:        company.invoice_series,
        rectification_series:  company.rectification_series ?? 'R',
        quote_series:          company.quote_series ?? 'P',
        default_vat_rate:      String(company.default_vat_rate),
        irpf_applicable:       company.irpf_applicable,
        irpf_rate:             String(company.irpf_rate),
        invoice_template:      company.invoice_template,
        primary_color:         company.primary_color,
        accent_color:          company.accent_color,
        invoice_footer_text:   company.invoice_footer_text ?? '',
        invoice_header_notes:  company.invoice_header_notes ?? '',
    });

    function handleSave(e: React.FormEvent) {
        e.preventDefault();
        post('/ajustes', { preserveScroll: true });
    }

    function uploadLogo(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0];
        if (!file) return;
        const fd = new FormData();
        fd.append('logo', file);
        router.post('/ajustes/logo', fd, { preserveScroll: true });
    }

    function deleteLogo() {
        if (!confirm('¿Eliminar el logo actual?')) return;
        router.delete('/ajustes/logo', { preserveScroll: true });
    }

    const tabs = [
        { id: 'fiscal',   label: 'Datos fiscales' },
        { id: 'factura',  label: 'Factura PDF' },
        { id: 'logo',     label: 'Logo y marca' },
        { id: 'series',   label: 'Series y fiscal' },
    ] as const;

    return (
        <AppLayout title="Ajustes">
            <Head title="Ajustes" />

            <form onSubmit={handleSave} className="max-w-3xl">
                {/* Tabs */}
                <div className="flex border-b mb-6 gap-1">
                    {tabs.map(t => (
                        <button
                            key={t.id}
                            type="button"
                            onClick={() => setActiveTab(t.id)}
                            className={`px-4 py-2.5 text-sm font-medium rounded-t-lg transition-colors ${
                                activeTab === t.id
                                    ? 'bg-white border border-b-white -mb-px text-[#1e3a5f]'
                                    : 'text-gray-500 hover:text-gray-700'
                            }`}
                        >
                            {t.label}
                        </button>
                    ))}
                </div>

                {/* ── Tab: Datos fiscales ─────────────────────────────────── */}
                {activeTab === 'fiscal' && (
                    <div className="bg-white rounded-xl border shadow-sm p-6 space-y-5">
                        <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide">Datos de la empresa / autónomo</h2>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <Field label="Nombre / Razón social *" error={errors.name}>
                                <input type="text" value={data.name}
                                    onChange={e => setData('name', e.target.value)}
                                    className={inp(!!errors.name)} required />
                            </Field>

                            <Field label="Nombre comercial" error={errors.trade_name}>
                                <input type="text" value={data.trade_name}
                                    onChange={e => setData('trade_name', e.target.value)}
                                    className={inp(false)} />
                            </Field>

                            <Field label="NIF / CIF *" error={errors.nif}>
                                <input type="text" value={data.nif}
                                    onChange={e => setData('nif', e.target.value.toUpperCase())}
                                    className={inp(!!errors.nif)} placeholder="12345678A" />
                            </Field>

                            <Field label="Email" error={errors.email}>
                                <input type="email" value={data.email}
                                    onChange={e => setData('email', e.target.value)}
                                    className={inp(!!errors.email)} />
                            </Field>

                            <Field label="Teléfono" error={errors.phone}>
                                <input type="tel" value={data.phone}
                                    onChange={e => setData('phone', e.target.value)}
                                    className={inp(false)} />
                            </Field>

                            <Field label="Web" error={errors.website}>
                                <input type="url" value={data.website}
                                    onChange={e => setData('website', e.target.value)}
                                    className={inp(false)} placeholder="https://" />
                            </Field>

                            <div className="sm:col-span-2">
                                <Field label="Dirección" error={errors.address}>
                                    <input type="text" value={data.address}
                                        onChange={e => setData('address', e.target.value)}
                                        className={inp(false)} />
                                </Field>
                            </div>

                            <Field label="Ciudad" error={errors.city}>
                                <input type="text" value={data.city}
                                    onChange={e => setData('city', e.target.value)}
                                    className={inp(false)} />
                            </Field>

                            <Field label="Provincia" error={errors.province}>
                                <input type="text" value={data.province}
                                    onChange={e => setData('province', e.target.value)}
                                    className={inp(false)} />
                            </Field>

                            <Field label="Código postal" error={errors.postal_code}>
                                <input type="text" value={data.postal_code}
                                    onChange={e => setData('postal_code', e.target.value)}
                                    className={inp(false)} maxLength={10} />
                            </Field>
                        </div>

                        <div className="pt-4 border-t space-y-4">
                            <h3 className="text-sm font-semibold text-gray-700">Datos bancarios</h3>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <Field label="IBAN" error={errors.iban}>
                                    <input type="text" value={data.iban}
                                        onChange={e => setData('iban', e.target.value.toUpperCase())}
                                        className={inp(false)} placeholder="ES91 2100 0418..." />
                                </Field>
                                <Field label="SWIFT/BIC" error={errors.swift}>
                                    <input type="text" value={data.swift}
                                        onChange={e => setData('swift', e.target.value.toUpperCase())}
                                        className={inp(false)} />
                                </Field>
                            </div>
                            <label className="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" checked={data.show_bank_details}
                                    onChange={e => setData('show_bank_details', e.target.checked)}
                                    className="w-4 h-4 rounded text-[#1e3a5f]" />
                                <span className="text-sm text-gray-700">Mostrar IBAN en las facturas PDF</span>
                            </label>
                        </div>
                    </div>
                )}

                {/* ── Tab: Factura PDF ─────────────────────────────────────── */}
                {activeTab === 'factura' && (
                    <div className="bg-white rounded-xl border shadow-sm p-6 space-y-6">
                        <div>
                            <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Plantilla de factura</h2>
                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                {templates.map(t => (
                                    <label key={t.id} className={`cursor-pointer rounded-xl border-2 p-4 transition-all ${
                                        data.invoice_template === t.id
                                            ? 'border-[#1e3a5f] bg-blue-50'
                                            : 'border-gray-200 hover:border-gray-300'
                                    }`}>
                                        <input type="radio" name="invoice_template" value={t.id}
                                            checked={data.invoice_template === t.id}
                                            onChange={() => setData('invoice_template', t.id)}
                                            className="sr-only" />
                                        <pre className="text-xs text-gray-600 font-mono leading-relaxed mb-3 overflow-hidden">
                                            {t.preview}
                                        </pre>
                                        <div className="font-medium text-sm text-gray-900">{t.label}</div>
                                        <div className="text-xs text-gray-500">{t.desc}</div>
                                    </label>
                                ))}
                            </div>
                        </div>

                        <div>
                            <h3 className="text-sm font-semibold text-gray-700 mb-4">Colores de la factura</h3>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Color primario
                                        <span className="ml-2 text-xs text-gray-400">(cabeceras, fondos)</span>
                                    </label>
                                    <div className="flex items-center gap-3">
                                        <input type="color" value={data.primary_color}
                                            onChange={e => setData('primary_color', e.target.value)}
                                            className="w-10 h-10 rounded cursor-pointer border border-gray-300" />
                                        <input type="text" value={data.primary_color}
                                            onChange={e => setData('primary_color', e.target.value)}
                                            className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                                            maxLength={7} />
                                    </div>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Color de acento
                                        <span className="ml-2 text-xs text-gray-400">(totales, detalles)</span>
                                    </label>
                                    <div className="flex items-center gap-3">
                                        <input type="color" value={data.accent_color}
                                            onChange={e => setData('accent_color', e.target.value)}
                                            className="w-10 h-10 rounded cursor-pointer border border-gray-300" />
                                        <input type="text" value={data.accent_color}
                                            onChange={e => setData('accent_color', e.target.value)}
                                            className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                                            maxLength={7} />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 className="text-sm font-semibold text-gray-700 mb-3">Textos de la factura</h3>
                            <div className="space-y-4">
                                <Field label="Notas de cabecera (opcional)">
                                    <textarea value={data.invoice_header_notes}
                                        onChange={e => setData('invoice_header_notes', e.target.value)}
                                        rows={2} className={inp(false) + ' resize-none'}
                                        placeholder="Ej: Número de expediente, referencia proyecto..." />
                                </Field>
                                <Field label="Texto de pie de factura">
                                    <textarea value={data.invoice_footer_text}
                                        onChange={e => setData('invoice_footer_text', e.target.value)}
                                        rows={3} className={inp(false) + ' resize-none'}
                                        placeholder="Ej: Pago a 30 días desde la fecha de factura. En caso de retraso se aplicarán intereses..." />
                                </Field>
                            </div>
                        </div>
                    </div>
                )}

                {/* ── Tab: Logo ────────────────────────────────────────────── */}
                {activeTab === 'logo' && (
                    <div className="bg-white rounded-xl border shadow-sm p-6 space-y-6">
                        <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide">Logo de empresa</h2>

                        {company.logo_path ? (
                            <div className="space-y-4">
                                <div className="border rounded-xl p-6 bg-gray-50 flex items-center justify-center">
                                    <img
                                        src={`/storage/${company.logo_path}`}
                                        alt="Logo empresa"
                                        className="max-h-32 max-w-64 object-contain"
                                    />
                                </div>
                                <div className="flex gap-3">
                                    <button type="button" onClick={() => fileInputRef.current?.click()}
                                        className="border border-[#1e3a5f] text-[#1e3a5f] hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        Cambiar logo
                                    </button>
                                    <button type="button" onClick={deleteLogo}
                                        className="border border-red-300 text-red-600 hover:bg-red-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        Eliminar logo
                                    </button>
                                </div>
                            </div>
                        ) : (
                            <div className="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center">
                                <div className="text-4xl mb-3">🏢</div>
                                <p className="text-gray-500 text-sm mb-4">
                                    Sube tu logo para que aparezca en las facturas PDF
                                </p>
                                <button type="button" onClick={() => fileInputRef.current?.click()}
                                    className="bg-[#1e3a5f] hover:bg-[#152843] text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                                    Subir logo
                                </button>
                                <p className="text-xs text-gray-400 mt-3">PNG, JPG o SVG · Máx. 2 MB</p>
                            </div>
                        )}

                        <input ref={fileInputRef} type="file" accept="image/png,image/jpeg,image/svg+xml"
                            onChange={uploadLogo} className="hidden" />
                    </div>
                )}

                {/* ── Tab: Series y fiscal ──────────────────────────────────── */}
                {activeTab === 'series' && (
                    <div className="bg-white rounded-xl border shadow-sm p-6 space-y-6">
                        <div>
                            <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Series de numeración</h2>
                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <Field label="Serie facturas" error={errors.invoice_series}>
                                    <input type="text" value={data.invoice_series}
                                        onChange={e => setData('invoice_series', e.target.value.toUpperCase())}
                                        className={inp(false)} maxLength={5} placeholder="F" />
                                    <p className="text-xs text-gray-400 mt-1">Ej: F → F2025-001</p>
                                </Field>
                                <Field label="Serie rectificativas">
                                    <input type="text" value={data.rectification_series}
                                        onChange={e => setData('rectification_series', e.target.value.toUpperCase())}
                                        className={inp(false)} maxLength={5} placeholder="R" />
                                </Field>
                                <Field label="Serie presupuestos">
                                    <input type="text" value={data.quote_series}
                                        onChange={e => setData('quote_series', e.target.value.toUpperCase())}
                                        className={inp(false)} maxLength={5} placeholder="P" />
                                </Field>
                            </div>
                        </div>

                        <div className="border-t pt-5">
                            <h3 className="text-sm font-semibold text-gray-700 mb-4">Configuración fiscal</h3>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <Field label="IVA por defecto" error={errors.default_vat_rate}>
                                    <select value={data.default_vat_rate}
                                        onChange={e => setData('default_vat_rate', e.target.value)}
                                        className={inp(false)}>
                                        <option value="21">21% (general)</option>
                                        <option value="10">10% (reducido)</option>
                                        <option value="4">4% (superreducido)</option>
                                        <option value="0">0% (exento)</option>
                                    </select>
                                </Field>

                                <div>
                                    <label className="flex items-center gap-2 cursor-pointer mt-6">
                                        <input type="checkbox" checked={data.irpf_applicable}
                                            onChange={e => setData('irpf_applicable', e.target.checked)}
                                            className="w-4 h-4 rounded text-[#1e3a5f]" />
                                        <span className="text-sm text-gray-700">Aplicar retención IRPF</span>
                                    </label>
                                </div>

                                {data.irpf_applicable && (
                                    <Field label="% Retención IRPF">
                                        <select value={data.irpf_rate}
                                            onChange={e => setData('irpf_rate', e.target.value)}
                                            className={inp(false)}>
                                            <option value="7">7% (nuevo autónomo, primeros 3 años)</option>
                                            <option value="15">15% (general)</option>
                                            <option value="19">19%</option>
                                        </select>
                                    </Field>
                                )}
                            </div>
                        </div>
                    </div>
                )}

                {/* Save button */}
                {activeTab !== 'logo' && (
                    <div className="flex items-center gap-3 mt-6">
                        <button type="submit" disabled={processing}
                            className="bg-[#1e3a5f] hover:bg-[#152843] text-white px-6 py-2.5 rounded-lg text-sm font-medium transition-colors disabled:opacity-50">
                            {processing ? 'Guardando...' : 'Guardar configuración'}
                        </button>
                    </div>
                )}
            </form>
        </AppLayout>
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

function inp(hasError: boolean) {
    return `w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] ${
        hasError ? 'border-red-400 bg-red-50' : 'border-gray-300'
    }`;
}
