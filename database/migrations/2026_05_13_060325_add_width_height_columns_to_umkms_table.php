<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umkms', function (Blueprint $table) {
            // Panel Depan
            $table->decimal('depan_atas_w', 8, 2)->nullable();
            $table->decimal('depan_atas_h', 8, 2)->nullable();
            $table->decimal('depan_tengah_w', 8, 2)->nullable();
            $table->decimal('depan_tengah_h', 8, 2)->nullable();
            $table->decimal('depan_bawah_w', 8, 2)->nullable();
            $table->decimal('depan_bawah_h', 8, 2)->nullable();
            
            // Panel Kanan
            $table->decimal('kanan_atas_w', 8, 2)->nullable();
            $table->decimal('kanan_atas_h', 8, 2)->nullable();
            $table->decimal('kanan_tengah_w', 8, 2)->nullable();
            $table->decimal('kanan_tengah_h', 8, 2)->nullable();
            $table->decimal('kanan_bawah_w', 8, 2)->nullable();
            $table->decimal('kanan_bawah_h', 8, 2)->nullable();
            
            // Panel Kiri
            $table->decimal('kiri_atas_w', 8, 2)->nullable();
            $table->decimal('kiri_atas_h', 8, 2)->nullable();
            $table->decimal('kiri_tengah_w', 8, 2)->nullable();
            $table->decimal('kiri_tengah_h', 8, 2)->nullable();
            $table->decimal('kiri_bawah_w', 8, 2)->nullable();
            $table->decimal('kiri_bawah_h', 8, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('umkms', function (Blueprint $table) {
            $table->dropColumn([
                'depan_atas_w', 'depan_atas_h',
                'depan_tengah_w', 'depan_tengah_h',
                'depan_bawah_w', 'depan_bawah_h',
                'kanan_atas_w', 'kanan_atas_h',
                'kanan_tengah_w', 'kanan_tengah_h',
                'kanan_bawah_w', 'kanan_bawah_h',
                'kiri_atas_w', 'kiri_atas_h',
                'kiri_tengah_w', 'kiri_tengah_h',
                'kiri_bawah_w', 'kiri_bawah_h',
            ]);
        });
    }
};