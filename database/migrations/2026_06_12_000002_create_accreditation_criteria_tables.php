<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mapping dokumen → kriteria akreditasi (PLAN §4.1 fase 3).
     */
    public function up(): void
    {
        Schema::create('accreditation_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->comment('mis. K1, K2, …');
            $table->string('name');
            $table->string('instrument')->default('LAMEMBA');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['instrument', 'code']);
        });

        Schema::create('document_criteria', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('criteria_id')->constrained('accreditation_criteria')->cascadeOnDelete();

            $table->primary(['document_id', 'criteria_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_criteria');
        Schema::dropIfExists('accreditation_criteria');
    }
};
