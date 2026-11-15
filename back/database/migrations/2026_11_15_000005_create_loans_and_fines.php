<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $t) {
            $t->id();
            $t->foreignId('copy_id')->constrained('copies');
            $t->foreignId('reader_id')->constrained('people');
            $t->date('loaned_on');
            $t->date('due_on');
            $t->date('returned_on')->nullable();
            $t->integer('renewals')->default(0);
            $t->foreignId('registered_by')->nullable()->constrained('users');
            $t->timestamps();
            $t->index('reader_id');
        });
        // R-04, RNF-04: a copy can have only one active loan, even with concurrent requests
        DB::statement('CREATE UNIQUE INDEX loan_active_unique ON loans (copy_id) WHERE returned_on IS NULL');

        Schema::create('fines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('loan_id')->unique()->constrained('loans');
            $t->integer('value');
            $t->integer('days_late');
            $t->integer('rate_applied');
            $t->string('status', 20)->default('PENDING');
            $t->date('paid_on')->nullable();
            $t->foreignId('payment_registered_by')->nullable()->constrained('users');
            $t->timestamps();
            $t->index('status');
        });
        DB::statement("ALTER TABLE fines ADD CONSTRAINT fines_status CHECK (status IN ('PENDING', 'PAID'))");
        DB::statement('ALTER TABLE fines ADD CONSTRAINT fines_positive CHECK (value > 0 AND days_late > 0 AND rate_applied > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
        Schema::dropIfExists('loans');
    }
};
