<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_operasionals', function (Blueprint $table) {
            $table->id();

            // Satu laporan harian hanya punya satu baris operasional
            $table->foreignId('laporan_harian_id')->unique()
                ->constrained('laporan_harians')->cascadeOnDelete();

            // Semua isian boleh kosong di database (simpan per bagian);
            // kewajiban isi diperiksa saat tombol Selesai.
            $table->unsignedInteger('jumlah_toko')->nullable();       // JML TOKO

            $table->bigInteger('npb_all')->nullable();                // NPB ALL harian
            $table->bigInteger('npb_dry')->nullable();                // NPB DRY harian
            $table->bigInteger('bpb_all')->nullable();                // BPB ALL harian

            $table->unsignedInteger('toko_picking')->nullable();      // JML TOKO PICKING
            $table->unsignedInteger('toko_kirim')->nullable();        // JML TOKO KIRIM

            $table->unsignedInteger('paket_terbentuk')->nullable();   // PAKET KIRIMAN (DRY) terbentuk
            $table->unsignedInteger('paket_kirim')->nullable();       // PAKET KIRIMAN (DRY) kirim

            $table->unsignedInteger('supplier_daftar')->nullable();   // SUPPLIER daftar
            $table->unsignedInteger('supplier_pending')->nullable();  // SUPPLIER pending

            // JAM SELESAI OPERASIONAL (tanggal + jam, karena bisa melewati tengah malam)
            $table->dateTime('waktu_selesai_rcv')->nullable();        // Rcv
            $table->dateTime('waktu_selesai_picking')->nullable();    // Picking
            $table->dateTime('waktu_selesai_loading')->nullable();    // Loading
            $table->dateTime('waktu_selesai_del')->nullable();        // Del

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_operasionals');
    }
};
