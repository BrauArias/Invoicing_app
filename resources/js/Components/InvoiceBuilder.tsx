import React from 'react';
import type { Product } from '@/types';

export interface LineData {
    product_id: number | null;
    description: string;
    quantity: string;
    unit: string;
    unit_price: string;
    discount: string;
    vat_rate: string;
}

export const emptyLine = (vat = 21): LineData => ({
    product_id: null, description: '', quantity: '1', unit: 'proyecto',
    unit_price: '', discount: '0', vat_rate: String(vat),
});

export const calcLine = (l: LineData) => {
    const qty      = parseFloat(l.quantity)   || 0;
    const price    = parseFloat(l.unit_price) || 0;
    const discount = parseFloat(l.discount)   || 0;
    const vatRate  = parseFloat(l.vat_rate)   || 0;
    const subtotal = Math.round(qty * price * (1 - discount / 100) * 100) / 100;
    const vatAmt   = Math.round(subtotal * vatRate / 100 * 100) / 100;
    return { subtotal, vatAmt, total: subtotal + vatAmt };
};

const euros = (v: number) =>
    new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(v);

interface InvoiceBuilderProps {
    lines: LineData[];
    setLines: React.Dispatch<React.SetStateAction<LineData[]>>;
    products: Product[];
    defaults: { vat_rate: number };
    errors?: string;
    applyIrpf: boolean;
    irpfRate: number;
    subtotal: number;
    total: number;
    vatBreakdown: { rate: number; base: number; amount: number }[];
    irpfAmount: number;
}

export default function InvoiceBuilder({
    lines, setLines, products, defaults, errors, 
    applyIrpf, irpfRate, subtotal, total, vatBreakdown, irpfAmount
}: InvoiceBuilderProps) {
    function updateLine(idx: number, field: keyof LineData, value: string | number | null) {
        setLines(ls => ls.map((l, i) => i === idx ? { ...l, [field]: value } : l));
    }

    function addLine() {
        setLines(ls => [...ls, emptyLine(defaults.vat_rate)]);
    }

    function removeLine(idx: number) {
        if (lines.length === 1) return;
        setLines(ls => ls.filter((_, i) => i !== idx));
    }

    function selectProduct(idx: number, productId: string) {
        const product = products.find(p => p.id === parseInt(productId));
        if (!product) { updateLine(idx, 'product_id', null); return; }
        setLines(ls => ls.map((l, i) => i === idx ? {
            ...l,
            product_id:  product.id,
            description: product.name,
            unit_price:  String(product.unit_price),
            vat_rate:    String(product.vat_rate),
            unit:        product.unit,
        } : l));
    }

    return (
        <div className="bg-white rounded-xl border shadow-sm">
            <div className="px-6 py-4 border-b flex items-center justify-between">
                <h2 className="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                    Líneas de factura
                </h2>
                {errors && <span className="text-sm text-red-600">{errors}</span>}
            </div>

            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="text-xs text-gray-500 uppercase border-b bg-gray-50">
                            <th className="px-3 py-2 text-left w-1/3">Descripción *</th>
                            <th className="px-3 py-2 text-center w-20">Cantidad</th>
                            <th className="px-3 py-2 text-center w-24">Unidad</th>
                            <th className="px-3 py-2 text-right w-28">Precio unit.</th>
                            <th className="px-3 py-2 text-center w-20">Dto. %</th>
                            <th className="px-3 py-2 text-center w-24">IVA %</th>
                            <th className="px-3 py-2 text-right w-28">Total línea</th>
                            <th className="px-3 py-2 w-8"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {lines.map((line, idx) => {
                            const { total: lineTotal } = calcLine(line);
                            return (
                                <tr key={idx} className="border-b hover:bg-gray-50">
                                    <td className="px-3 py-2">
                                        <div className="space-y-1.5">
                                            {products.length > 0 && (
                                                <select
                                                    value={line.product_id ?? ''}
                                                    onChange={e => selectProduct(idx, e.target.value)}
                                                    className="w-full px-2 py-1 border border-gray-200 rounded text-xs text-gray-500 bg-white"
                                                >
                                                    <option value="">— Catálogo —</option>
                                                    {products.map(p => (
                                                        <option key={p.id} value={p.id}>{p.name}</option>
                                                    ))}
                                                </select>
                                            )}
                                            <input
                                                type="text"
                                                value={line.description}
                                                onChange={e => updateLine(idx, 'description', e.target.value)}
                                                className="w-full px-2 py-1.5 border border-gray-300 rounded text-sm"
                                                placeholder="Descripción del servicio"
                                                required
                                            />
                                        </div>
                                    </td>
                                    <td className="px-3 py-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            value={line.quantity}
                                            onChange={e => updateLine(idx, 'quantity', e.target.value)}
                                            className="w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-right"
                                        />
                                    </td>
                                    <td className="px-3 py-2">
                                        <select
                                            value={line.unit}
                                            onChange={e => updateLine(idx, 'unit', e.target.value)}
                                            className="w-full px-2 py-1.5 border border-gray-300 rounded text-sm bg-white"
                                        >
                                            <option>proyecto</option>
                                            <option>hora</option>
                                            <option>unidad</option>
                                            <option>imagen</option>
                                            <option>video</option>
                                            <option>mes</option>
                                        </select>
                                    </td>
                                    <td className="px-3 py-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            value={line.unit_price}
                                            onChange={e => updateLine(idx, 'unit_price', e.target.value)}
                                            className="w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-right"
                                            placeholder="0.00"
                                        />
                                    </td>
                                    <td className="px-3 py-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            value={line.discount}
                                            onChange={e => updateLine(idx, 'discount', e.target.value)}
                                            className="w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-right"
                                        />
                                    </td>
                                    <td className="px-3 py-2">
                                        <select
                                            value={line.vat_rate}
                                            onChange={e => updateLine(idx, 'vat_rate', e.target.value)}
                                            className="w-full px-2 py-1.5 border border-gray-300 rounded text-sm bg-white"
                                        >
                                            <option value="21">21%</option>
                                            <option value="10">10%</option>
                                            <option value="4">4%</option>
                                            <option value="0">0%</option>
                                        </select>
                                    </td>
                                    <td className="px-3 py-2 text-right font-medium whitespace-nowrap">
                                        {euros(lineTotal)}
                                    </td>
                                    <td className="px-3 py-2 text-center">
                                        <button
                                            type="button"
                                            onClick={() => removeLine(idx)}
                                            disabled={lines.length === 1}
                                            className="text-gray-400 hover:text-red-500 disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer transition-colors"
                                            title="Eliminar línea"
                                        >
                                            ✕
                                        </button>
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>

            <div className="px-6 py-4 bg-gray-50 flex items-start justify-between rounded-b-xl">
                <button
                    type="button"
                    onClick={addLine}
                    className="text-sm text-[#1e3a5f] hover:text-[#1e3a5f]/80 font-semibold px-4 py-2 border border-[#1e3a5f]/20 rounded-lg bg-white shadow-sm hover:shadow transition-all"
                >
                    + Añadir línea
                </button>

                <div className="text-right space-y-1.5 min-w-[280px]">
                    <div className="flex justify-between gap-8 text-sm text-gray-600 px-2">
                        <span>Base imponible</span>
                        <span className="font-medium">{euros(subtotal)}</span>
                    </div>
                    {vatBreakdown.map(vb => (
                        <div key={vb.rate} className="flex justify-between gap-8 text-sm text-gray-600 px-2 pb-1">
                            <span>IVA {vb.rate}%</span>
                            <span>{euros(vb.amount)}</span>
                        </div>
                    ))}
                    {applyIrpf && (
                        <div className="flex justify-between gap-8 text-sm text-red-600/90 px-2 pb-1 border-t border-gray-200/60 pt-2 mt-1">
                            <span>Retención IRPF {irpfRate}%</span>
                            <span>−{euros(irpfAmount)}</span>
                        </div>
                    )}
                    <div className="flex justify-between gap-8 pt-3 border-t border-gray-200 font-bold text-lg text-gray-900 px-2">
                        <span>TOTAL</span>
                        <span className="text-[#1e3a5f]">{euros(total)}</span>
                    </div>
                </div>
            </div>
        </div>
    );
}
