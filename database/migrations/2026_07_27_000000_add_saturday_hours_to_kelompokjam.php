<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kelompokjam')) {
            return;
        }

        Schema::table('kelompokjam', function (Blueprint $table) {
            if (!Schema::hasColumn('kelompokjam', 'jammasuk_sabtu')) {
                $table->time('jammasuk_sabtu')->nullable()->after('jampulang');
            }

            if (!Schema::hasColumn('kelompokjam', 'jampulang_sabtu')) {
                $table->time('jampulang_sabtu')->nullable()->after('jammasuk_sabtu');
            }
        });

        foreach ($this->shiftDefaults() as $shift => $hours) {
            DB::table('kelompokjam')->updateOrInsert(
                ['shift' => $shift],
                $hours + ['toleransi_menit' => 30]
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('kelompokjam')) {
            return;
        }

        Schema::table('kelompokjam', function (Blueprint $table) {
            if (Schema::hasColumn('kelompokjam', 'jampulang_sabtu')) {
                $table->dropColumn('jampulang_sabtu');
            }

            if (Schema::hasColumn('kelompokjam', 'jammasuk_sabtu')) {
                $table->dropColumn('jammasuk_sabtu');
            }
        });
    }

    private function shiftDefaults(): array
    {
        return [
            'Pagi' => [
                'jammasuk' => '08:00:00',
                'jampulang' => '13:00:00',
                'jammasuk_sabtu' => '08:00:00',
                'jampulang_sabtu' => '12:00:00',
            ],
            'Siang' => [
                'jammasuk' => '13:00:00',
                'jampulang' => '17:00:00',
                'jammasuk_sabtu' => '12:00:00',
                'jampulang_sabtu' => '16:00:00',
            ],
            'Middle 1' => [
                'jammasuk' => '08:00:00',
                'jampulang' => '15:00:00',
                'jammasuk_sabtu' => '08:00:00',
                'jampulang_sabtu' => '14:00:00',
            ],
            'Middle 2' => [
                'jammasuk' => '10:00:00',
                'jampulang' => '17:00:00',
                'jammasuk_sabtu' => '10:00:00',
                'jampulang_sabtu' => '16:00:00',
            ],
        ];
    }
};
