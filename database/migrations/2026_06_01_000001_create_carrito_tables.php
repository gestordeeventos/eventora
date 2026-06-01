<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carritos', function (Blueprint $table) {
            $table->id('id_carrito');
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario')->cascadeOnDelete();
            $table->string('estado', 20)->default('activo');
            $table->timestamps();

            $table->index(['id_usuario', 'estado']);
        });

        Schema::create('carrito_items', function (Blueprint $table) {
            $table->id('id_item');
            $table->foreignId('id_carrito')->constrained('carritos', 'id_carrito')->cascadeOnDelete();
            $table->foreignId('id_evento')->constrained('eventos', 'id_evento')->restrictOnDelete();
            $table->foreignId('id_boleto')->constrained('boletos', 'id_boleto')->restrictOnDelete();
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->timestamps();

            $table->unique(['id_carrito', 'id_boleto']);
        });

        Schema::table('reservas', function (Blueprint $table) {
            if (! Schema::hasColumn('reservas', 'id_carrito')) {
                $table->foreignId('id_carrito')->nullable()->after('id_usuario')
                    ->constrained('carritos', 'id_carrito')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            if (Schema::hasColumn('reservas', 'id_carrito')) {
                $table->dropConstrainedForeignId('id_carrito');
            }
        });

        Schema::dropIfExists('carrito_items');
        Schema::dropIfExists('carritos');
    }
};
