<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request, Company $company): Response
    {
        $query = $company->clients()
            ->withCount('invoices')
            ->withSum(['invoices' => fn($q) => $q->where('status', 'paid')], 'total');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nif', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('name')->paginate(20)->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => ['search' => $request->get('search', '')],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Clients/Create');
    }

    public function store(Request $request, Company $company)
    {
        $data = $this->validateClient($request);
        $data['company_id'] = $company->id;

        Client::create($data);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    public function edit(Company $company, Client $client): Response
    {
        $this->authorizeClient($company, $client);

        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    public function update(Request $request, Company $company, Client $client)
    {
        $this->authorizeClient($company, $client);
        $data = $this->validateClient($request);
        $client->update($data);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Company $company, Client $client)
    {
        $this->authorizeClient($company, $client);
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Cliente eliminado.');
    }

    private function validateClient(Request $request): array
    {
        return $request->validate([
            'type'            => ['required', 'in:individual,business'],
            'name'            => ['required', 'string', 'max:255'],
            'trade_name'      => ['nullable', 'string', 'max:255'],
            'nif'             => ['nullable', 'string', 'max:20'],
            'email'           => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'website'         => ['nullable', 'url', 'max:255'],
            'address'         => ['nullable', 'string', 'max:255'],
            'city'            => ['nullable', 'string', 'max:100'],
            'province'        => ['nullable', 'string', 'max:100'],
            'postal_code'     => ['nullable', 'string', 'max:10'],
            'country'         => ['nullable', 'string', 'size:2'],
            'vat_exempt'      => ['boolean'],
            'irpf_applicable' => ['boolean'],
            'irpf_rate'       => ['nullable', 'numeric'],
            'notes'           => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function authorizeClient(Company $company, Client $client): void
    {
        if ($client->company_id !== $company->id) {
            abort(403);
        }
    }
}
