<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Service;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CREATE ADMIN
        $admin = User::firstOrCreate(
            ['email' => 'admin@petspa.com'],
            [
                'nombre_completo' => 'Administrador Principal',
                'password' => 'password', // Will be hashed automatically by cast
                'telefono' => '70000001',
                'ci' => '1234567',
                'is_active' => true,
                'email_verified_at' => now(),
                'password_change_required' => false
            ]
        );
        $admin->assignRole('ADMIN');

        // 2. CREATE RECEPCION
        $recepcion = User::firstOrCreate(
            ['email' => 'recepcion@petspa.com'],
            [
                'nombre_completo' => 'Recepcionista Recepción',
                'password' => 'password',
                'telefono' => '70000002',
                'ci' => '2345678',
                'is_active' => true,
                'email_verified_at' => now(),
                'password_change_required' => false
            ]
        );
        $recepcion->assignRole('RECEPCION');

        DB::table('empleados')->updateOrInsert(
            ['usuario_id' => $recepcion->id],
            [
                'tipo_empleado' => 'RECEPCION',
                'especialidad' => 'Administración',
                'turno' => 'COMPLETO',
                'fecha_contratacion' => now(),
                'max_servicios_simultaneos' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 3. CREATE GROOMER
        $groomer = User::firstOrCreate(
            ['email' => 'groomer1@petspa.com'],
            [
                'nombre_completo' => 'Estilista Groomer One',
                'password' => 'password',
                'telefono' => '70000003',
                'ci' => '3456789',
                'is_active' => true,
                'email_verified_at' => now(),
                'password_change_required' => false
            ]
        );
        $groomer->assignRole('GROOMER');

        DB::table('empleados')->updateOrInsert(
            ['usuario_id' => $groomer->id],
            [
                'tipo_empleado' => 'GROOMER',
                'especialidad' => 'Estilismo canino',
                'turno' => 'COMPLETO',
                'fecha_contratacion' => now(),
                'max_servicios_simultaneos' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        DB::table('groomers')->updateOrInsert(
            ['groomer_id' => $groomer->id],
            [
                'especialidades' => '{BAÑO,CORTE,ESTETICA}', // Postgres Text Array literal representation
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Seed some shift hours for Groomer (dia_semana: 1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri)
        for ($i = 1; $i <= 5; $i++) {
            DB::table('horario_trabajo_groomer')->updateOrInsert(
                [
                    'groomer_id' => $groomer->id,
                    'dia_semana' => $i
                ],
                [
                    'id' => Str::uuid(),
                    'hora_inicio' => '09:00:00',
                    'hora_fin' => '18:00:00',
                    'pausa_inicio' => '13:00:00',
                    'pausa_fin' => '14:00:00',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        // 4. CREATE CLIENTE
        $cliente = User::firstOrCreate(
            ['email' => 'cliente@petspa.com'],
            [
                'nombre_completo' => 'Juan Pérez Cliente',
                'password' => 'password',
                'telefono' => '70000004',
                'ci' => '4567890',
                'is_active' => true,
                'email_verified_at' => now(),
                'password_change_required' => false
            ]
        );
        $cliente->assignRole('CLIENTE');

        DB::table('clientes')->updateOrInsert(
            ['usuario_id' => $cliente->id],
            [
                'direccion' => 'Av. San Martín #456, Equipetrol',
                'preferencia_comunicacion' => 'WHATSAPP',
                'newsletter' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 5. SEED DEFAULT SERVICES
        $services = [
            [
                'nombre' => 'Baño rápido',
                'slug' => 'banio-rapido',
                'descripcion' => 'Lavado higiénico con shampoo premium, secado y cepillado rápido.',
                'duracion_base_minutos' => 30,
                'precio_base' => 40.00,
                'categoria' => 'GROOMING',
                'activo' => true
            ],
            [
                'nombre' => 'Baño completo',
                'slug' => 'banio-completo',
                'descripcion' => 'Lavado profundo con shampoo específico, acondicionador, secado completo, corte de uñas y limpieza de oídos.',
                'duracion_base_minutos' => 60,
                'precio_base' => 70.00,
                'categoria' => 'GROOMING',
                'activo' => true
            ],
            [
                'nombre' => 'Corte y peinado',
                'slug' => 'corte-y-peinado',
                'descripcion' => 'Estilismo personalizado, corte con tijera o máquina según raza, baño profundo, secado y perfume.',
                'duracion_base_minutos' => 90,
                'precio_base' => 90.00,
                'categoria' => 'GROOMING',
                'activo' => true
            ],
            [
                'nombre' => 'Servicio completo',
                'slug' => 'servicio-completo',
                'descripcion' => 'Estilismo completo, baño premium, drenado de glándulas, limpieza de oídos, corte de uñas, hidratación de almohadillas y perfume.',
                'duracion_base_minutos' => 120,
                'precio_base' => 120.00,
                'categoria' => 'GROOMING',
                'activo' => true
            ]
        ];

        foreach ($services as $serv) {
            Service::updateOrCreate(
                ['slug' => $serv['slug']],
                [
                    'nombre' => $serv['nombre'],
                    'descripcion' => $serv['descripcion'],
                    'duracion_base_minutos' => $serv['duracion_base_minutos'],
                    'precio_base' => $serv['precio_base'],
                    'categoria' => $serv['categoria'],
                    'activo' => $serv['activo']
                ]
            );
        }

        // 6. SEED DEFAULT PRODUCTS (INVENTARIO)
        $productos = [
            [
                'nombre' => 'Shampoo Canino Avena Premium',
                'codigo' => 'SHAMP-AV-01',
                'descripcion' => 'Shampoo hidratante de avena para todo tipo de pelajes.',
                'categoria' => 'INSUMO',
                'unidad_medida' => 'UNIDAD',
                'precio_compra' => 15.50,
                'precio_venta' => 25.00,
                'stock_actual' => 20,
                'stock_minimo' => 5,
                'activo' => true
            ],
            [
                'nombre' => 'Acondicionador Brillo Sedoso',
                'codigo' => 'ACOND-BS-02',
                'descripcion' => 'Acondicionador suavizante para desenredar nudos difíciles.',
                'categoria' => 'INSUMO',
                'unidad_medida' => 'UNIDAD',
                'precio_compra' => 12.00,
                'precio_venta' => 20.00,
                'stock_actual' => 15,
                'stock_minimo' => 4,
                'activo' => true
            ],
            [
                'nombre' => 'Perfume Brisa Tropical',
                'codigo' => 'PERF-BT-03',
                'descripcion' => 'Fragancia de larga duración, segura y sin alcohol.',
                'categoria' => 'INSUMO',
                'unidad_medida' => 'UNIDAD',
                'precio_compra' => 18.00,
                'precio_venta' => 30.00,
                'stock_actual' => 10,
                'stock_minimo' => 3,
                'activo' => true
            ],
            [
                'nombre' => 'Shampoo Hipoalergénico Alivio',
                'codigo' => 'SHAMP-HA-04',
                'descripcion' => 'Fórmula ultra suave para mascotas con piel extra sensible.',
                'categoria' => 'INSUMO',
                'unidad_medida' => 'UNIDAD',
                'precio_compra' => 22.00,
                'precio_venta' => 35.00,
                'stock_actual' => 8,
                'stock_minimo' => 3,
                'activo' => true
            ],
            [
                'nombre' => 'Shampoo Antiparasitario Fipro',
                'codigo' => 'SHAMP-AP-05',
                'descripcion' => 'Elimina pulgas y garrapatas de forma efectiva.',
                'categoria' => 'INSUMO',
                'unidad_medida' => 'UNIDAD',
                'precio_compra' => 25.00,
                'precio_venta' => 40.00,
                'stock_actual' => 12,
                'stock_minimo' => 4,
                'activo' => true
            ]
        ];

        foreach ($productos as $prod) {
            \App\Models\Producto::updateOrCreate(
                ['codigo' => $prod['codigo']],
                [
                    'nombre' => $prod['nombre'],
                    'descripcion' => $prod['descripcion'],
                    'categoria' => $prod['categoria'],
                    'unidad_medida' => $prod['unidad_medida'],
                    'precio_compra' => $prod['precio_compra'],
                    'precio_venta' => $prod['precio_venta'],
                    'stock_actual' => $prod['stock_actual'],
                    'stock_minimo' => $prod['stock_minimo'],
                    'activo' => $prod['activo']
                ]
            );
        }
    }
}

