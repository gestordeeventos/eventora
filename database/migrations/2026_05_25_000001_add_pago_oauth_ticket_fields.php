<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            if (! Schema::hasColumn('reservas', 'codigo_ticket')) {
                $table->string('codigo_ticket', 24)->nullable()->unique()->after('total');
            }
            if (! Schema::hasColumn('reservas', 'pagado_at')) {
                $table->timestamp('pagado_at')->nullable()->after('codigo_ticket');
            }
            if (! Schema::hasColumn('reservas', 'metodo_pago')) {
                $table->string('metodo_pago', 40)->nullable()->after('pagado_at');
            }
            if (! Schema::hasColumn('reservas', 'ultimos4_tarjeta')) {
                $table->char('ultimos4_tarjeta', 4)->nullable()->after('metodo_pago');
            }
        });

        Schema::table('usuarios', function (Blueprint $table) {
            if (! Schema::hasColumn('usuarios', 'google_id')) {
                $table->string('google_id')->nullable()->unique()->after('email');
            }
            if (! Schema::hasColumn('usuarios', 'facebook_id')) {
                $table->string('facebook_id')->nullable()->unique()->after('google_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            foreach (['ultimos4_tarjeta', 'metodo_pago', 'pagado_at', 'codigo_ticket'] as $col) {
                if (Schema::hasColumn('reservas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::table('usuarios', function (Blueprint $table) {
            foreach (['facebook_id', 'google_id'] as $col) {
                if (Schema::hasColumn('usuarios', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
