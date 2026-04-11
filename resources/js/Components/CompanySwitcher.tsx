import { useState, useRef, useEffect } from 'react';
import { Link, usePage } from '@inertiajs/react';
import type { Company } from '@/types';

interface PageProps {
    auth: {
        user: { name: string; email: string };
        companies?: Company[];
        active_company?: Company;
    };
}

export default function CompanySwitcher() {
    const { auth } = usePage<PageProps>().props;
    const [isOpen, setIsOpen] = useState(false);
    const dropdownRef = useRef<HTMLDivElement>(null);

    const activeCompany = auth.active_company;
    const companies = auth.companies || [];

    useEffect(() => {
        function handleClickOutside(event: MouseEvent) {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        }
        document.addEventListener("mousedown", handleClickOutside);
        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    if (!activeCompany && companies.length === 0) return null;

    return (
        <div className="relative" ref={dropdownRef}>
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="flex items-center justify-between w-full p-2 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none"
            >
                <div className="flex items-center gap-3 overflow-hidden">
                    {/* Avatar/Logo placeholder */}
                    <div className="flex-shrink-0 w-8 h-8 rounded bg-[#1e3a5f] text-white flex items-center justify-center font-bold text-xs uppercase">
                        {activeCompany?.name?.substring(0, 2) || 'CP'}
                    </div>
                    <div className="text-left overflow-hidden">
                        <div className="text-sm font-semibold text-gray-800 truncate">
                            {activeCompany?.name || 'Loading...'}
                        </div>
                        <div className="text-xs text-gray-500 truncate">Empresa activa</div>
                    </div>
                </div>
                <svg className="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            {isOpen && (
                <div className="absolute top-14 left-0 w-full bg-white border border-gray-200 shadow-lg rounded-lg overflow-hidden z-50">
                    <div className="px-3 py-2 bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Tus Empresas
                    </div>
                    <div className="max-h-60 overflow-y-auto">
                        {companies.map(company => (
                            <Link
                                key={company.id}
                                href={`/companies/${company.id}/switch`}
                                method="post"
                                as="button"
                                className={`w-full text-left px-4 py-3 flex items-center gap-3 hover:bg-gray-50 transition-colors ${company.id === activeCompany?.id ? 'bg-indigo-50 border-l-2 border-[#1e3a5f]' : 'border-l-2 border-transparent'}`}
                                onClick={() => setIsOpen(false)}
                            >
                                <div className={`flex-shrink-0 w-6 h-6 rounded flex items-center justify-center font-bold text-[10px] uppercase ${company.id === activeCompany?.id ? 'bg-[#1e3a5f] text-white' : 'bg-gray-200 text-gray-700'}`}>
                                    {company.name.substring(0, 2)}
                                </div>
                                <span className={`text-sm ${company.id === activeCompany?.id ? 'font-semibold text-gray-900' : 'text-gray-700'}`}>
                                    {company.name}
                                </span>
                            </Link>
                        ))}
                    </div>
                    <div className="border-t border-gray-100">
                        <Link
                            href="/companies/create"
                            className="block w-full text-left px-4 py-3 text-sm text-[#1e3a5f] hover:bg-gray-50 font-medium transition-colors"
                            onClick={() => setIsOpen(false)}
                        >
                            + Crear nueva empresa
                        </Link>
                    </div>
                </div>
            )}
        </div>
    );
}
