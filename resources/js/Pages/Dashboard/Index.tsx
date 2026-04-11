import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import type { Invoice } from '@/types';

interface Stats {
    monthly_revenue: number;
    yearly_revenue: number;
    pending_count: number;
    pending_amount: number;
    overdue_count: number;
}

interface Props {
    stats: Stats;
    recentInvoices: Invoice[];
}

function euros(value: number): string {
    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(value);
}

const statusConfig = {
    draft:     { label: 'Borrador',  color: 'bg-gray-100 text-gray-700' },
    sent:      { label: 'Enviada',   color: 'bg-blue-100 text-blue-700' },
    paid:      { label: 'Pagada',    color: 'bg-green-100 text-green-700' },
    overdue:   { label: 'Vencida',   color: 'bg-red-100 text-red-700' },
    cancelled: { label: 'Cancelada', color: 'bg-gray-100 text-gray-500 line-through' },
} as const;

export default function DashboardIndex({ stats, recentInvoices }: Props) {
    const now = new Date();
    const monthName = now.toLocaleString('es-ES', { month: 'long' });
    const year = now.getFullYear();

    return (
        <AppLayout title="Dashboard">
            <Head title="Dashboard" />

            {/* KPI Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
                <KpiCard
                    label={`Ingresos ${monthName}`}
                    value={euros(stats.monthly_revenue)}
                    sublabel="Facturas cobradas este mes"
                    color="border-l-[#d4a017]"
                    icon="💶"
                />
                <KpiCard
                    label={`Ingresos ${year}`}
                    value={euros(stats.yearly_revenue)}
                    sublabel="Total año actual"
                    color="border-l-[#1e3a5f]"
                    icon="📈"
                />
                <KpiCard
                    label="Pendiente de cobro"
                    value={euros(stats.pending_amount)}
                    sublabel={`${stats.pending_count} factura${stats.pending_count !== 1 ? 's' : ''} enviada${stats.pending_count !== 1 ? 's' : ''}`}
                    color="border-l-blue-400"
                    icon="⏳"
                />
                <KpiCard
                    label="Vencidas"
                    value={String(stats.overdue_count)}
                    sublabel="Facturas sin cobrar vencidas"
                    color={stats.overdue_count > 0 ? "border-l-red-500" : "border-l-green-400"}
                    icon={stats.overdue_count > 0 ? "⚠️" : "✅"}
                />
            </div>

            {/* Quick actions */}
            <div className="flex flex-wrap gap-3 mb-8">
                <Link
                    href="/facturas/nueva"
                    className="inline-flex items-center gap-2 bg-[#1e3a5f] hover:bg-[#152843] text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors"
                >
                    <span>+</span> Nueva factura
                </Link>
                <Link
                    href="/clientes/nuevo"
                    className="inline-flex items-center gap-2 border border-[#1e3a5f] text-[#1e3a5f] hover:bg-[#1e3a5f] hover:text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors"
                >
                    <span>+</span> Nuevo cliente
                </Link>
            </div>

            {/* Recent invoices */}
            <div className="bg-white rounded-xl border shadow-sm">
                <div className="px-6 py-4 border-b flex items-center justify-between">
                    <h2 className="text-base font-semibold text-gray-900">Últimas facturas</h2>
                    <Link href="/facturas" className="text-sm text-[#1e3a5f] hover:underline">
                        Ver todas →
                    </Link>
                </div>

                {recentInvoices.length === 0 ? (
                    <div className="px-6 py-12 text-center text-gray-400">
                        <div className="text-4xl mb-3">📄</div>
                        <p className="text-sm">No hay facturas aún.</p>
                        <Link href="/facturas/nueva" className="mt-3 inline-block text-sm text-[#1e3a5f] hover:underline font-medium">
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
                                    <th className="px-6 py-3 text-right font-medium">Total</th>
                                    <th className="px-6 py-3 text-center font-medium">Estado</th>
                                    <th className="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {recentInvoices.map((inv) => {
                                    const sc = statusConfig[inv.status] ?? statusConfig.draft;
                                    return (
                                        <tr key={inv.id} className="hover:bg-gray-50 transition-colors">
                                            <td className="px-6 py-3 font-mono font-medium text-[#1e3a5f]">
                                                {inv.full_number || '—'}
                                            </td>
                                            <td className="px-6 py-3 text-gray-700">{inv.client_name}</td>
                                            <td className="px-6 py-3 text-gray-500">
                                                {new Date(inv.issue_date).toLocaleDateString('es-ES')}
                                            </td>
                                            <td className="px-6 py-3 text-right font-medium text-gray-900">
                                                {euros(inv.total)}
                                            </td>
                                            <td className="px-6 py-3 text-center">
                                                <span className={`inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium ${sc.color}`}>
                                                    {sc.label}
                                                </span>
                                            </td>
                                            <td className="px-6 py-3 text-right">
                                                <Link
                                                    href={`/facturas/${inv.id}`}
                                                    className="text-[#1e3a5f] hover:underline text-xs"
                                                >
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
            </div>
        </AppLayout>
    );
}

function KpiCard({ label, value, sublabel, color, icon }: {
    label: string; value: string; sublabel: string; color: string; icon: string;
}) {
    return (
        <div className={`bg-white rounded-xl border shadow-sm p-5 border-l-4 ${color}`}>
            <div className="flex items-start justify-between">
                <div>
                    <p className="text-xs font-medium text-gray-500 uppercase tracking-wide">{label}</p>
                    <p className="text-2xl font-bold text-gray-900 mt-1">{value}</p>
                    <p className="text-xs text-gray-500 mt-1">{sublabel}</p>
                </div>
                <span className="text-2xl">{icon}</span>
            </div>
        </div>
    );
}
