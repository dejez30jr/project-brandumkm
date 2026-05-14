// database/migrations/xxxx_create_kotas_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kotas', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // bogor, depok, tangerang, dll
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kotas');
    }
};