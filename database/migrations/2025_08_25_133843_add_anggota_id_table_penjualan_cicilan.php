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
        if (!Schema::hasTable('penjualan_cicilan') || Schema::hasColumn('penjualan_cicilan', 'anggota_id')) {
            return;
        }

        Schema::table('penjualan_cicilan', function (Blueprint $table) {
            $table->integer('anggota_id')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('penjualan_cicilan') || !Schema::hasColumn('penjualan_cicilan', 'anggota_id')) {
            return;
        }

        Schema::table('penjualan_cicilan', function (Blueprint $table) {
            $table->dropColumn('anggota_id');
        });
    }
};
