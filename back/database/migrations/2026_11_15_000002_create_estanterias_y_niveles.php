<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estanterias', function (Blueprint $t) {
            $t->id();
            $t->foreignId('biblioteca_id')->constrained('bibliotecas');
            $t->string('codigo', 30)->unique();
            $t->timestamps();
        });

        Schema::create('niveles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('estanteria_id')->constrained('estanterias')->cascadeOnDelete();
            $t->integer('numero');
            $t->decimal('largo_cm', 7, 1);
            $t->decimal('alto_cm', 7, 1);
            $t->unique(['estanteria_id', 'numero']);
        });

        DB::statement('ALTER TABLE niveles ADD CONSTRAINT niveles_positivos CHECK (numero > 0 AND largo_cm > 0 AND alto_cm > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('niveles');
        Schema::dropIfExists('estanterias');
    }
};
