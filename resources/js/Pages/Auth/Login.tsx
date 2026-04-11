import { useForm, Head } from '@inertiajs/react';
import type { FormEventHandler } from 'react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <>
            <Head title="Acceder" />
            <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#1e3a5f] to-[#0a1422] px-4">
                <div className="w-full max-w-md">
                    {/* Logo */}
                    <div className="text-center mb-8">
                        <div className="text-3xl font-bold text-white">
                            <span className="text-[#d4a017]">Inmo</span>VisualPro
                        </div>
                        <div className="text-white/50 text-sm mt-1">Sistema de Facturación</div>
                    </div>

                    <div className="bg-white rounded-2xl shadow-2xl p-8">
                        <h2 className="text-xl font-semibold text-gray-900 mb-6">Iniciar sesión</h2>

                        <form onSubmit={submit} className="space-y-5">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Correo electrónico
                                </label>
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={e => setData('email', e.target.value)}
                                    className={`w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] ${
                                        errors.email ? 'border-red-400 bg-red-50' : 'border-gray-300'
                                    }`}
                                    placeholder="tu@email.com"
                                    autoFocus
                                    required
                                />
                                {errors.email && (
                                    <p className="text-red-600 text-xs mt-1">{errors.email}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Contraseña
                                </label>
                                <input
                                    type="password"
                                    value={data.password}
                                    onChange={e => setData('password', e.target.value)}
                                    className={`w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a5f] ${
                                        errors.password ? 'border-red-400 bg-red-50' : 'border-gray-300'
                                    }`}
                                    placeholder="••••••••"
                                    required
                                />
                                {errors.password && (
                                    <p className="text-red-600 text-xs mt-1">{errors.password}</p>
                                )}
                            </div>

                            <div className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="remember"
                                    checked={data.remember}
                                    onChange={e => setData('remember', e.target.checked)}
                                    className="w-4 h-4 rounded border-gray-300 text-[#1e3a5f] focus:ring-[#1e3a5f]"
                                />
                                <label htmlFor="remember" className="text-sm text-gray-600">
                                    Recordarme
                                </label>
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full bg-[#1e3a5f] hover:bg-[#152843] text-white font-medium py-2.5 px-4 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {processing ? 'Accediendo...' : 'Acceder'}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}
