<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kelompokjam')) {
            Schema::table('kelompokjam', function (Blueprint $table) {
                if (!Schema::hasColumn('kelompokjam', 'toleransi_menit')) {
                    $table->unsignedSmallInteger('toleransi_menit')->default(30)->after('jampulang');
                }
            });
        }

        if (Schema::hasTable('presensi')) {
            Schema::table('presensi', function (Blueprint $table) {
                if (!Schema::hasColumn('presensi', 'shift')) {
                    $table->string('shift', 100)->nullable()->after('inoutmode');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('presensi') && Schema::hasColumn('presensi', 'shift')) {
            Schema::table('presensi', function (Blueprint $table) {
                $table->dropColumn('shift');
            });
        }

        if (Schema::hasTable('kelompokjam') && Schema::hasColumn('kelompokjam', 'toleransi_menit')) {
            Schema::table('kelompokjam', function (Blueprint $table) {
                $table->dropColumn('toleransi_menit');
            });
        }
    }
};
