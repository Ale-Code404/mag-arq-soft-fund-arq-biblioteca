<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shelves', function (Blueprint $t) {
            $t->id();
            $t->foreignId('library_id')->constrained('libraries');
            $t->string('code', 30)->unique();
            $t->timestamps();
        });

        Schema::create('levels', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shelf_id')->constrained('shelves')->cascadeOnDelete();
            $t->integer('number');
            $t->decimal('length_cm', 7, 1);
            $t->decimal('height_cm', 7, 1);
            $t->unique(['shelf_id', 'number']);
        });

        DB::statement('ALTER TABLE levels ADD CONSTRAINT levels_positive CHECK (number > 0 AND length_cm > 0 AND height_cm > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
        Schema::dropIfExists('shelves');
    }
};
