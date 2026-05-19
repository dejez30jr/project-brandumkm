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
        Schema::table('umkms', function (Blueprint $table) {

            $table->string('stiker_tampak_depan')->nullable();

            $table->string('stiker_tampak_kanan')->nullable();

            $table->string('stiker_tampak_kiri')->nullable();

            $table->string('foto_wide')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('umkms', function (Blueprint $table) {

            $table->dropColumn([
                'stiker_tampak_depan',
                'stiker_tampak_kanan',
                'stiker_tampak_kiri',
                'foto_wide',
            ]);

        });
    }
};