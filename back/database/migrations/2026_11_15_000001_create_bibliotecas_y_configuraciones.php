<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bibliotecas', function (Blueprint $t) {
            $t->id();
            $t->string('nombre', 150);
            $t->timestamps();
        });

        Schema::create('configuraciones', function (Blueprint $t) {
            $t->id();
            $t->foreignId('biblioteca_id')->constrained('bibliotecas');
            $t->integer('dias_prestamo');
            $t->integer('dias_renovacion');
            $t->integer('max_renovaciones');
            $t->integer('valor_multa_diaria');
            $t->decimal('holgura_cm', 5, 2);
            $t->decimal('grosor_por_defecto_cm', 5, 2);
            $t->timestamp('vigente_desde');
            $t->unsignedBigInteger('creado_por')->nullable();
            $t->timestamps();
            $t->index('vigente_desde');
        });

        DB::statement('ALTER TABLE configuraciones ADD CONSTRAINT configuraciones_positivas CHECK (
            dias_prestamo > 0 AND dias_renovacion > 0 AND max_renovaciones > 0
            AND valor_multa_diaria > 0 AND holgura_cm > 0 AND grosor_por_defecto_cm > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
        Schema::dropIfExists('bibliotecas');
    }
};
