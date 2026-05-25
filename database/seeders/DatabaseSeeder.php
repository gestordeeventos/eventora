<?php

namespace Database\Seeders;

use App\Models\Boleto;
use App\Models\Evento;
use App\Models\Paquete;
use App\Models\TipoEvento;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Corporativo', 'Eventos empresariales, conferencias y lanzamientos'],
            ['Social', 'Fiestas, reuniones y celebraciones privadas'],
            ['Conferencia', 'Congresos, seminarios y charlas profesionales'],
            ['Boda', 'Ceremonias y recepciones nupciales'],
        ] as [$nombre, $descripcion]) {
            TipoEvento::firstOrCreate(['nombre' => $nombre], ['descripcion' => $descripcion]);
        }

        foreach ([
            ['Básico', 'Coordinación esencial del evento', 1500, 'Coordinador, cronograma, checklist'],
            ['Profesional', 'Logística completa y proveedores', 4500, 'Coordinador, logística, catering básico, decoración'],
            ['Premium', 'Experiencia integral llave en mano', 9000, 'Todo Profesional + DJ, fotografía, transporte VIP'],
        ] as [$nombre, $descripcion, $precio, $incluye]) {
            Paquete::firstOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $descripcion, 'precio' => $precio, 'incluye' => $incluye]
            );
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@gestoreventos.com'],
            [
                'nombre' => 'Admin',
                'apellido' => 'Eventora',
                'password_hash' => Hash::make('Admin123!'),
                'rol' => 'admin',
                'activo' => true,
            ]
        );

        $tipo = TipoEvento::where('nombre', 'Corporativo')->first();
        if ($tipo && ! Evento::where('titulo', 'Gala Eventora 2026')->exists()) {
            $evento = Evento::create([
                'id_organizador' => $admin->id_usuario,
                'id_tipo_evento' => $tipo->id_tipo_evento,
                'titulo' => 'Gala Eventora 2026',
                'descripcion' => 'Evento de demostración con boletos disponibles.',
                'fecha_inicio' => now()->addMonth(),
                'lugar' => 'Centro de Convenciones',
                'ciudad' => 'Ciudad de México',
                'capacidad_max' => 500,
                'estado' => 'publicado',
            ]);
            foreach ([['General', 350, 200], ['VIP', 750, 50]] as [$tipo, $precio, $stock]) {
                Boleto::create([
                    'id_evento' => $evento->id_evento,
                    'nombre_tipo' => $tipo,
                    'precio' => $precio,
                    'cantidad_total' => $stock,
                ]);
            }
        }
    }
}
