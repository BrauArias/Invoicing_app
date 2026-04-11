import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import type { Client, Product } from '@/types';
import InvoiceBuilder, { type LineData, emptyLine, calcLine } from '@/Components/InvoiceBuilder';

interface Props {
    clients: Client[];
    products: Product[];
    defaults: { issue_date: string; due_date: string; vat_rate: number; irpf_rate: number; };
}

export default function InvoiceCreate({ clients, products, defaults }: Props) {
    const [lines, setLines]             = useState<LineData[]>([emptyLine(defaults.vat_rate)]);
    const [selectedClientId, setClientId] = useState<number | null>(null);
    const [applyIrpf, setApplyIrpf]     = useState(false);
    const [irpfRate, setIrpfRate]        = useState(defaults.irpf_rate);
    const [invoiceType, setInvoiceType]  = useState('invoice');

    const { data, setData, post, processing, errors } = useForm({
        client_id:      0,
        type:           'invoice',
        status:         'draft',
        issue_date:     defaults.issue_date,
        due_date:       defaults.due_date,
        service_date:   '',
        payment_method: 'transferencia',
        payment_terms:  '',
        notes:          '',
        internal_notes: '',
        lines:          [] as LineData[],
    });

    // Calcular totales en tiempo real
    const subtotal   = lines.reduce((s, l) => s + calcLine(l).subtotal, 0);
    const vatAmount  = lines.reduce((s, l) => s + calcLine(l).vatAmt, 0);
    const irpfAmount = applyIrpf ? Math.round(subtotal * irpfRate / 100 * 100) / 100 : 0;
    const total      = Math.round((subtotal + vatAmount - irpfAmount) * 100) / 100;

    const vatBreakdown = lines.reduce((acc, l) => {
        const rate = parseFloat(l.vat_rate) || 0;
        const { subtotal: ls, vatAmt } = calcLine(l);
        const existing = acc.find(a => a.rate === rate);
        if (existing) { existing.base += ls; existing.amount += vatAmt; }
        else acc.push({ rate, base: ls, amount: vatAmt });
        return acc;
    }, [] as { rate: number; base: number; amount: number }[]);

    function handleClientChange(id: string) {
        const cId = parseInt(id);
        setClientId(cId || null);
        const client = clients.find(c => c.id === cId);
        if (client?.irpf_applicable) {
            setApplyIrpf(true);
            if (client.irpf_rate) setIrpfRate(client.irpf_rate);
        }
        setData('client_id', cId);
    }

    function handleSubmit(e: React.FormEvent, status: 'draft' | 'sent') {
        e.preventDefault();
        setData(d => ({ ...d, status, lines }));
        setTimeout(() => post('/facturas'), 50);
    }

    return (
        <AppLayout title="Nueva factura">
            <Head title="Nueva factura" />

            <form className="space-y-6">
                {/* Header Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Datos factura */}
                    <div className="bg-white rounded-xl border shadow-sm p-6 space-y-4">
                        <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide">Datos de la factura</h2>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                <select
                                    value={invoiceType}
                                    onChange={e => { setInvoiceType(e.target.value); setData('type', e.target.value); }}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                                >
                                    <option value="invoice">Factura</option>
                                    <option value="proforma">Proforma</option>
                                    <option value="quote">Presupuesto</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Fecha de emisión *</label>
                                <input
                                    type="date"
                                    value={data.issue_date}
                                    onChange={e => setData('issue_date', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Vencimiento</label>
                                <input
                                    type="date"
                                    value={data.due_date}
                                    onChange={e => setData('due_date', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Fecha servicio</label>
                                <input
                                    type="date"
                                    value={data.service_date}
                                    onChange={e => setData('service_date', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                />
                            </div>

                            <div className="col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-1">Forma de pago</label>
                                <select
                                    value={data.payment_method}
                                    onChange={e => setData('payment_method', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white"
                                >
                                    <option value="transferencia">Transferencia bancaria</option>
                                    <option value="tarjeta">Tarjeta de crédito</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="bizum">Bizum</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {/* Cliente */}
                    <div className="bg-white rounded-xl border shadow-sm p-6 space-y-4 flex flex-col">
                        <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            Cliente *
                            {errors.client_id && <span className="ml-2 text-red-600 normal-case font-normal text-xs">{errors.client_id}</span>}
                        </h2>

                        <select
                            value={selectedClientId ?? ''}
                            onChange={e => handleClientChange(e.target.value)}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#1e3a5f]"
                            required
                        >
                            <option value="">— Seleccionar cliente —</option>
                            {clients.map(c => (
                                <option key={c.id} value={c.id}>
                                    {c.name} {c.nif ? `(${c.nif})` : ''}
                                </option>
                            ))}
                        </select>

                        {clients.length === 0 && (
                            <p className="text-sm text-amber-600 mt-2">
                                No hay clientes.{' '}
                                <Link href="/clientes/nuevo" className="underline font-medium">Crear cliente →</Link>
                            </p>
                        )}

                        <div className="flex-grow"></div>

                        {/* IRPF config */}
                        <div className="pt-4 border-t border-gray-100 mt-4">
                            <label className="flex items-center gap-2 cursor-pointer w-max">
                                <input
                                    type="checkbox"
                                    checked={applyIrpf}
                                    onChange={e => setApplyIrpf(e.target.checked)}
                                    className="w-4 h-4 rounded text-[#1e3a5f] border-gray-300 focus:ring-[#1e3a5f]"
                                />
                                <span className="text-sm font-medium text-gray-700">Aplicar retención IRPF en esta factura</span>
                            </label>

                            {applyIrpf && (
                                <div className="mt-3 flex items-center gap-3 bg-gray-50 p-3 rounded-lg border border-gray-100">
                                    <label className="block text-sm font-medium text-gray-700">% IRPF</label>
                                    <select
                                        value={irpfRate}
                                        onChange={e => setIrpfRate(parseFloat(e.target.value))}
                                        className="px-3 py-1.5 border border-gray-300 bg-white rounded-md text-sm"
                                    >
                                        <option value={7}>7% (Nuevos Autónomos)</option>
                                        <option value={15}>15% (General)</option>
                                        <option value={19}>19%</option>
                                    </select>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Modular Invoice Builder for Lines */}
                <InvoiceBuilder 
                    lines={lines}
                    setLines={setLines}
                    products={products}
                    defaults={defaults}
                    errors={errors.lines}
                    applyIrpf={applyIrpf}
                    irpfRate={irpfRate}
                    subtotal={subtotal}
                    total={total}
                    vatBreakdown={vatBreakdown}
                    irpfAmount={irpfAmount}
                />

                {/* Notes */}
                <div className="bg-white rounded-xl border shadow-sm p-6">
                    <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Notas y observaciones</h2>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Notas en factura <span className="text-gray-400 font-normal italic">(visible para el cliente)</span>
                            </label>
                            <textarea
                                value={data.notes}
                                onChange={e => setData('notes', e.target.value)}
                                rows={3}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-[#1e3a5f] focus:outline-none"
                                placeholder="Ej: Condiciones de pago a 30 días, referencias del proyecto..."
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Notas internas <span className="text-gray-400 font-normal italic">(solo tú las ves)</span>
                            </label>
                            <textarea
                                value={data.internal_notes}
                                onChange={e => setData('internal_notes', e.target.value)}
                                rows={3}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-[#1e3a5f] focus:outline-none"
                                placeholder="Ej: Recordatorios, llamadas pendientes..."
                            />
                        </div>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex items-center gap-4 flex-wrap pt-2 pb-8">
                    <button
                        type="button"
                        onClick={(e) => handleSubmit(e, 'sent')}
                        disabled={processing || !selectedClientId}
                        className="bg-[#1e3a5f] hover:bg-[#152843] text-white px-8 py-3 rounded-lg text-sm font-semibold transition-all disabled:opacity-50 shadow-sm disabled:shadow-none hover:shadow-md transform hover:-translate-y-0.5"
                    >
                        📤 Emitir y Guardar
                    </button>
                    <button
                        type="button"
                        onClick={(e) => handleSubmit(e, 'draft')}
                        disabled={processing || !selectedClientId}
                        className="border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 px-6 py-3 rounded-lg text-sm font-medium transition-all disabled:opacity-50 shadow-sm"
                    >
                        💾 Guardar como borrador
                    </button>
                    <Link href="/facturas" className="text-sm font-medium text-gray-500 hover:text-gray-800 underline ml-auto">
                        Cancelar
                    </Link>
                </div>
            </form>
        </AppLayout>
    );
}
