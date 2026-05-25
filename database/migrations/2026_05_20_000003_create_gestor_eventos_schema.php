<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('reserva_boletos');
        Schema::dropIfExists('reservas');
        Schema::dropIfExists('boletos');
        Schema::dropIfExists('evento_paquete');
        Schema::dropIfExists('eventos');
        Schema::dropIfExists('paquetes');
        Schema::dropIfExists('tipos_evento');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('users');

        Schema::create('tipos_evento', function (Blueprint $table) {
            $table->id('id_tipo_evento');
            $table->string('nombre', 80)->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nombre', 80);
            $table->string('apellido', 80);
            $table->string('email', 120)->unique();
            $table->string('password_hash');
            $table->string('rol', 20)->default('cliente');
            $table->string('telefono', 20)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('email_verificado_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('eventos', function (Blueprint $table) {
            $table->id('id_evento');
            $table->foreignId('id_organizador')->constrained('usuarios', 'id_usuario')->restrictOnDelete();
            $table->foreignId('id_tipo_evento')->constrained('tipos_evento', 'id_tipo_evento')->restrictOnDelete();
            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_fin')->nullable();
            $table->string('lugar', 200);
            $table->string('ciudad', 100)->nullable();
            $table->unsignedInteger('capacidad_max');
            $table->string('estado', 20)->default('borrador');
            $table->text('imagen_url')->nullable();
            $table->timestamps();

            $table->index('estado');
            $table->index('fecha_inicio');
        });

        Schema::create('paquetes', function (Blueprint $table) {
            $table->id('id_paquete');
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->text('incluye')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('evento_paquete', function (Blueprint $table) {
            $table->foreignId('id_evento')->constrained('eventos', 'id_evento')->cascadeOnDelete();
            $table->foreignId('id_paquete')->constrained('paquetes', 'id_paquete')->restrictOnDelete();
            $table->primary(['id_evento', 'id_paquete']);
        });

        Schema::create('boletos', function (Blueprint $table) {
            $table->id('id_boleto');
            $table->foreignId('id_evento')->constrained('eventos', 'id_evento')->cascadeOnDelete();
            $table->string('nombre_tipo', 50);
            $table->decimal('precio', 10, 2);
            $table->unsignedInteger('cantidad_total');
            $table->unsignedInteger('cantidad_vendida')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['id_evento', 'nombre_tipo']);
        });

        Schema::create('reservas', function (Blueprint $table) {
            $table->id('id_reserva');
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario')->restrictOnDelete();
            $table->foreignId('id_evento')->constrained('eventos', 'id_evento')->restrictOnDelete();
            $table->foreignId('id_paquete')->nullable()->constrained('paquetes', 'id_paquete')->nullOnDelete();
            $table->string('estado', 20)->default('pendiente');
            $table->decimal('total', 10, 2)->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        Schema::create('reserva_boletos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_reserva')->constrained('reservas', 'id_reserva')->cascadeOnDelete();
            $table->foreignId('id_boleto')->constrained('boletos', 'id_boleto')->restrictOnDelete();
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);

            $table->unique(['id_reserva', 'id_boleto']);
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_boletos');
        Schema::dropIfExists('reservas');
        Schema::dropIfExists('boletos');
        Schema::dropIfExists('evento_paquete');
        Schema::dropIfExists('eventos');
        Schema::dropIfExists('paquetes');
        Schema::dropIfExists('tipos_evento');
        Schema::dropIfExists('usuarios');
    }
};
