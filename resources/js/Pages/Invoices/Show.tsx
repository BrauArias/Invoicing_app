import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import type { Invoice } from '@/types';

interface Props { invoice: Invoice; }

const euros = (v: number | string) =>
    new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(Number(v));

const statusConfig = {
    draft:     { label: 'Borrador',  color: 'bg-gray-100 text-gray-700' },
    sent:      { label: 'Enviada',   color: 'bg-blue-100 text-blue-700' },
    paid:      { label: 'Pagada',    color: 'bg-green-100 text-green-700' },
    overdue:   { label: 'Vencida',   color: 'bg-red-100 text-red-700' },
    cancelled: { label: 'Cancelada', color: 'bg-gray-100 text-gray-500' },
} as const;

export default function InvoiceShow({ invoice }: Props) {
    const sc = statusConfig[invoice.status] ?? statusConfig.draft;

    function emit() {
        if (!confirm('¿Emitir esta factura? Se asignará un número definitivo y no podrá modificarse.')) return;
        router.post(`/facturas/${invoice.id}/emitir`);
    }

    function markPaid() {
        if (!confirm('¿Marcar como pagada?')) return;
        router.post(`/facturas/${invoice.id}/cobrada`);
    }

    function cancel() {
        if (!confirm('¿Cancelar esta factura?')) return;
        router.post(`/facturas/${invoice.id}/cancelar`);
    }

    function duplicate() {
        router.post(`/facturas/${invoice.id}/duplicar`);
    }

    return (
        <AppLayout title={invoice.full_number || 'Borrador'}>
            <Head title={`Factura ${invoice.full_number || 'Borrador'}`} />

            {/* Toolbar */}
            <div className="flex flex-wrap items-center gap-3 mb-6">
                <span className={`inline-flex px-3 py-1 rounded-full text-sm font-medium ${sc.color}`}>
                    {sc.label}
                </span>

                <div className="flex-1" />

                <a href={`/facturas/${invoice.id}/pdf`} target="_blank"
                    className="inline-flex items-center gap-2 border border-[#1e3a5f] text-[#1e3a5f] hover:bg-[#1e3a5f] hover:text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    📄 Descargar PDF
                </a>

                {invoice.status === 'draft' && (
                    <button onClick={emit}
                        className="bg-[#1e3a5f] hover:bg-[#152843] text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        📤 Emitir factura
                    </button>
                )}

                {invoice.status === 'sent' && (
                    <button onClick={markPaid}
                        className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        ✓ Marcar como cobrada
                    </button>
                )}

                <button onClick={duplicate}
                    className="border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    📋 Duplicar
                </button>

                {['draft', 'sent'].includes(invoice.status) && (
                    <button onClick={cancel}
                        className="text-red-600 hover:text-red-700 text-sm font-medium">
                        Cancelar factura
                    </button>
                )}

                <Link href="/facturas" className="text-sm text-gray-500 hover:text-gray-700">
                    ← Volver
                </Link>
            </div>

            {/* Invoice preview */}
            <div className="bg-white rounded-xl border shadow-sm">
                {/* Header */}
                <div className="p-8 border-b">
                    <div className="flex justify-between items-start">
                        <div>
                            <h2 className="text-2xl font-bold text-[#1e3a5f]">
                                {invoice.full_number || <span className="text-gray-400 italic text-lg">BORRADOR</span>}
                            </h2>
                            <p className="text-gray-500 text-sm mt-1">
                                {invoice.type === 'invoice'     ? 'Factura' :
                                 invoice.type === 'proforma'    ? 'Proforma' :
                                 invoice.type === 'quote'       ? 'Presupuesto' : 'Factura Rectificativa'}
                            </p>
                        </div>
                        <div className="text-right text-sm text-gray-600 space-y-1">
                            <div><span className="font-medium">Fecha:</span> {new Date(invoice.issue_date).toLocaleDateString('es-ES')}</div>
                            {invoice.due_date && (
                                <div><span className="font-medium">Vencimiento:</span> {new Date(invoice.due_date).toLocaleDateString('es-ES')}</div>
                            )}
                            {invoice.paid_at && (
                                <div className="text-green-600">
                                    <span className="font-medium">Cobrada:</span> {new Date(invoice.paid_at).toLocaleDateString('es-ES')}
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Client info */}
                <div className="px-8 py-6 border-b">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-8">
                        <div>
                            <h3 className="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Facturado a</h3>
                            <p className="font-semibold text-gray-900">{invoice.client_name}</p>
                            {invoice.client_nif && <p className="text-sm text-gray-600">NIF: {invoice.client_nif}</p>}
                            {invoice.client_address && <p className="text-sm text-gray-500">{invoice.client_address}</p>}
                        </div>
                        <div>
                            <h3 className="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Pago</h3>
                            {invoice.payment_method && (
                                <p className="text-sm text-gray-700 capitalize">{invoice.payment_method}</p>
                            )}
                            {invoice.payment_terms && (
                                <p className="text-sm text-gray-500">{invoice.payment_terms}</p>
                            )}
                        </div>
                    </div>
                </div>

                {/* Lines */}
                <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="bg-gray-50 border-b text-xs text-gray-500 uppercase">
                                <th className="px-8 py-3 text-left font-medium">Descripción</th>
                                <th className="px-4 py-3 text-right font-medium">Cantidad</th>
                                <th className="px-4 py-3 text-right font-medium">Precio unit.</th>
                                <th className="px-4 py-3 text-center font-medium">Dto.</th>
                                <th className="px-4 py-3 text-center font-medium">IVA</th>
                                <th className="px-8 py-3 text-right font-medium">Total</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {(invoice.lines ?? []).map((line) => (
                                <tr key={line.id}>
                                    <td className="px-8 py-3">
                                        <div className="font-medium text-gray-900">{line.description}</div>
                                        <div className="text-xs text-gray-500">{line.unit}</div>
                                    </td>
                                    <td className="px-4 py-3 text-right text-gray-700">{line.quantity}</td>
                                    <td className="px-4 py-3 text-right text-gray-700">{euros(line.unit_price)}</td>
                                    <td className="px-4 py-3 text-center text-gray-500">
                                        {Number(line.discount) > 0 ? `${line.discount}%` : '—'}
                                    </td>
                                    <td className="px-4 py-3 text-center text-gray-500">{line.vat_rate}%</td>
                                    <td className="px-8 py-3 text-right font-medium">{euros(line.total)}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Totals */}
                <div className="px-8 py-6 border-t flex justify-end">
                    <div className="w-64 space-y-2">
                        <div className="flex justify-between text-sm text-gray-600">
                            <span>Base imponible</span>
                            <span>{euros(invoice.subtotal)}</span>
                        </div>
                        <div className="flex justify-between text-sm text-gray-600">
                            <span>IVA</span>
                            <span>{euros(invoice.vat_amount)}</span>
                        </div>
                        {Number(invoice.irpf_amount) > 0 && (
                            <div className="flex justify-between text-sm text-red-600">
                                <span>Retención IRPF</span>
                                <span>−{euros(invoice.irpf_amount)}</span>
                            </div>
                        )}
                        <div className="flex justify-between font-bold text-base text-gray-900 border-t pt-2">
                            <span>TOTAL A PAGAR</span>
                            <span className="text-[#1e3a5f] text-lg">{euros(invoice.total)}</span>
                        </div>
                    </div>
                </div>

                {/* Notes */}
                {invoice.notes && (
                    <div className="px-8 py-5 border-t bg-gray-50">
                        <p className="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Notas</p>
                        <p className="text-sm text-gray-600 whitespace-pre-wrap">{invoice.notes}</p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
