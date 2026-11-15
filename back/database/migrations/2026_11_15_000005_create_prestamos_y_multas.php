<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ejemplar_id')->constrained('ejemplares');
            $t->foreignId('lector_id')->constrained('personas');
            $t->date('fecha_prestamo');
            $t->date('fecha_entrega_prevista');
            $t->date('fecha_devolucion')->nullable();
            $t->integer('renovaciones')->default(0);
            $t->foreignId('registrado_por')->nullable()->constrained('usuarios');
            $t->timestamps();
            $t->index('lector_id');
        });
        // R-04, RNF-04: un ejemplar solo puede tener un préstamo activo, aun con solicitudes concurrentes
        DB::statement('CREATE UNIQUE INDEX prestamo_activo_unico ON prestamos (ejemplar_id) WHERE fecha_devolucion IS NULL');

        Schema::create('multas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('prestamo_id')->unique()->constrained('prestamos');
            $t->integer('valor');
            $t->integer('dias_retraso');
            $t->integer('tarifa_aplicada');
            $t->string('estado', 20)->default('PENDIENTE');
            $t->date('fecha_pago')->nullable();
            $t->foreignId('pago_registrado_por')->nullable()->constrained('usuarios');
            $t->timestamps();
            $t->index('estado');
        });
        DB::statement("ALTER TABLE multas ADD CONSTRAINT multas_estado CHECK (estado IN ('PENDIENTE', 'PAGADA'))");
        DB::statement('ALTER TABLE multas ADD CONSTRAINT multas_positivas CHECK (valor > 0 AND dias_retraso > 0 AND tarifa_aplicada > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('multas');
        Schema::dropIfExists('prestamos');
    }
};
