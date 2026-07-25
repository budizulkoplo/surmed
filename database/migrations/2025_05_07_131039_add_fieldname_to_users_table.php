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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nomor_anggota')) {
                $table->string('nomor_anggota', 20)->nullable();
            }

            if (!Schema::hasColumn('users', 'nik')) {
                $table->string('nik', 20)->nullable();
            }

            if (!Schema::hasColumn('users', 'jabatan')) {
                $table->string('jabatan', 100)->nullable();
            }

            if (!Schema::hasColumn('users', 'unit_kerja')) {
                $table->string('unit_kerja', 100)->nullable();
            }

            if (!Schema::hasColumn('users', 'tanggal_masuk')) {
                $table->date('tanggal_masuk')->nullable();
            }

            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['aktif', 'nonaktif'])->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['nomor_anggota', 'nik', 'jabatan', 'unit_kerja', 'tanggal_masuk', 'status'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                Schema::table('users', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
