<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $t) {
            $t->id();
            $t->string('isbn', 20)->unique();
            $t->string('title', 255);
            $t->string('author', 255);
            $t->string('publisher', 150);
            $t->smallInteger('year');
            $t->string('genre', 20);
            $t->decimal('height_cm', 5, 1);
            $t->decimal('width_cm', 5, 1);
            $t->decimal('thickness_cm', 5, 2);
            $t->timestamps();
        });
        DB::statement("ALTER TABLE books ADD CONSTRAINT books_genre CHECK (genre IN ('CHILDREN', 'NARRATIVE', 'ESSAY', 'POETRY'))");

        Schema::create('copies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('book_id')->constrained('books');
            $t->foreignId('level_id')->nullable()->constrained('levels');
            $t->string('code', 30)->unique();
            $t->string('status', 20)->default('AVAILABLE');
            $t->timestamps();
            $t->index('level_id');
        });
        DB::statement("ALTER TABLE copies ADD CONSTRAINT copies_status CHECK (status IN ('AVAILABLE', 'ON_LOAN'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('copies');
        Schema::dropIfExists('books');
    }
};
