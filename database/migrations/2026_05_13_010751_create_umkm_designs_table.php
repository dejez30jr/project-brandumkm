// database/migrations/xxxx_create_umkm_designs_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umkm_designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkms')->onDelete('cascade');
            $table->foreignId('designer_id')->constrained('users')->onDelete('cascade');
            $table->string('file_path');
            $table->string('gerobak_depan')->nullable();
            $table->string('gerobak_kiri')->nullable();
            $table->string('gerobak_kanan')->nullable();
            $table->enum('status', ['pending', 'approved', 'revision_needed', 'revised'])->default('pending');
            $table->text('catatan_revisi')->nullable();
            $table->integer('versi')->default(1);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umkm_designs');
    }
};