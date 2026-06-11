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
        Schema::create('lecturers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nidn', 30)->nullable()->unique();
            $table->string('position')->nullable()->comment('jabatan fungsional, mis. Lektor Kepala');
            $table->string('expertise')->nullable()->comment('bidang keahlian');
            $table->string('email')->nullable();
            $table->string('photo_path')->nullable()->comment('di disk public, folder dosen/');
            $table->string('publication_url')->nullable()->comment('tautan SINTA/Scholar');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturers');
    }
};
