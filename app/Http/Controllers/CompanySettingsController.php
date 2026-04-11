<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CompanySettingsController extends Controller
{
    public function index(Company $company): Response
    {
        return Inertia::render('Settings/Index', [
            'company' => $company,
        ]);
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'trade_name'           => ['nullable', 'string', 'max:255'],
            'nif'                  => ['nullable', 'string', 'max:20'],
            'address'              => ['nullable', 'string', 'max:255'],
            'city'                 => ['nullable', 'string', 'max:100'],
            'province'             => ['nullable', 'string', 'max:100'],
            'postal_code'          => ['nullable', 'string', 'max:10'],
            'country'              => ['nullable', 'string', 'size:2'],
            'phone'                => ['nullable', 'string', 'max:30'],
            'email'                => ['nullable', 'email', 'max:255'],
            'website'              => ['nullable', 'url', 'max:255'],
            'iban'                 => ['nullable', 'string', 'max:34'],
            'swift'                => ['nullable', 'string', 'max:11'],
            'show_bank_details'    => ['boolean'],
            'invoice_series'       => ['required', 'string', 'max:5'],
            'rectification_series' => ['required', 'string', 'max:5'],
            'quote_series'         => ['required', 'string', 'max:5'],
            'default_vat_rate'     => ['required', 'numeric', 'in:0,4,10,21'],
            'irpf_applicable'      => ['boolean'],
            'irpf_rate'            => ['nullable', 'numeric', 'in:7,15,19'],
            'invoice_template'     => ['required', 'in:classic,modern,minimal'],
            'primary_color'        => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color'         => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'invoice_footer_text'  => ['nullable', 'string', 'max:1000'],
            'invoice_header_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $company->update($data);

        return back()->with('success', 'Configuración guardada correctamente.');
    }

    public function uploadLogo(Request $request, Company $company)
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
        ]);

        // Eliminar logo anterior
        if ($company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
        }

        $path = $request->file('logo')->store('logos', 'public');
        $company->update(['logo_path' => $path]);

        return back()->with('success', 'Logo actualizado correctamente.');
    }

    public function deleteLogo(Company $company)
    {
        if ($company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
            $company->update(['logo_path' => null]);
        }

        return back()->with('success', 'Logo eliminado.');
    }
}
