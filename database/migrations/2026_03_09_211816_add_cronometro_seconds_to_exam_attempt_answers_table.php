<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exam_attempt_answers', function (Blueprint $table) {
            $table->unsignedInteger('cronometro_segundos')->nullable()->after('time_spent_seconds');
        });

        DB::table('exam_attempt_answers')->update([
            'cronometro_segundos' => DB::raw('time_spent_seconds'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_attempt_answers', function (Blueprint $table) {
            $table->dropColumn('cronometro_segundos');
        });
    }
};
