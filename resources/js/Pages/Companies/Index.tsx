import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import type { Company, PageProps } from '@/types';

interface Props { companies: Company[]; }

export default function CompaniesIndex({ companies }: Props) {
    const { activeCompany } = usePage<PageProps>().props;

    function switchTo(id: number) {
        router.post('/empresas/cambiar', { company_id: id });
    }

    return (
        <AppLayout title="Mis empresas">
            <Head title="Mis empresas" />

            <div className="flex items-center justify-between mb-6">
                <p className="text-sm text-gray-500">{companies.length} empresa{companies.length !== 1 ? 's' : ''} configurada{companies.length !== 1 ? 's' : ''}</p>
                <Link
                    href="/empresas/nueva"
                    className="inline-flex items-center gap-2 bg-[#1e3a5f] hover:bg-[#152843] text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors"
                >
                    + Nueva empresa
                </Link>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {companies.map((company) => {
                    const isActive = activeCompany?.id === company.id;
                    return (
                        <div
                            key={company.id}
                            className={`bg-white rounded-xl border shadow-sm p-5 flex flex-col gap-4 transition-all ${
                                isActive ? 'border-[#1e3a5f] ring-2 ring-[#1e3a5f]/20' : 'hover:border-gray-300'
                            }`}
                        >
                            {/* Logo + name */}
                            <div className="flex items-center gap-3">
                                {company.logo_url ? (
                                    <img src={company.logo_url} alt={company.name} className="w-12 h-12 object-contain rounded-lg border" />
                                ) : (
                                    <div className="w-12 h-12 rounded-lg bg-[#1e3a5f] flex items-center justify-center text-white font-bold text-xl">
                                        {company.name.charAt(0).toUpperCase()}
                                    </div>
                                )}
                                <div className="flex-1 min-w-0">
                                    <h3 className="font-semibold text-gray-900 truncate">{company.name}</h3>
                                    {company.trade_name && (
                                        <p className="text-xs text-gray-500 truncate">{company.trade_name}</p>
                                    )}
                                </div>
                                {isActive && (
                                    <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-[#1e3a5f] text-white shrink-0">
                                        Activa
                                    </span>
                                )}
                            </div>

                            {/* Info */}
                            <div className="text-sm text-gray-600 space-y-1">
                                {company.nif && <div><span className="text-gray-400">NIF:</span> {company.nif}</div>}
                                {company.city && <div><span className="text-gray-400">Ciudad:</span> {company.city}</div>}
                                {company.email && <div className="truncate"><span className="text-gray-400">Email:</span> {company.email}</div>}
                            </div>

                            {/* Template badge */}
                            <div className="flex items-center gap-2">
                                <span className="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600 capitalize">
                                    Plantilla: {company.invoice_template ?? 'classic'}
                                </span>
                                <span className="w-4 h-4 rounded-full border border-gray-200 shrink-0"
                                    style={{ backgroundColor: company.primary_color ?? '#1e3a5f' }} />
                            </div>

                            {/* Actions */}
                            <div className="flex gap-2 pt-1 border-t">
                                {!isActive && (
                                    <button
                                        onClick={() => switchTo(company.id)}
                                        className="flex-1 text-center text-xs font-medium text-[#1e3a5f] hover:bg-blue-50 py-1.5 rounded-lg transition-colors"
                                    >
                                        Activar
                                    </button>
                                )}
                                <Link
                                    href="/ajustes"
                                    onClick={() => !isActive && switchTo(company.id)}
                                    className="flex-1 text-center text-xs font-medium text-gray-600 hover:bg-gray-50 py-1.5 rounded-lg transition-colors"
                                >
                                    Ajustes
                                </Link>
                            </div>
                        </div>
                    );
                })}

                {/* Add new card */}
                <Link
                    href="/empresas/nueva"
                    className="bg-white rounded-xl border border-dashed border-gray-300 shadow-sm p-5 flex flex-col items-center justify-center gap-2 text-gray-400 hover:border-[#1e3a5f] hover:text-[#1e3a5f] transition-colors min-h-[200px]"
                >
                    <span className="text-3xl">+</span>
                    <span className="text-sm font-medium">Añadir empresa</span>
                </Link>
            </div>
        </AppLayout>
    );
}
