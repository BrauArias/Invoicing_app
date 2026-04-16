<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $activeCompany = null;
        $companies = [];

        if ($user) {
            $companies = $user->companies()->orderBy('name')->get(['companies.id', 'companies.name', 'companies.logo_path', 'companies.invoice_series']);
            $activeCompany = $user->activeCompany;
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'email'             => $user->email,
                    'active_company_id' => $user->active_company_id,
                ] : null,
            ],
            'activeCompany' => $activeCompany,
            'companies'     => $companies,
            'flash' => [
                'success' => session('success'),
                'error'   => session('error'),
            ],
        ]);
    }
}
