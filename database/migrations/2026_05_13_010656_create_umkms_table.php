// database/migrations/xxxx_create_umkms_table.php

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
            
            // Geotagging
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('sharelock_url')->nullable();
            
            // Ukuran Gerobak - Tampak Depan
            $table->string('depan_panel_atas')->nullable(); // format: "100x50"
            $table->decimal('depan_panel_atas_m2', 8, 2)->nullable();
            $table->string('depan_panel_tengah')->nullable();
            $table->decimal('depan_panel_tengah_m2', 8, 2)->nullable();
            $table->string('depan_panel_bawah')->nullable();
            $table->decimal('depan_panel_bawah_m2', 8, 2)->nullable();
            
            // Ukuran Gerobak - Sisi Kanan
            $table->string('kanan_panel_atas')->nullable();
            $table->decimal('kanan_panel_atas_m2', 8, 2)->nullable();
            $table->string('kanan_panel_tengah')->nullable();
            $table->decimal('kanan_panel_tengah_m2', 8, 2)->nullable();
            $table->string('kanan_panel_bawah')->nullable();
            $table->decimal('kanan_panel_bawah_m2', 8, 2)->nullable();
            
            // Ukuran Gerobak - Sisi Kiri
            $table->string('kiri_panel_atas')->nullable();
            $table->decimal('kiri_panel_atas_m2', 8, 2)->nullable();
            $table->string('kiri_panel_tengah')->nullable();
            $table->decimal('kiri_panel_tengah_m2', 8, 2)->nullable();
            $table->string('kiri_panel_bawah')->nullable();
            $table->decimal('kiri_panel_bawah_m2', 8, 2)->nullable();
            
            // Total Area
            $table->decimal('total_area_branding', 8, 2)->nullable();
            $table->boolean('memenuhi_kriteria')->default(false); // >= 1.5 m2
            
            // Status & Approval
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('alasan_reject')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            // foto
                $table->string('foto_depan')->nullable();
    $table->string('foto_kanan')->nullable();
    $table->string('foto_kiri')->nullable();
    $table->string('foto_plang_alfamart')->nullable();

    // design final (hasil revisi jika ada)
      $table->string('design_final')->nullable();
            $table->string('design_gerobak_depan')->nullable();
            $table->string('design_gerobak_kiri')->nullable();
            $table->string('design_gerobak_kanan')->nullable();
            
            // Relasi
            $table->foreignId('kota_id')->constrained('kotas')->onDelete('cascade');
            $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade'); // PIC Lapangan
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umkms');
    }
};