<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('partner_name');
            $table->enum('type', ['MoU', 'MoA', 'IA'])->index();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable()->index();
            $table->text('description')->nullable();
            // File perjanjian lengkap (visibility internal) — opsional
            $table->foreignId('document_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};
