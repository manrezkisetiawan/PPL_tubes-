<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('emission_factor_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            // Hasil perhitungan disimpan saat pencatatan (KNF-06).
            $table->decimal('emission', 12, 4);
            $table->date('activity_date');
            $table->string('note')->nullable();
            $table->timestamps();

            // Indeks gabungan untuk kueri rentang tanggal per pengguna (KNF-07).
            $table->index(['user_id', 'activity_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
