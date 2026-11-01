<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_inventories', function (Blueprint $table) {
            $table->id();

            // Satu laporan harian hanya punya satu baris inventory
            $table->foreignId('laporan_harian_id')->unique()
                ->constrained('laporan_harians')->cascadeOnDelete();

            // Semua isian boleh kosong di database; kewajiban isi dijaga di validasi form.
            // Semua nilai boleh negatif (bertanda). Kolom HO (PKM, LPP, PO Out, dst)
            // tidak ada di sini; masuk tabel HO yang dibuat belakangan.

            // STOCK OUT
            $table->integer('stock_out_toko')->nullable();
            $table->integer('stock_out_dc')->nullable();

            // INTRANSIT NRB
            $table->bigInteger('intransit_nrb_proforma')->nullable();   // Proforma ALL
            $table->bigInteger('nrb_fisik_di_dc')->nullable();          // Fisik ALL: di DC
            $table->bigInteger('nrb_fisik_di_toko')->nullable();        // Fisik ALL: di toko
            $table->bigInteger('intransit_a')->nullable();              // Fisik ALL: Intransit A

            // NRB KE SUPPLIER (harian)
            $table->bigInteger('nrb_ke_supplier')->nullable();

            // NRB SEPIHAK (akumulasi, diketik apa adanya dari sumber)
            $table->bigInteger('nrb_sepihak_sudah_diambil')->nullable();
            $table->bigInteger('nrb_sepihak_belum_diambil')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_inventories');
    }
};
