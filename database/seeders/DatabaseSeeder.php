<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario principal
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => Hash::make('password'),
        ]);

        // Crear una empresa (InmoVisualPro)
        $company = Company::create([
            'name' => 'InmoVisualPro S.L.',
            'trade_name' => 'InmoVisualPro',
            'nif' => 'B12345678',
            'email' => 'info@inmovisualpro.com',
            'phone' => '+34 600 000 000',
            'address' => 'Calle de la Inmobiliaria 123',
            'city' => 'Valencia',
            'province' => 'Valencia',
            'postal_code' => '46001',
            'country' => 'España',
            'invoice_template' => 'modern',
            'primary_color' => '#1e3a5f',
            'accent_color' => '#d4a017',
            'show_bank_details' => true,
            'iban' => 'ES91 2100 0418 4012 3456 7890',
        ]);

        // Asegurarse de que el status sync no falle
        $user->companies()->attach($company->id, ['role' => 'owner']);
        $user->update(['active_company_id' => $company->id]);

        // Crear clientes de prueba
        $client1 = Client::create([
            'company_id' => $company->id,
            'type' => 'business',
            'name' => 'Constructora Mediterránea',
            'nif' => 'B98765432',
            'email' => 'admin@mediterranea.es',
            'address' => 'Av. del Mar 45',
            'city' => 'Alicante',
            'postal_code' => '03001',
            'irpf_applicable' => true,
        ]);

        Client::create([
            'company_id' => $company->id,
            'type' => 'individual',
            'name' => 'Juan Arquitecto',
            'nif' => '12345678Z',
            'email' => 'juan@arquitectura.com',
            'address' => 'Calle Mayor 1',
            'city' => 'Madrid',
            'postal_code' => '28001',
            'irpf_applicable' => false,
        ]);

        // Crear productos de prueba (Catálogo de Renders 3D)
        $products = [
            ['code' => 'R3D-EXT', 'name' => 'Render 3D Exterior Alta Resolución', 'price' => 250, 'unit' => 'imagen'],
            ['code' => 'R3D-INT', 'name' => 'Render 3D Interior Fotorrealista', 'price' => 200, 'unit' => 'imagen'],
            ['code' => 'TOUR-360', 'name' => 'Tour Virtual 360 Interactiu', 'price' => 600, 'unit' => 'proyecto'],
            ['code' => 'VID-ANIM', 'name' => 'Video Animación 3D (Minuto)', 'price' => 800, 'unit' => 'video'],
        ];

        foreach ($products as $p) {
            Product::create([
                'company_id' => $company->id,
                'code' => $p['code'],
                'name' => $p['name'],
                'description' => 'Servicio de ' . strtolower($p['name']),
                'unit_price' => $p['price'],
                'vat_rate' => 21,
                'unit' => $p['unit'],
                'is_active' => true,
            ]);
        }
    }
}
