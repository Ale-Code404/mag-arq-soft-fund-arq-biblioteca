<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $t) {
            $t->id();
            $t->string('tipo', 20);
            $t->string('identificacion', 30);
            $t->string('nombre', 150);
            $t->string('direccion', 255)->nullable();
            $t->string('cargo', 100)->nullable();
            $t->timestamps();
            $t->unique(['tipo', 'identificacion']);
        });
        DB::statement("ALTER TABLE personas ADD CONSTRAINT personas_tipo CHECK (tipo IN ('LECTOR', 'FUNCIONARIO'))");

        Schema::create('usuarios', function (Blueprint $t) {
            $t->id();
            $t->foreignId('persona_id')->unique()->constrained('personas');
            $t->string('login', 50)->unique();
            $t->string('password');
            $t->boolean('activo')->default(true);
            $t->timestamps();
        });

        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('nombre', 50)->unique();
        });

        Schema::create('rol_permiso', function (Blueprint $t) {
            $t->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $t->string('permiso', 50);
            $t->primary(['rol_id', 'permiso']);
        });

        Schema::create('usuario_rol', function (Blueprint $t) {
            $t->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $t->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $t->primary(['usuario_id', 'rol_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_rol');
        Schema::dropIfExists('rol_permiso');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('personas');
    }
};
