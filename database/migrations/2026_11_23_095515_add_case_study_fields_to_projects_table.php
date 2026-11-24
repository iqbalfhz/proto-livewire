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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('role', 150)->nullable()->after('description');
            $table->string('year', 20)->nullable()->after('role');
            $table->text('problem')->nullable()->after('year');
            $table->text('approach')->nullable()->after('problem');
            $table->text('outcome')->nullable()->after('approach');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['role', 'year', 'problem', 'approach', 'outcome']);
        });
    }
};
