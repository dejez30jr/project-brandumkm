<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umkms', function (Blueprint $table) {
            // TASK-16: Jam operasional
            $table->time('jam_buka')->nullable()->after('alamat_usaha');
            $table->time('jam_tutup')->nullable()->after('jam_buka');

            // TASK-17: Teks branding yang diminta pemilik
            $table->text('request_text')->nullable()->after('jam_tutup');

            // TASK-18: Catatan tambahan PIC lapangan
            $table->text('catatan')->nullable()->after('request_text');

            // TASK-19: Foto ke-5 (tampak jauh)
            $table->string('foto_tampak_jauh')->nullable()->after('foto_plang_alfamart');
        });
    }

    public function down(): void
    {
        Schema::table('umkms', function (Blueprint $table) {
            $table->dropColumn(['jam_buka', 'jam_tutup', 'request_text', 'catatan', 'foto_tampak_jauh']);
        });
    }
};
