import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import type { Invoice, PaginatedData } from '@/types';

interface Props {
    invoices: PaginatedData<Invoice>;
    filters: { status?: string; type?: string; search?: string };
}

const statusConfig = {
    draft:     { label: 'Borrador',  color: 'bg-gray-100 text-gray-700' },
    sent:      { label: 'Enviada',   color: 'bg-blue-100 text-blue-700' },
    paid:      { label: 'Pagada',    color: 'bg-green-100 text-green-700' },
    overdue:   { label: 'Vencida',   color: 'bg-red-100 text-red-700' },
    cancelled: { label: 'Cancelada', color: 'bg-gray-100 text-gray-400' },
} as const;

const euros = (v: number) =>
    new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(v);

export default function InvoicesIndex({ invoices, filters }: Props) {
    const [search, setSearch]   = useState(filters.search || '');
    const [status, setStatus]   = useState(filters.status || '');
    const [type, setType]       = useState(filters.type || '');

    function applyFilters() {
        router.get('/facturas', { search, status, type }, { preserveState: true });
    }

    return (
        <AppLayout title="Facturas">
            <Head title="Facturas" />

            {/* Filters */}
            <div className="flex flex-col sm:flex-row gap-3 mb-6">
                <input
                    type="text"
                    value={search}
                    onChange={e => setSearch(e.target.value)}
                    onKeyDown={e => e.key === 'Enter' && applyFilters()}
                    placeholder="Buscar por número o cliente..."
                    className="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                />
                <select value={status} onChange={e => { setStatus(e.target.value); applyFilters(); }}
                    className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none">
                    <option value="">Todos los estados</option>
                    {Object.entries(statusConfig).map(([k, v]) => (
                        <option key={k} value={k}>{v.label}</option>
                    ))}
                </select>
                <select value={type} onChange={e => { setType(e.target.value); applyFilters(); }}
                    className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none">
                    <option value="">Todos los tipos</option>
                    <option value="invoice">Factura</option>
                    <option value="proforma">Proforma</option>
                    <option value="quote">Presupuesto</option>
                    <option value="credit_note">Rectificativa</option>
                </select>
                <Link href="/facturas/nueva"
                    className="inline-flex items-center gap-2 bg-[#1e3a5f] hover:bg-[#152843] text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
                    + Nueva factura
                </Link>
            </div>

            <div className="bg-white rounded-xl border shadow-sm">
                {invoices.data.length === 0 ? (
                    <div className="px-6 py-16 text-center">
                        <div className="text-4xl mb-3">📄</div>
                        <p className="text-gray-500 text-sm mb-4">No hay facturas.</p>
                        <Link href="/facturas/nueva" className="text-sm text-[#1e3a5f] font-medium hover:underline">
                            Crear primera factura →
                        </Link>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="text-xs text-gray-500 uppercase border-b">
                                    <th className="px-6 py-3 text-left font-medium">Nº Factura</th>
                                    <th className="px-6 py-3 text-left font-medium">Cliente</th>
                                    <th className="px-6 py-3 text-left font-medium">Fecha</th>
                                    <th className="px-6 py-3 text-left font-medium">Vencimiento</th>
                                    <th className="px-6 py-3 text-right font-medium">Total</th>
                                    <th className="px-6 py-3 text-center font-medium">Estado</th>
                                    <th className="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {invoices.data.map((inv) => {
                                    const sc = statusConfig[inv.status] ?? statusConfig.draft;
                                    const isOverdue = inv.due_date && inv.status === 'sent' &&
                                        new Date(inv.due_date) < new Date();
                                    return (
                                        <tr key={inv.id} className="hover:bg-gray-50 transition-colors">
                                            <td className="px-6 py-3">
                                                <div className="font-mono font-medium text-[#1e3a5f]">
                                                    {inv.full_number || <span className="text-gray-400 italic">Borrador</span>}
                                                </div>
                                                <div className="text-xs text-gray-500">
                                                    {inv.type === 'invoice' ? 'Factura' :
                                                     inv.type === 'proforma' ? 'Proforma' :
                                                     inv.type === 'quote' ? 'Presupuesto' : 'Rectificativa'}
                                                </div>
                                            </td>
                                            <td className="px-6 py-3 text-gray-700">{inv.client_name}</td>
                                            <td className="px-6 py-3 text-gray-500">
                                                {new Date(inv.issue_date).toLocaleDateString('es-ES')}
                                            </td>
                                            <td className={`px-6 py-3 ${isOverdue ? 'text-red-600 font-medium' : 'text-gray-500'}`}>
                                                {inv.due_date ? new Date(inv.due_date).toLocaleDateString('es-ES') : '—'}
                                                {isOverdue && ' ⚠️'}
                                            </td>
                                            <td className="px-6 py-3 text-right font-medium">{euros(inv.total)}</td>
                                            <td className="px-6 py-3 text-center">
                                                <span className={`inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium ${sc.color}`}>
                                                    {sc.label}
                                                </span>
                                            </td>
                                            <td className="px-6 py-3 text-right">
                                                <Link href={`/facturas/${inv.id}`}
                                                    className="text-xs text-[#1e3a5f] hover:underline">
                                                    Ver →
                                                </Link>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}

                {invoices.last_page > 1 && (
                    <div className="px-6 py-4 border-t flex items-center justify-between text-sm text-gray-600">
                        <span>Mostrando {invoices.from}–{invoices.to} de {invoices.total}</span>
                        <div className="flex gap-2">
                            {invoices.current_page > 1 && (
                                <button onClick={() => router.get('/facturas', { ...filters, page: invoices.current_page - 1 })}
                                    className="px-3 py-1 border rounded hover:bg-gray-50">← Anterior</button>
                            )}
                            {invoices.current_page < invoices.last_page && (
                                <button onClick={() => router.get('/facturas', { ...filters, page: invoices.current_page + 1 })}
                                    className="px-3 py-1 border rounded hover:bg-gray-50">Siguiente →</button>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
