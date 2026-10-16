<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_performances', function (Blueprint $table) {
            $table->id();

            // Satu laporan harian hanya punya satu baris performance
            $table->foreignId('laporan_harian_id')->unique()
                ->constrained('laporan_harians')->cascadeOnDelete();

            // Semua isian boleh kosong di database; kewajiban isi dijaga di validasi form.
            // Semua nilai angka boleh negatif (bertanda), kecuali SL (persen 0-100) dan isian waktu.

            // PRODUKTIVITAS (akumulasi, diketik apa adanya dari sumber)
            $table->bigInteger('produktivitas_akumulasi')->nullable();

            // PROFORMA BEBAN (harian)
            $table->bigInteger('proforma_beban_tk_all')->nullable();     // TK (ALL)
            $table->bigInteger('proforma_beban_dc_perish')->nullable();  // DC (PERISH)
            $table->bigInteger('proforma_dry_whs')->nullable();          // DC (DRY): WHS
            $table->bigInteger('proforma_dry_issuing')->nullable();      //           Issuing
            $table->bigInteger('proforma_dry_expedisi')->nullable();     //           Expedisi
            $table->bigInteger('proforma_dry_admin')->nullable();        //           Admin
            $table->bigInteger('proforma_dry_lain2')->nullable();        //           Lain-lain
            // TTL harian DRY tidak disimpan: dihitung dari lima kolom di atas

            // SERVICE LEVEL (persen)
            $table->decimal('sl_pick_item', 5, 2)->nullable();           // SL PICK (item)
            $table->decimal('sl_pick_qty', 5, 2)->nullable();            // SL PICK (qty)
            $table->decimal('sl_npb_pb_item', 5, 2)->nullable();         // SL NPB vs PB (item)
            $table->decimal('sl_npb_pb_qty', 5, 2)->nullable();          // SL NPB vs PB (qty)

            // REVISI SCANNER (boleh negatif)
            $table->bigInteger('revisi_scanner_pick')->nullable();
            $table->bigInteger('revisi_scanner_scan')->nullable();

            // STOCK OPNAME IC LOKASI 01 (waktu bisa lewat tengah malam; Rp boleh negatif)
            $table->dateTime('so_jam_upload')->nullable();
            $table->dateTime('so_jam_preadjust1')->nullable();
            $table->bigInteger('so_rp_preadjust1')->nullable();
            $table->dateTime('so_jam_adjust')->nullable();
            $table->bigInteger('so_rp_adjust')->nullable();

            // NKL, barang rusak pabrik, time picking
            $table->bigInteger('nkl_dry_ba')->nullable();
            $table->bigInteger('barang_rusak_pabrik_beban_toko')->nullable();
            $table->time('time_picking')->nullable();                    // durasi, contoh 00:02:30

            // BARANG TERTINGGAL
            $table->integer('tertinggal_container')->nullable();
            $table->integer('tertinggal_bronjong')->nullable();
            $table->bigInteger('tertinggal_intransit_b')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_performances');
    }
};
