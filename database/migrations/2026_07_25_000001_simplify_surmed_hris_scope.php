<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('setting')) {
            Schema::table('setting', function (Blueprint $table) {
                if (!Schema::hasColumn('setting', 'lokasi_klinik')) {
                    $table->string('lokasi_klinik', 100)->nullable()->after('telepon');
                }

                if (!Schema::hasColumn('setting', 'radius_absensi')) {
                    $table->unsignedInteger('radius_absensi')->default(100)->after('lokasi_klinik');
                }
            });

            $setting = DB::table('setting')->first();
            $payload = [];

            if (Schema::hasColumn('setting', 'nama_perusahaan')) {
                $payload['nama_perusahaan'] = 'Klinik Surya Medika';
            }

            if (Schema::hasColumn('setting', 'lokasi_klinik')) {
                $payload['lokasi_klinik'] = $setting->lokasi_klinik ?? '-6.966667,110.416664';
            }

            if (Schema::hasColumn('setting', 'radius_absensi')) {
                $payload['radius_absensi'] = $setting->radius_absensi ?? 100;
            }

            if ($payload) {
                if ($setting && isset($setting->id)) {
                    DB::table('setting')->where('id', $setting->id)->update($payload);
                } else {
                    DB::table('setting')->insert($payload);
                }
            }
        }

        if (Schema::hasTable('unitkerja')) {
            DB::table('unitkerja')->updateOrInsert(
                ['namaunit' => 'Klinik Surya Medika'],
                [
                    'lokasi' => '-6.966667,110.416664',
                    'umk' => 0,
                ]
            );

            $unitId = DB::table('unitkerja')->where('namaunit', 'Klinik Surya Medika')->value('id');

            if ($unitId && Schema::hasTable('users') && Schema::hasColumn('users', 'id_unitkerja')) {
                DB::table('users')->whereNull('id_unitkerja')->update(['id_unitkerja' => $unitId]);
            }
        }

        if (Schema::hasTable('menus')) {
            $this->moveMenusToHris();
        }

    }

    public function down(): void
    {
        if (Schema::hasTable('setting')) {
            Schema::table('setting', function (Blueprint $table) {
                if (Schema::hasColumn('setting', 'radius_absensi')) {
                    $table->dropColumn('radius_absensi');
                }

                if (Schema::hasColumn('setting', 'lokasi_klinik')) {
                    $table->dropColumn('lokasi_klinik');
                }
            });
        }

    }

    private function moveMenusToHris(): void
    {
        $hasModule = Schema::hasColumn('menus', 'module');
        $hasParent = Schema::hasColumn('menus', 'parent_id');
        $hasSeq = Schema::hasColumn('menus', 'seq');
        $hasIcon = Schema::hasColumn('menus', 'icon');
        $hasRole = Schema::hasColumn('menus', 'role');
        $hasLink = Schema::hasColumn('menus', 'link');

        $masterHris = DB::table('menus')->where('name', 'Master HRIS')->first();

        if (!$masterHris) {
            $payload = ['name' => 'Master HRIS'];

            if ($hasLink) {
                $payload['link'] = null;
            }

            if ($hasParent) {
                $payload['parent_id'] = null;
            }

            if ($hasRole) {
                $payload['role'] = ';superadmin;admin;';
            }

            if ($hasSeq) {
                $payload['seq'] = 10;
            }

            if ($hasIcon) {
                $payload['icon'] = 'bi bi-person-gear';
            }

            if ($hasModule) {
                $payload['module'] = 'hris';
            }

            DB::table('menus')->insert($payload);
            $masterHris = DB::table('menus')->where('name', 'Master HRIS')->first();
        }

        $links = ['users.list', 'roles.list', 'menu.list', 'setting.index'];
        $updates = [];

        if ($hasModule) {
            $updates['module'] = 'hris';
        }

        if ($hasParent && $masterHris) {
            $updates['parent_id'] = $masterHris->id;
        }

        if ($updates && $hasLink) {
            DB::table('menus')->whereIn('link', $links)->update($updates);
            DB::table('menus')->where('link', 'setting.index')->update(['name' => 'Setting']);
        }

        if ($hasModule) {
            DB::table('menus')
                ->whereIn('name', ['User', 'Users', 'Role', 'Roles', 'Menu', 'Setting'])
                ->update(['module' => 'hris']);

            DB::table('menus')
                ->whereIn('link', ['master.unitkerja', 'plotting.unitkerja'])
                ->update(['module' => 'disabled']);
        }
    }
};
