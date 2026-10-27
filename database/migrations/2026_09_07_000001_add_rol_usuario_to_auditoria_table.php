<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditoria', function (Blueprint $table) {
            $table->string('rol_usuario', 50)
                ->nullable()
                ->after('usuario_id')
                ->index('idx_auditoria_rol_usuario');
        });
    }

    public function down(): void
    {
        Schema::table('auditoria', function (Blueprint $table) {
            $table->dropIndex('idx_auditoria_rol_usuario');
            $table->dropColumn('rol_usuario');
        });
    }
};
