<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_karyawans', function (Blueprint $table) {
            $table->id();

            // Satu laporan harian hanya punya satu baris karyawan
            $table->foreignId('laporan_harian_id')->unique()
                ->constrained('laporan_harians')->cascadeOnDelete();

            // Semua isian boleh kosong di database; kewajiban isi dijaga di validasi form.
            // Jumlah orang dan jam lembur tidak mungkin negatif, jadi tanpa tanda (unsigned).

            // KEHADIRAN (jumlah karyawan)
            $table->unsignedInteger('hadir')->nullable();
            $table->unsignedInteger('off')->nullable();
            $table->unsignedInteger('cuti')->nullable();
            $table->unsignedInteger('alpa')->nullable();
            $table->unsignedInteger('sakit')->nullable();

            // JAM LEMBUR per bagian
            $table->unsignedInteger('lembur_rcv_ret')->nullable();   // Receiving / Retur
            $table->unsignedInteger('lembur_whs')->nullable();       // Warehouse
            $table->unsignedInteger('lembur_iss')->nullable();       // Issuing
            $table->unsignedInteger('lembur_perish')->nullable();    // Perishable
            $table->unsignedInteger('lembur_adm')->nullable();       // Admin

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_karyawans');
    }
};
