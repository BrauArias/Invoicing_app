import { Link, router, usePage } from '@inertiajs/react';
import { useState, type ReactNode } from 'react';
import type { PageProps } from '@/types';

interface NavItem {
    label: string;
    href: string;
    routeName: string;
    icon: string;
}

const navItems: NavItem[] = [
    { label: 'Dashboard',  href: '/dashboard',  routeName: 'dashboard',       icon: '◼' },
    { label: 'Facturas',   href: '/facturas',   routeName: 'invoices.index',   icon: '📄' },
    { label: 'Clientes',   href: '/clientes',   routeName: 'clients.index',    icon: '👤' },
    { label: 'Servicios',  href: '/servicios',  routeName: 'products.index',   icon: '🗂' },
    { label: 'Ajustes',    href: '/ajustes',    routeName: 'settings.index',   icon: '⚙' },
];

interface Props {
    children: ReactNode;
    title?: string;
}

export default function AppLayout({ children, title }: Props) {
    const { auth, activeCompany, companies, flash } = usePage<PageProps>().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [companyMenuOpen, setCompanyMenuOpen] = useState(false);
    const currentPath = window.location.pathname;

    function switchCompany(companyId: number) {
        router.post(`/empresas/${companyId}/cambiar`);
        setCompanyMenuOpen(false);
    }

    return (
        <div className="min-h-screen flex bg-gray-50">
            {/* Sidebar */}
            <aside className={`
                fixed inset-y-0 left-0 z-50 w-64 flex flex-col
                bg-[#1e3a5f] text-white transform transition-transform duration-200
                ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}
                lg:relative lg:translate-x-0
            `}>
                {/* Logo / Header */}
                <div className="p-5 border-b border-white/10">
                    <div className="text-xl font-bold tracking-tight">
                        <span className="text-[#d4a017]">Inmo</span>VisualPro
                    </div>
                    <div className="text-xs text-white/50 mt-0.5">Facturación</div>
                </div>

                {/* Company Switcher */}
                {activeCompany && (
                    <div className="px-3 py-3 border-b border-white/10 relative">
                        <button
                            onClick={() => setCompanyMenuOpen(!companyMenuOpen)}
                            className="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition-colors text-left"
                        >
                            {activeCompany.logo_path ? (
                                <img src={`/storage/${activeCompany.logo_path}`} className="w-8 h-8 rounded-md bg-white object-contain p-0.5" alt={activeCompany.name} />
                            ) : (
                                <div className="w-8 h-8 rounded-md bg-[#d4a017] flex items-center justify-center text-[#1e3a5f] font-bold text-sm shrink-0">
                                    {activeCompany.name.charAt(0).toUpperCase()}
                                </div>
                            )}
                            <div className="flex-1 min-w-0">
                                <div className="text-sm font-medium truncate">{activeCompany.name}</div>
                                <div className="text-xs text-white/50">Serie: {activeCompany.invoice_series}</div>
                            </div>
                            <svg className={`w-4 h-4 text-white/50 transition-transform ${companyMenuOpen ? 'rotate-180' : ''}`}
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {companyMenuOpen && (
                            <div className="absolute left-3 right-3 top-full mt-1 bg-white rounded-lg shadow-xl border z-50 py-1">
                                {companies.map((c) => (
                                    <button
                                        key={c.id}
                                        onClick={() => switchCompany(c.id)}
                                        className={`w-full flex items-center gap-3 px-4 py-2 text-sm hover:bg-gray-50 text-left ${
                                            c.id === activeCompany.id ? 'text-[#1e3a5f] font-medium' : 'text-gray-700'
                                        }`}
                                    >
                                        {c.logo_path ? (
                                            <img src={`/storage/${c.logo_path}`} className="w-6 h-6 rounded bg-white object-contain p-0.5 border shadow-sm" alt={c.name} />
                                        ) : (
                                            <div className="w-6 h-6 rounded bg-[#1e3a5f] flex items-center justify-center text-white text-xs font-bold shrink-0">
                                                {c.name.charAt(0).toUpperCase()}
                                            </div>
                                        )}
                                        {c.name}
                                        {c.id === activeCompany.id && (
                                            <span className="ml-auto text-[#d4a017]">✓</span>
                                        )}
                                    </button>
                                ))}
                                <div className="border-t my-1" />
                                <Link
                                    href="/empresas/nueva"
                                    className="flex items-center gap-3 px-4 py-2 text-sm text-[#1e3a5f] hover:bg-gray-50"
                                    onClick={() => setCompanyMenuOpen(false)}
                                >
                                    <span className="text-[#d4a017]">+</span>
                                    Nueva empresa
                                </Link>
                            </div>
                        )}
                    </div>
                )}

                {/* Navigation */}
                <nav className="flex-1 px-3 py-4 space-y-1">
                    {navItems.map((item) => {
                        const isActive = currentPath.startsWith(item.href) ||
                            (item.href === '/dashboard' && currentPath === '/');
                        return (
                            <Link
                                key={item.routeName}
                                href={item.href}
                                className={`flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                                    isActive
                                        ? 'bg-[#d4a017] text-[#1e3a5f]'
                                        : 'text-white/80 hover:bg-white/10 hover:text-white'
                                }`}
                            >
                                <span className="w-5 text-center">{item.icon}</span>
                                {item.label}
                            </Link>
                        );
                    })}
                </nav>

                {/* User section */}
                <div className="p-3 border-t border-white/10">
                    <div className="flex items-center gap-3 px-3 py-2">
                        <div className="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-medium">
                            {auth.user.name.charAt(0).toUpperCase()}
                        </div>
                        <div className="flex-1 min-w-0">
                            <div className="text-sm font-medium truncate">{auth.user.name}</div>
                            <div className="text-xs text-white/50 truncate">{auth.user.email}</div>
                        </div>
                    </div>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        className="w-full mt-1 flex items-center gap-2 px-3 py-2 text-sm text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
                    >
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Cerrar sesión
                    </Link>
                </div>
            </aside>

            {/* Mobile overlay */}
            {sidebarOpen && (
                <div
                    className="fixed inset-0 bg-black/50 z-40 lg:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}

            {/* Main content */}
            <div className="flex-1 flex flex-col min-w-0">
                {/* Top bar */}
                <header className="bg-white border-b px-4 py-3 flex items-center gap-4 lg:px-6">
                    <button
                        className="lg:hidden p-2 rounded-md hover:bg-gray-100"
                        onClick={() => setSidebarOpen(true)}
                    >
                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    {title && (
                        <h1 className="text-lg font-semibold text-gray-900">{title}</h1>
                    )}
                </header>

                {/* Flash messages */}
                {(flash?.success || flash?.error) && (
                    <div className={`mx-4 mt-4 lg:mx-6 p-4 rounded-lg text-sm ${
                        flash.success ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'
                    }`}>
                        {flash.success || flash.error}
                    </div>
                )}

                {/* Page content */}
                <main className="flex-1 p-4 lg:p-6">
                    {children}
                </main>
            </div>
        </div>
    );
}
