import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import type { Product } from '@/types';

interface Props { product: Product; }

export default function ProductEdit({ product }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        code:        product.code        ?? '',
        name:        product.name,
        description: product.description ?? '',
        unit_price:  String(product.unit_price),
        vat_rate:    String(product.vat_rate),
        unit:        product.unit        ?? 'proyecto',
        is_active:   product.is_active,
    });

    return (
        <AppLayout title="Editar servicio">
            <Head title="Editar servicio" />
            <form onSubmit={(e) => { e.preventDefault(); put(`/servicios/${product.id}`); }} className="max-w-2xl">
                <div className="bg-white rounded-xl border shadow-sm p-6 space-y-5">
                    <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide">Datos del servicio</h2>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                            <input
                                type="text"
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                                required
                            />
                            {errors.name && <p className="text-red-600 text-xs mt-1">{errors.name}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Código (opcional)</label>
                            <input
                                type="text"
                                value={data.code}
                                onChange={e => setData('code', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                            />
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea
                            value={data.description}
                            onChange={e => setData('description', e.target.value)}
                            rows={2}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] resize-none"
                        />
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Precio (€) *</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                value={data.unit_price}
                                onChange={e => setData('unit_price', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                                required
                            />
                            {errors.unit_price && <p className="text-red-600 text-xs mt-1">{errors.unit_price}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">IVA *</label>
                            <select
                                value={data.vat_rate}
                                onChange={e => setData('vat_rate', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                            >
                                <option value="21">21% (general)</option>
                                <option value="10">10% (reducido)</option>
                                <option value="4">4% (superreducido)</option>
                                <option value="0">0% (exento)</option>
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Unidad</label>
                            <select
                                value={data.unit}
                                onChange={e => setData('unit', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                            >
                                <option value="proyecto">Proyecto</option>
                                <option value="hora">Hora</option>
                                <option value="unidad">Unidad</option>
                                <option value="imagen">Imagen</option>
                                <option value="video">Video</option>
                                <option value="mes">Mes</option>
                            </select>
                        </div>
                    </div>

                    <label className="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            checked={data.is_active}
                            onChange={e => setData('is_active', e.target.checked)}
                            className="w-4 h-4 rounded text-[#1e3a5f] focus:ring-[#1e3a5f]"
                        />
                        <span className="text-sm text-gray-700">Servicio activo (visible al crear facturas)</span>
                    </label>
                </div>

                <div className="flex items-center gap-3 mt-6">
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-[#1e3a5f] hover:bg-[#152843] text-white px-6 py-2.5 rounded-lg text-sm font-medium transition-colors disabled:opacity-50"
                    >
                        {processing ? 'Guardando...' : 'Guardar cambios'}
                    </button>
                    <Link href="/servicios" className="text-sm text-gray-500 hover:text-gray-700">Cancelar</Link>
                </div>
            </form>
        </AppLayout>
    );
}
