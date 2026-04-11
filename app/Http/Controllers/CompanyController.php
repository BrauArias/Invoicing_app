<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(Request $request): Response
    {
        $companies = $request->user()->companies()->get();

        return Inertia::render('Companies/Index', [
            'companies' => $companies,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nif'  => ['nullable', 'string', 'max:20'],
        ]);

        $company = Company::create($data);
        $request->user()->companies()->attach($company->id, ['role' => 'owner']);
        $request->user()->update(['active_company_id' => $company->id]);

        return redirect()->route('settings.index')
            ->with('success', 'Empresa creada. Completa los datos.');
    }

    public function switchCompany(Request $request, Company $company)
    {
        $user = $request->user();

        // Verificar que el usuario pertenece a esta empresa
        if (!$user->companies()->where('companies.id', $company->id)->exists()) {
            abort(403);
        }

        $user->update(['active_company_id' => $company->id]);

        return redirect()->back()->with('success', "Empresa cambiada a {$company->name}");
    }
}
