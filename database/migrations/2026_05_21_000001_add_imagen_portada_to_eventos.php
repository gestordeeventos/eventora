<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            if (! Schema::hasColumn('eventos', 'imagen_updated_at')) {
                $table->timestamp('imagen_updated_at')->nullable()->after('imagen_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            if (Schema::hasColumn('eventos', 'imagen_updated_at')) {
                $table->dropColumn('imagen_updated_at');
            }
        });
    }
};
