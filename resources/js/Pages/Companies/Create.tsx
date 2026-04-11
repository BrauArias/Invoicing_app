import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function CompanyCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name:              '',
        trade_name:        '',
        nif:               '',
        address:           '',
        city:              '',
        province:          '',
        postal_code:       '',
        phone:             '',
        email:             '',
        website:           '',
        iban:              '',
        default_vat_rate:  '21',
        irpf_applicable:   false,
        irpf_rate:         '15',
        invoice_series:    'F',
        invoice_template:  'classic',
        primary_color:     '#1e3a5f',
        accent_color:      '#d4a017',
    });

    return (
        <AppLayout title="Nueva empresa">
            <Head title="Nueva empresa" />
            <form onSubmit={(e) => { e.preventDefault(); post('/empresas'); }} className="max-w-2xl space-y-6">

                {/* Fiscal data */}
                <div className="bg-white rounded-xl border shadow-sm p-6 space-y-5">
                    <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide">Datos fiscales</h2>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div className="sm:col-span-2">
                            <label className="block text-sm font-medium text-gray-700 mb-1">Razón social / Nombre *</label>
                            <input
                                type="text"
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                                placeholder="InmoVisualPro S.L."
                                required
                            />
                            {errors.name && <p className="text-red-600 text-xs mt-1">{errors.name}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Nombre comercial</label>
                            <input
                                type="text"
                                value={data.trade_name}
                                onChange={e => setData('trade_name', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                                placeholder="InmoVisualPro"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">NIF / CIF *</label>
                            <input
                                type="text"
                                value={data.nif}
                                onChange={e => setData('nif', e.target.value.toUpperCase())}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                                placeholder="12345678Z"
                                required
                            />
                            {errors.nif && <p className="text-red-600 text-xs mt-1">{errors.nif}</p>}
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                        <input
                            type="text"
                            value={data.address}
                            onChange={e => setData('address', e.target.value)}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                            placeholder="Calle Mayor, 1"
                        />
                    </div>

                    <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div className="col-span-2 sm:col-span-2">
                            <label className="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                            <input
                                type="text"
                                value={data.city}
                                onChange={e => setData('city', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                                placeholder="Valencia"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Provincia</label>
                            <input
                                type="text"
                                value={data.province}
                                onChange={e => setData('province', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                                placeholder="Valencia"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">C.P.</label>
                            <input
                                type="text"
                                value={data.postal_code}
                                onChange={e => setData('postal_code', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                                placeholder="46001"
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input
                                type="tel"
                                value={data.phone}
                                onChange={e => setData('phone', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                                placeholder="+34 600 000 000"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                type="email"
                                value={data.email}
                                onChange={e => setData('email', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                                placeholder="hola@empresa.es"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Web</label>
                            <input
                                type="text"
                                value={data.website}
                                onChange={e => setData('website', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                                placeholder="www.empresa.es"
                            />
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">IBAN</label>
                        <input
                            type="text"
                            value={data.iban}
                            onChange={e => setData('iban', e.target.value.toUpperCase())}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] font-mono"
                            placeholder="ES91 2100 0418 4502 0005 1332"
                        />
                    </div>
                </div>

                {/* Fiscal config */}
                <div className="bg-white rounded-xl border shadow-sm p-6 space-y-5">
                    <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide">Configuración fiscal</h2>

                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Serie de facturas</label>
                            <input
                                type="text"
                                value={data.invoice_series}
                                onChange={e => setData('invoice_series', e.target.value.toUpperCase())}
                                maxLength={5}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] font-mono"
                                placeholder="F"
                            />
                            <p className="text-xs text-gray-400 mt-1">Ej: F → F2025-001</p>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">IVA por defecto</label>
                            <select
                                value={data.default_vat_rate}
                                onChange={e => setData('default_vat_rate', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                            >
                                <option value="21">21% (general)</option>
                                <option value="10">10% (reducido)</option>
                                <option value="4">4% (superreducido)</option>
                                <option value="0">0% (exento)</option>
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">IRPF</label>
                            <label className="flex items-center gap-2 mt-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={data.irpf_applicable}
                                    onChange={e => setData('irpf_applicable', e.target.checked)}
                                    className="w-4 h-4 rounded text-[#1e3a5f] focus:ring-[#1e3a5f]"
                                />
                                <span className="text-sm text-gray-700">Aplica retención IRPF</span>
                            </label>
                            {data.irpf_applicable && (
                                <select
                                    value={data.irpf_rate}
                                    onChange={e => setData('irpf_rate', e.target.value)}
                                    className="w-full mt-2 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                                >
                                    <option value="15">15% (general)</option>
                                    <option value="7">7% (nuevo autónomo)</option>
                                </select>
                            )}
                        </div>
                    </div>
                </div>

                {/* Template & colors */}
                <div className="bg-white rounded-xl border shadow-sm p-6 space-y-5">
                    <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide">Plantilla y colores</h2>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-3">Plantilla de factura</label>
                        <div className="grid grid-cols-3 gap-3">
                            {(['classic', 'modern', 'minimal'] as const).map((t) => (
                                <label key={t} className={`cursor-pointer rounded-lg border-2 p-3 text-center transition-all ${
                                    data.invoice_template === t
                                        ? 'border-[#1e3a5f] bg-blue-50'
                                        : 'border-gray-200 hover:border-gray-300'
                                }`}>
                                    <input
                                        type="radio"
                                        name="template"
                                        value={t}
                                        checked={data.invoice_template === t}
                                        onChange={() => setData('invoice_template', t)}
                                        className="sr-only"
                                    />
                                    <div className="text-xl mb-1">
                                        {t === 'classic' ? '🏛️' : t === 'modern' ? '✨' : '▪️'}
                                    </div>
                                    <div className="text-xs font-medium text-gray-700 capitalize">{t === 'classic' ? 'Clásica' : t === 'modern' ? 'Moderna' : 'Minimalista'}</div>
                                </label>
                            ))}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Color principal</label>
                            <div className="flex gap-2 items-center">
                                <input
                                    type="color"
                                    value={data.primary_color}
                                    onChange={e => setData('primary_color', e.target.value)}
                                    className="w-10 h-10 rounded cursor-pointer border border-gray-300"
                                />
                                <input
                                    type="text"
                                    value={data.primary_color}
                                    onChange={e => setData('primary_color', e.target.value)}
                                    className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                                    placeholder="#1e3a5f"
                                />
                            </div>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Color de acento</label>
                            <div className="flex gap-2 items-center">
                                <input
                                    type="color"
                                    value={data.accent_color}
                                    onChange={e => setData('accent_color', e.target.value)}
                                    className="w-10 h-10 rounded cursor-pointer border border-gray-300"
                                />
                                <input
                                    type="text"
                                    value={data.accent_color}
                                    onChange={e => setData('accent_color', e.target.value)}
                                    className="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                                    placeholder="#d4a017"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div className="flex items-center gap-3">
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-[#1e3a5f] hover:bg-[#152843] text-white px-6 py-2.5 rounded-lg text-sm font-medium transition-colors disabled:opacity-50"
                    >
                        {processing ? 'Creando...' : 'Crear empresa'}
                    </button>
                    <Link href="/empresas" className="text-sm text-gray-500 hover:text-gray-700">Cancelar</Link>
                </div>
            </form>
        </AppLayout>
    );
}
