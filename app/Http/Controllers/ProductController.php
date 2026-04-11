<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Company $company): Response
    {
        $products = $company->products()->orderBy('name')->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Products/Create');
    }

    public function store(Request $request, Company $company)
    {
        $data = $this->validateProduct($request);
        $data['company_id'] = $company->id;

        Product::create($data);

        return redirect()->route('products.index')
            ->with('success', 'Servicio creado correctamente.');
    }

    public function edit(Company $company, Product $product): Response
    {
        $this->authorizeProduct($company, $product);

        return Inertia::render('Products/Edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Company $company, Product $product)
    {
        $this->authorizeProduct($company, $product);
        $product->update($this->validateProduct($request));

        return redirect()->route('products.index')
            ->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Company $company, Product $product)
    {
        $this->authorizeProduct($company, $product);
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Servicio eliminado.');
    }

    private function validateProduct(Request $request): array
    {
        return $request->validate([
            'code'        => ['nullable', 'string', 'max:50'],
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'unit_price'  => ['required', 'numeric', 'min:0'],
            'vat_rate'    => ['required', 'numeric', 'in:0,4,10,21'],
            'unit'        => ['required', 'string', 'max:30'],
            'is_active'   => ['boolean'],
        ]);
    }

    private function authorizeProduct(Company $company, Product $product): void
    {
        if ($product->company_id !== $company->id) {
            abort(403);
        }
    }
}
