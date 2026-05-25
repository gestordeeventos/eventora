<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (! Schema::hasColumn('usuarios', 'foto_perfil_url')) {
                $table->text('foto_perfil_url')->nullable()->after('telefono');
            }
            if (! Schema::hasColumn('usuarios', 'foto_perfil_updated_at')) {
                $table->timestamp('foto_perfil_updated_at')->nullable()->after('foto_perfil_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'foto_perfil_updated_at')) {
                $table->dropColumn('foto_perfil_updated_at');
            }
            if (Schema::hasColumn('usuarios', 'foto_perfil_url')) {
                $table->dropColumn('foto_perfil_url');
            }
        });
    }
};
