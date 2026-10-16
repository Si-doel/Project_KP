<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_harians', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->date('tanggal_laporan');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Lama pengisian: start = pertama kali disimpan, end = bagian ke-6 selesai
            $table->timestamp('start_proses')->nullable();
            $table->timestamp('end_proses')->nullable();

            // Penanda selesai per bagian (kosong = belum selesai)
            $table->timestamp('opr_selesai_at')->nullable(); // Operasional
            $table->timestamp('prf_selesai_at')->nullable(); // Performance
            $table->timestamp('inv_selesai_at')->nullable(); // Inventory
            $table->timestamp('kry_selesai_at')->nullable(); // Karyawan
            $table->timestamp('utl_selesai_at')->nullable(); // Utilitas
            $table->timestamp('arm_selesai_at')->nullable(); // Armada

            $table->timestamps();

            // Satu cabang hanya boleh punya satu laporan per tanggal
            $table->unique(['cabang_id', 'tanggal_laporan']);
            // Mempercepat daftar admin per tanggal
            $table->index('tanggal_laporan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_harians');
    }
};
