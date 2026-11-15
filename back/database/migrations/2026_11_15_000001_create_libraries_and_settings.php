<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('libraries', function (Blueprint $t) {
            $t->id();
            $t->string('name', 150);
            $t->timestamps();
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('library_id')->constrained('libraries');
            $t->integer('loan_days');
            $t->integer('renewal_days');
            $t->integer('max_renewals');
            $t->integer('daily_fine_value');
            $t->decimal('clearance_cm', 5, 2);
            $t->decimal('default_thickness_cm', 5, 2);
            $t->timestamp('effective_from');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->index('effective_from');
        });

        DB::statement('ALTER TABLE settings ADD CONSTRAINT settings_positive CHECK (
            loan_days > 0 AND renewal_days > 0 AND max_renewals > 0
            AND daily_fine_value > 0 AND clearance_cm > 0 AND default_thickness_cm > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('libraries');
    }
};
