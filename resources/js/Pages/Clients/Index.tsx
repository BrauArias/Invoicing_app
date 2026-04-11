import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import type { Client, PaginatedData } from '@/types';

interface Props {
    clients: PaginatedData<Client>;
    filters: { search: string };
}

export default function ClientsIndex({ clients, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');

    function handleSearch(e: React.FormEvent) {
        e.preventDefault();
        router.get('/clientes', { search }, { preserveState: true });
    }

    function deleteClient(client: Client) {
        if (!confirm(`¿Eliminar a "${client.name}"? Esta acción no se puede deshacer.`)) return;
        router.delete(`/clientes/${client.id}`);
    }

    return (
        <AppLayout title="Clientes">
            <Head title="Clientes" />

            <div className="flex flex-col sm:flex-row gap-3 mb-6">
                <form onSubmit={handleSearch} className="flex gap-2 flex-1">
                    <input
                        type="text"
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                        placeholder="Buscar por nombre, NIF o email..."
                        className="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                    />
                    <button type="submit" className="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm transition-colors">
                        Buscar
                    </button>
                </form>
                <Link
                    href="/clientes/nuevo"
                    className="inline-flex items-center gap-2 bg-[#1e3a5f] hover:bg-[#152843] text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors"
                >
                    + Nuevo cliente
                </Link>
            </div>

            <div className="bg-white rounded-xl border shadow-sm">
                {clients.data.length === 0 ? (
                    <div className="px-6 py-16 text-center">
                        <div className="text-4xl mb-3">👤</div>
                        <p className="text-gray-500 text-sm mb-4">No se encontraron clientes.</p>
                        <Link href="/clientes/nuevo" className="text-sm text-[#1e3a5f] font-medium hover:underline">
                            Crear primer cliente →
                        </Link>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="text-xs text-gray-500 uppercase border-b">
                                    <th className="px-6 py-3 text-left font-medium">Cliente</th>
                                    <th className="px-6 py-3 text-left font-medium">NIF</th>
                                    <th className="px-6 py-3 text-left font-medium">Contacto</th>
                                    <th className="px-6 py-3 text-right font-medium">Facturas</th>
                                    <th className="px-6 py-3 text-right font-medium">Facturado</th>
                                    <th className="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {clients.data.map((client) => (
                                    <tr key={client.id} className="hover:bg-gray-50">
                                        <td className="px-6 py-3">
                                            <div className="font-medium text-gray-900">{client.name}</div>
                                            {client.trade_name && (
                                                <div className="text-xs text-gray-500">{client.trade_name}</div>
                                            )}
                                            <div className="text-xs text-gray-400">
                                                {client.type === 'business' ? 'Empresa' : 'Particular'}
                                            </div>
                                        </td>
                                        <td className="px-6 py-3 font-mono text-gray-700">{client.nif || '—'}</td>
                                        <td className="px-6 py-3">
                                            {client.email && <div className="text-gray-600">{client.email}</div>}
                                            {client.phone && <div className="text-gray-500 text-xs">{client.phone}</div>}
                                        </td>
                                        <td className="px-6 py-3 text-right text-gray-600">
                                            {client.invoices_count ?? 0}
                                        </td>
                                        <td className="px-6 py-3 text-right font-medium">
                                            {new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' })
                                                .format(client.total_invoiced ?? 0)}
                                        </td>
                                        <td className="px-6 py-3 text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link
                                                    href={`/clientes/${client.id}/editar`}
                                                    className="text-xs text-[#1e3a5f] hover:underline"
                                                >
                                                    Editar
                                                </Link>
                                                <button
                                                    onClick={() => deleteClient(client)}
                                                    className="text-xs text-red-500 hover:underline"
                                                >
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

                {/* Pagination */}
                {clients.last_page > 1 && (
                    <div className="px-6 py-4 border-t flex items-center justify-between text-sm text-gray-600">
                        <span>
                            Mostrando {clients.from}–{clients.to} de {clients.total}
                        </span>
                        <div className="flex gap-2">
                            {clients.current_page > 1 && (
                                <button onClick={() => router.get('/clientes', { search, page: clients.current_page - 1 })}
                                    className="px-3 py-1 border rounded hover:bg-gray-50">
                                    ← Anterior
                                </button>
                            )}
                            {clients.current_page < clients.last_page && (
                                <button onClick={() => router.get('/clientes', { search, page: clients.current_page + 1 })}
                                    className="px-3 py-1 border rounded hover:bg-gray-50">
                                    Siguiente →
                                </button>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
