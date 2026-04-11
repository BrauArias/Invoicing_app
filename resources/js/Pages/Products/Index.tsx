import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import type { Product } from '@/types';

interface Props { products: Product[]; }

const euros = (v: number) =>
    new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(v);

export default function ProductsIndex({ products }: Props) {
    function deleteProduct(p: Product) {
        if (!confirm(`¿Eliminar "${p.name}"?`)) return;
        router.delete(`/servicios/${p.id}`);
    }

    return (
        <AppLayout title="Servicios">
            <Head title="Servicios" />

            <div className="flex justify-end mb-6">
                <Link href="/servicios/nuevo"
                    className="inline-flex items-center gap-2 bg-[#1e3a5f] hover:bg-[#152843] text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                    + Nuevo servicio
                </Link>
            </div>

            <div className="bg-white rounded-xl border shadow-sm">
                {products.length === 0 ? (
                    <div className="px-6 py-16 text-center">
                        <div className="text-4xl mb-3">🗂</div>
                        <p className="text-gray-500 text-sm mb-4">No hay servicios en el catálogo.</p>
                        <Link href="/servicios/nuevo" className="text-sm text-[#1e3a5f] font-medium hover:underline">
                            Crear primer servicio →
                        </Link>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="text-xs text-gray-500 uppercase border-b">
                                    <th className="px-6 py-3 text-left font-medium">Servicio</th>
                                    <th className="px-6 py-3 text-left font-medium">Código</th>
                                    <th className="px-6 py-3 text-right font-medium">Precio</th>
                                    <th className="px-6 py-3 text-center font-medium">IVA</th>
                                    <th className="px-6 py-3 text-left font-medium">Unidad</th>
                                    <th className="px-6 py-3 text-center font-medium">Estado</th>
                                    <th className="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {products.map((p) => (
                                    <tr key={p.id} className="hover:bg-gray-50">
                                        <td className="px-6 py-3">
                                            <div className="font-medium text-gray-900">{p.name}</div>
                                            {p.description && (
                                                <div className="text-xs text-gray-500 truncate max-w-xs">{p.description}</div>
                                            )}
                                        </td>
                                        <td className="px-6 py-3 font-mono text-gray-500">{p.code || '—'}</td>
                                        <td className="px-6 py-3 text-right font-medium">{euros(p.unit_price)}</td>
                                        <td className="px-6 py-3 text-center">{p.vat_rate}%</td>
                                        <td className="px-6 py-3 text-gray-600">{p.unit}</td>
                                        <td className="px-6 py-3 text-center">
                                            <span className={`inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${
                                                p.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'
                                            }`}>
                                                {p.is_active ? 'Activo' : 'Inactivo'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-3 text-right">
                                            <div className="flex items-center justify-end gap-3">
                                                <Link href={`/servicios/${p.id}/editar`}
                                                    className="text-xs text-[#1e3a5f] hover:underline">
                                                    Editar
                                                </Link>
                                                <button onClick={() => deleteProduct(p)}
                                                    className="text-xs text-red-500 hover:underline">
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
