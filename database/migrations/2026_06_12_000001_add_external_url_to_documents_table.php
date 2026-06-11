<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dokumen bisa bersumber dari file unggahan ATAU tautan eksternal
     * (mis. Google Drive) untuk menghemat penyimpanan server.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('external_url', 2048)->nullable()->after('mime_type')
                ->comment('tautan penyimpanan eksternal, mis. Google Drive');
            $table->string('file_path')->nullable()->change();
            $table->string('file_name')->nullable()->change();
            $table->unsignedBigInteger('file_size')->nullable()->comment('bytes')->change();
            $table->string('mime_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('external_url');
            $table->string('file_path')->nullable(false)->change();
            $table->string('file_name')->nullable(false)->change();
            $table->unsignedBigInteger('file_size')->nullable(false)->comment('bytes')->change();
            $table->string('mime_type')->nullable(false)->change();
        });
    }
};
