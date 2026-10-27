<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->nullOnDelete();
            $table->string('photo')->nullable()->after('password');
            $table->string('campus')->nullable()->after('photo');
            $table->string('study_program')->nullable()->after('campus');
            $table->unsignedInteger('points')->default(0)->after('study_program');
            $table->unsignedInteger('streak_count')->default(0)->after('points');
            $table->boolean('is_active')->default(true)->after('streak_count');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn(['photo', 'campus', 'study_program', 'points', 'streak_count', 'is_active']);
        });
    }
};
