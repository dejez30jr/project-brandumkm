<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umkms', function (Blueprint $table) {
            $table->index('status');
            $table->index('kota_id');
            $table->index('submitted_by');
        });

        Schema::table('umkm_designs', function (Blueprint $table) {
            $table->index('designer_id');
            $table->index('status');
        });

        Schema::table('notifikasis', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('umkms', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['kota_id']);
            $table->dropIndex(['submitted_by']);
        });

        Schema::table('umkm_designs', function (Blueprint $table) {
            $table->dropIndex(['designer_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('notifikasis', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
    }
};
