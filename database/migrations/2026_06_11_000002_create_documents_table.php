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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->comment('bytes');
            $table->string('mime_type');
            // Default 'internal' = fail-closed: dokumen tidak pernah bocor ke publik karena lupa diset
            $table->enum('visibility', ['public', 'mahasiswa', 'internal'])->default('internal')->index();
            $table->string('academic_year', 9)->nullable()->index()->comment('mis. 2025/2026');
            $table->enum('semester', ['ganjil', 'genap', '-'])->nullable();
            $table->string('course_name')->nullable()->comment('untuk RPS, modul, kontrak kuliah');
            $table->string('lecturer_name')->nullable()->comment('untuk arsip dosen');
            $table->date('expires_at')->nullable()->comment('untuk MoU/MoA');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('download_count')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['published', 'draft', 'archived'])->default('draft')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
