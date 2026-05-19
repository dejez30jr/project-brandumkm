// database/migrations/xxxx_add_role_to_users_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'client', 'design', 'pic_lapangan', 'team_pasang'])->default('pic_lapangan')->after('email');
            $table->foreignId('kota_id')->nullable()->constrained('kotas')->onDelete('set null');
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'kota_id', 'is_active']);
        });
    }
};