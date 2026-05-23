<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umkms', function (Blueprint $table) {
            $table->id();

            // Data Pemilik
            $table->string('nama_pemilik');
            $table->string('nama_usaha');
            $table->text('alamat_usaha');
            $table->string('no_wa');
            $table->string('radius')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('nama_bank')->nullable();
            $table->string('atas_nama_rekening')->nullable();

            // Operasional
            $table->time('jam_buka')->nullable();
            $table->time('jam_tutup')->nullable();
            $table->text('request_text')->nullable();
            $table->text('catatan')->nullable();

            // Geotagging
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('sharelock_url')->nullable();

            // Ukuran Panel — Width & Height (cm)
            $table->decimal('depan_atas_w', 8, 2)->nullable();
            $table->decimal('depan_atas_h', 8, 2)->nullable();
            $table->decimal('depan_tengah_w', 8, 2)->nullable();
            $table->decimal('depan_tengah_h', 8, 2)->nullable();
            $table->decimal('depan_bawah_w', 8, 2)->nullable();
            $table->decimal('depan_bawah_h', 8, 2)->nullable();
            $table->decimal('kanan_atas_w', 8, 2)->nullable();
            $table->decimal('kanan_atas_h', 8, 2)->nullable();
            $table->decimal('kanan_tengah_w', 8, 2)->nullable();
            $table->decimal('kanan_tengah_h', 8, 2)->nullable();
            $table->decimal('kanan_bawah_w', 8, 2)->nullable();
            $table->decimal('kanan_bawah_h', 8, 2)->nullable();
            $table->decimal('kiri_atas_w', 8, 2)->nullable();
            $table->decimal('kiri_atas_h', 8, 2)->nullable();
            $table->decimal('kiri_tengah_w', 8, 2)->nullable();
            $table->decimal('kiri_tengah_h', 8, 2)->nullable();
            $table->decimal('kiri_bawah_w', 8, 2)->nullable();
            $table->decimal('kiri_bawah_h', 8, 2)->nullable();

            // Panel M2 (auto-calculated)
            $table->decimal('depan_panel_atas_m2', 8, 2)->nullable();
            $table->decimal('depan_panel_tengah_m2', 8, 2)->nullable();
            $table->decimal('depan_panel_bawah_m2', 8, 2)->nullable();
            $table->decimal('kanan_panel_atas_m2', 8, 2)->nullable();
            $table->decimal('kanan_panel_tengah_m2', 8, 2)->nullable();
            $table->decimal('kanan_panel_bawah_m2', 8, 2)->nullable();
            $table->decimal('kiri_panel_atas_m2', 8, 2)->nullable();
            $table->decimal('kiri_panel_tengah_m2', 8, 2)->nullable();
            $table->decimal('kiri_panel_bawah_m2', 8, 2)->nullable();

            // Total Area
            $table->decimal('total_area_branding', 8, 2)->nullable();
            $table->boolean('memenuhi_kriteria')->default(false);

            // Status & Approval (lifecycle lengkap)
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'designing',
                'design_review',
                'design_approved',
                'revision_needed',
                'branded',
            ])->default('pending')->index();
            $table->text('alasan_reject')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            // Foto (min 5)
            $table->string('foto_depan')->nullable();
            $table->string('foto_kanan')->nullable();
            $table->string('foto_kiri')->nullable();
            $table->string('foto_plang_alfamart')->nullable();
            $table->string('foto_tampak_jauh')->nullable();
            $table->string('video_validasi')->nullable();

            // Design final (hasil approve dari client)
            $table->string('design_final')->nullable();
            $table->string('design_gerobak_depan')->nullable();
            $table->string('design_gerobak_kiri')->nullable();
            $table->string('design_gerobak_kanan')->nullable();

            // Relasi
            $table->foreignId('kota_id')->constrained('kotas')->onDelete('cascade');
            $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade');

            // Foto stiker terpasang (diisi team pasang)
            $table->string('stiker_tampak_depan')->nullable();
            $table->string('stiker_tampak_kanan')->nullable();
            $table->string('stiker_tampak_kiri')->nullable();
            $table->string('foto_wide')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umkms');
    }
};
