<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_utilitas', function (Blueprint $table) {
            $table->id();

            // Satu laporan harian hanya punya satu baris utilitas
            $table->foreignId('laporan_harian_id')->unique()
                ->constrained('laporan_harians')->cascadeOnDelete();

            // Semua isian boleh kosong di database; kewajiban isi dijaga di validasi form.
            // Jumlah unit tidak mungkin negatif, jadi tanpa tanda (unsigned).

            // Jumlah unit per jenis alat: aktif dan rusak
            $table->unsignedInteger('forklift_aktif')->nullable();
            $table->unsignedInteger('forklift_rusak')->nullable();
            $table->unsignedInteger('stacker_aktif')->nullable();
            $table->unsignedInteger('stacker_rusak')->nullable();
            $table->unsignedInteger('php_aktif')->nullable();
            $table->unsignedInteger('php_rusak')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_utilitas');
    }
};
