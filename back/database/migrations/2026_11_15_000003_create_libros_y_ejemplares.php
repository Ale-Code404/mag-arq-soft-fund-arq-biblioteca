<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('libros', function (Blueprint $t) {
            $t->id();
            $t->string('isbn', 20)->unique();
            $t->string('titulo', 255);
            $t->string('autor', 255);
            $t->string('editorial', 150);
            $t->smallInteger('anio');
            $t->string('tematica', 20);
            $t->decimal('alto_cm', 5, 1);
            $t->decimal('ancho_cm', 5, 1);
            $t->decimal('grosor_cm', 5, 2);
            $t->timestamps();
        });
        DB::statement("ALTER TABLE libros ADD CONSTRAINT libros_tematica CHECK (tematica IN ('INFANTIL', 'NARRATIVA', 'ENSAYO', 'POESIA'))");

        Schema::create('ejemplares', function (Blueprint $t) {
            $t->id();
            $t->foreignId('libro_id')->constrained('libros');
            $t->foreignId('nivel_id')->nullable()->constrained('niveles');
            $t->string('codigo', 30)->unique();
            $t->string('estado', 20)->default('DISPONIBLE');
            $t->timestamps();
            $t->index('nivel_id');
        });
        DB::statement("ALTER TABLE ejemplares ADD CONSTRAINT ejemplares_estado CHECK (estado IN ('DISPONIBLE', 'PRESTADO'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('ejemplares');
        Schema::dropIfExists('libros');
    }
};
