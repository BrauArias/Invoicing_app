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
        // Credenciales Demo para Beta Abierta
        $betaUsers = [
            ['name' => 'Braulio Arias', 'email' => 'admin@powerhelp.es', 'password' => 'Secreto123!'],
        ];

        $users = [];
        foreach ($betaUsers as $bu) {
            $users[] = User::factory()->create([
                'name' => $bu['name'],
                'email' => $bu['email'],
                'password' => Hash::make($bu['password']),
            ]);
        }
        
        foreach ($users as $index => $user) {
            // Crear empresa principal
            $company = Company::create([
                'name'             => 'InmoVisualPro',
                'trade_name'       => 'InmoVisualPro',
                'nif'              => '', // Rellenar en Ajustes
                'email'            => 'admin@powerhelp.es',
                'address'          => '',
                'city'             => 'Valencia',
                'province'         => 'Valencia',
                'postal_code'      => '46001',
                'country'          => 'ES',
                'invoice_series'   => 'F',
                'invoice_template' => 'modern',
                'primary_color'    => '#1e3a5f',
                'accent_color'     => '#d4a017',
                'default_vat_rate' => 21,
                'irpf_applicable'  => true,
                'irpf_rate'        => 15,
                'show_bank_details'=> false,
            ]);

            $user->companies()->attach($company->id, ['role' => 'owner']);
            $user->update(['active_company_id' => $company->id]);

            // Servicios demo de renders 3D
            $products = [
                ['code' => 'REXT-01',  'name' => 'Render Exterior',           'desc' => 'Render fotorrealista de fachada exterior',          'price' => 350,  'unit' => 'imagen'],
                ['code' => 'RINT-01',  'name' => 'Render Interior',            'desc' => 'Render de espacio interior con iluminación HDR',     'price' => 280,  'unit' => 'imagen'],
                ['code' => 'RAER-01',  'name' => 'Vista Aérea / Drone',        'desc' => 'Render aéreo con contexto urbano o natural',          'price' => 420,  'unit' => 'imagen'],
                ['code' => 'ANIM-01',  'name' => 'Animación Walkthrough',      'desc' => 'Vídeo de recorrido interior (hasta 60 seg)',           'price' => 1200, 'unit' => 'video'],
                ['code' => 'PLAN-01',  'name' => 'Plano 2D Renderizado',       'desc' => 'Planta arquitectónica con acabados',                   'price' => 120,  'unit' => 'imagen'],
                ['code' => 'PACK-01',  'name' => 'Pack Promocional Inmueble',  'desc' => '3 renders exteriores + 2 interiores + 1 aéreo',        'price' => 1500, 'unit' => 'proyecto'],
            ];

            foreach ($products as $p) {
                Product::create([
                    'company_id'  => $company->id,
                    'code'        => $p['code'],
                    'name'        => $p['name'],
                    'description' => $p['desc'],
                    'unit_price'  => $p['price'],
                    'vat_rate'    => 21,
                    'unit'        => $p['unit'],
                    'is_active'   => true,
                ]);
            }

            // Cliente demo
            Client::create([
                'company_id'      => $company->id,
                'type'            => 'business',
                'name'            => 'Promotora Mediterráneo S.L.',
                'nif'             => 'B12345678',
                'email'           => 'proyectos@promotora-med.es',
                'phone'           => '+34 963 000 001',
                'address'         => 'Av. del Puerto, 15',
                'city'            => 'Valencia',
                'province'        => 'Valencia',
                'postal_code'     => '46023',
                'irpf_applicable' => true,
                'irpf_rate'       => 15,
            ]);
        }
    }
}
