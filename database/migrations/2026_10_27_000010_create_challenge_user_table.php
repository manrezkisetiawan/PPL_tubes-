<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            // berjalan | selesai | gagal | batal
            $table->string('status')->default('berjalan');
            // Persentase capaian 0-100.
            $table->decimal('progress', 5, 2)->default(0);
            $table->timestamp('joined_at')->useCurrent();

            $table->primary(['user_id', 'challenge_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_user');
    }
};
