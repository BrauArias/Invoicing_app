<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// ─── Autenticación (públicas) ────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ─── App (protegidas) ────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Middleware para resolver la empresa activa en cada request
    Route::middleware([\App\Http\Middleware\SetActiveCompany::class])->group(function () {

        // Dashboard: redirigir al dashboard de la empresa activa
        Route::get('/', function () {
            $user    = auth()->user();
            $company = $user?->activeCompany;

            if (!$company) {
                return redirect()->route('companies.create');
            }

            return redirect()->route('dashboard');
        })->name('home');

        // Dashboard con la empresa activa como parámetro inyectado
        Route::get('/dashboard', function () {
            $company = auth()->user()?->activeCompany;
            if (!$company) {
                return redirect()->route('companies.create');
            }
            return app(DashboardController::class)->index($company);
        })->name('dashboard');

        // ─── Empresas ────────────────────────────────────────────────────────
        Route::get('/empresas', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('/empresas/nueva', fn() => \Inertia\Inertia::render('Companies/Create'))->name('companies.create');
        Route::post('/empresas', [CompanyController::class, 'store'])->name('companies.store');
        Route::post('/empresas/{company}/cambiar', [CompanyController::class, 'switchCompany'])->name('companies.switch');

        // ─── Ajustes de empresa ─────────────────────────────────────────────
        Route::get('/ajustes', function () {
            $company = auth()->user()?->activeCompany;
            if (!$company) return redirect()->route('companies.create');
            return app(CompanySettingsController::class)->index($company);
        })->name('settings.index');

        Route::post('/ajustes', function (\Illuminate\Http\Request $request) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(CompanySettingsController::class)->update($request, $company);
        })->name('settings.update');

        Route::post('/ajustes/logo', function (\Illuminate\Http\Request $request) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(CompanySettingsController::class)->uploadLogo($request, $company);
        })->name('settings.logo');

        Route::delete('/ajustes/logo', function () {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(CompanySettingsController::class)->deleteLogo($company);
        })->name('settings.logo.delete');

        // ─── Clientes ────────────────────────────────────────────────────────
        Route::get('/clientes', function (\Illuminate\Http\Request $request) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(ClientController::class)->index($request, $company);
        })->name('clients.index');

        Route::get('/clientes/nuevo', [ClientController::class, 'create'])->name('clients.create');

        Route::post('/clientes', function (\Illuminate\Http\Request $request) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(ClientController::class)->store($request, $company);
        })->name('clients.store');

        Route::get('/clientes/{client}/editar', function (\App\Models\Client $client) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(ClientController::class)->edit($company, $client);
        })->name('clients.edit');

        Route::put('/clientes/{client}', function (\Illuminate\Http\Request $request, \App\Models\Client $client) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(ClientController::class)->update($request, $company, $client);
        })->name('clients.update');

        Route::delete('/clientes/{client}', function (\App\Models\Client $client) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(ClientController::class)->destroy($company, $client);
        })->name('clients.destroy');

        // ─── Servicios/Productos ─────────────────────────────────────────────
        Route::get('/servicios', function () {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(ProductController::class)->index($company);
        })->name('products.index');

        Route::get('/servicios/nuevo', [ProductController::class, 'create'])->name('products.create');

        Route::post('/servicios', function (\Illuminate\Http\Request $request) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(ProductController::class)->store($request, $company);
        })->name('products.store');

        Route::get('/servicios/{product}/editar', function (\App\Models\Product $product) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(ProductController::class)->edit($company, $product);
        })->name('products.edit');

        Route::put('/servicios/{product}', function (\Illuminate\Http\Request $request, \App\Models\Product $product) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(ProductController::class)->update($request, $company, $product);
        })->name('products.update');

        Route::delete('/servicios/{product}', function (\App\Models\Product $product) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(ProductController::class)->destroy($company, $product);
        })->name('products.destroy');

        // ─── Facturas ────────────────────────────────────────────────────────
        Route::get('/facturas', function (\Illuminate\Http\Request $request) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(InvoiceController::class)->index($request, $company);
        })->name('invoices.index');

        Route::get('/facturas/nueva', function (\Illuminate\Http\Request $request) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(InvoiceController::class)->create($request, $company);
        })->name('invoices.create');

        Route::post('/facturas', function (\Illuminate\Http\Request $request) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(InvoiceController::class)->store($request, $company);
        })->name('invoices.store');

        Route::get('/facturas/{invoice}', function (\App\Models\Invoice $invoice) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(InvoiceController::class)->show($company, $invoice);
        })->name('invoices.show');

        Route::post('/facturas/{invoice}/emitir', function (\App\Models\Invoice $invoice) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(InvoiceController::class)->emit($company, $invoice);
        })->name('invoices.emit');

        Route::post('/facturas/{invoice}/cobrada', function (\Illuminate\Http\Request $request, \App\Models\Invoice $invoice) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(InvoiceController::class)->markPaid($request, $company, $invoice);
        })->name('invoices.paid');

        Route::get('/facturas/{invoice}/pdf', function (\App\Models\Invoice $invoice) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(InvoiceController::class)->download($company, $invoice);
        })->name('invoices.pdf');

        Route::post('/facturas/{invoice}/duplicar', function (\App\Models\Invoice $invoice) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(InvoiceController::class)->duplicate($company, $invoice);
        })->name('invoices.duplicate');

        Route::post('/facturas/{invoice}/cancelar', function (\App\Models\Invoice $invoice) {
            $company = auth()->user()?->activeCompany;
            abort_unless($company, 403);
            return app(InvoiceController::class)->cancel($company, $invoice);
        })->name('invoices.cancel');
    });
});
