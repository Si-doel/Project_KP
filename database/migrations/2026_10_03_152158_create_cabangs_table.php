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
        Schema::create('cabangs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_cabang', 4)->unique();
            $table->string('nama_cabang', 100);
            $table->string('alamat', 255);
            $table->string('kota_kabupaten', 50);
            $table->string('provinsi', 50);
            $table->enum('jenis_cabang', ['INDUK', 'DEPO', 'SEWA']);
            $table->foreignId('region_id')->constrained('regions');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('status', ['AKTIF', 'TIDAK_AKTIF', 'TUTUP'])->default('AKTIF');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cabangs');
    }
};
