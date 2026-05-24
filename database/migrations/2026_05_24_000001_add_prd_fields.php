<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add tanggal_pasang, nama_team_pasang to umkms
        Schema::table('umkms', function (Blueprint $table) {
            $table->date('tanggal_pasang')->nullable()->after('foto_wide');
            $table->string('nama_team_pasang')->nullable()->after('tanggal_pasang');
        });

        // Add nama_desainer to umkm_designs
        Schema::table('umkm_designs', function (Blueprint $table) {
            $table->string('nama_desainer')->nullable()->after('designer_id');
        });

        // Fix status enum: add waiting_installation, installation_completed, terbranding_final
        // MySQL: modify enum column
        DB::statement("ALTER TABLE umkms MODIFY COLUMN status ENUM(
            'pending',
            'approved',
            'rejected',
            'menunggu_didesain',
            'designing',
            'design_review',
            'design_approved',
            'waiting_installation',
            'revision_needed',
            'revision',
            'installation_completed',
            'branded',
            'terbranding_final'
        ) NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('umkms', function (Blueprint $table) {
            $table->dropColumn(['tanggal_pasang', 'nama_team_pasang']);
        });
        Schema::table('umkm_designs', function (Blueprint $table) {
            $table->dropColumn('nama_desainer');
        });
    }
};
