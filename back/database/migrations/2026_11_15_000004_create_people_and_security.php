<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $t) {
            $t->id();
            $t->string('type', 20);
            $t->string('identification', 30);
            $t->string('name', 150);
            $t->string('address', 255)->nullable();
            $t->string('position', 100)->nullable();
            $t->timestamps();
            $t->unique(['type', 'identification']);
        });
        DB::statement("ALTER TABLE people ADD CONSTRAINT people_type CHECK (type IN ('READER', 'STAFF'))");

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->foreignId('person_id')->unique()->constrained('people');
            $t->string('login', 50)->unique();
            $t->string('password');
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('name', 50)->unique();
        });

        Schema::create('role_permission', function (Blueprint $t) {
            $t->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $t->string('permission', 50);
            $t->primary(['role_id', 'permission']);
        });

        Schema::create('user_role', function (Blueprint $t) {
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $t->primary(['user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('people');
    }
};
