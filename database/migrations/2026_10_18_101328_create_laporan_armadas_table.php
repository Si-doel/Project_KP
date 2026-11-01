<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_armadas', function (Blueprint $table) {
            $table->id();

            // Satu laporan harian hanya punya satu baris armada
            $table->foreignId('laporan_harian_id')->unique()
                ->constrained('laporan_harians')->cascadeOnDelete();

            // Semua isian boleh kosong di database; kewajiban isi dijaga di validasi form.
            // Jumlah unit tidak mungkin negatif, jadi tanpa tanda (unsigned).

            // Jumlah unit armada per jenis: aktif dan rusak
            $table->unsignedInteger('dry_aktif')->nullable();
            $table->unsignedInteger('dry_rusak')->nullable();
            $table->unsignedInteger('roti_aktif')->nullable();
            $table->unsignedInteger('roti_rusak')->nullable();
            $table->unsignedInteger('buah_aktif')->nullable();
            $table->unsignedInteger('buah_rusak')->nullable();
            $table->unsignedInteger('other_aktif')->nullable();
            $table->unsignedInteger('other_rusak')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_armadas');
    }
};
