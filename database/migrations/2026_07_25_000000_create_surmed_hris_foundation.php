<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureUsersColumns();
        $this->ensurePegawaiDetailColumns();
        $this->ensureSettingTable();
        $this->ensureUnitKerjaTable();
        $this->ensureMenusTable();
        $this->ensureKelompokJamTable();
        $this->ensureJadwalTable();
        $this->ensurePresensiTable();
        $this->ensureLemburTable();
        $this->ensurePengajuanIzinTable();
        $this->ensurePayrollTable();
    }

    public function down(): void
    {
        foreach ([
            'payroll',
            'pengajuan_izin',
            'lembur',
            'presensi',
            'jadwal',
            'kelompokjam',
            'menus',
            'unitkerja',
            'setting',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function ensureUsersColumns(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nip')) {
                $table->string('nip', 50)->nullable()->after('nik');
            }

            if (!Schema::hasColumn('users', 'nohp')) {
                $table->string('nohp', 50)->nullable()->after('status');
            }

            if (!Schema::hasColumn('users', 'alamat')) {
                $table->text('alamat')->nullable()->after('nohp');
            }

            if (!Schema::hasColumn('users', 'id_unitkerja')) {
                $table->unsignedBigInteger('id_unitkerja')->nullable()->after('unit_kerja');
            }
        });
    }

    private function ensurePegawaiDetailColumns(): void
    {
        if (!Schema::hasTable('pegawai_dtls')) {
            Schema::create('pegawai_dtls', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        Schema::table('pegawai_dtls', function (Blueprint $table) {
            $columns = [
                'nik' => fn () => $table->string('nik', 20)->nullable()->index(),
                'nama' => fn () => $table->string('nama', 150)->nullable(),
                'awal_kontrak' => fn () => $table->date('awal_kontrak')->nullable(),
                'akhir_kontrak' => fn () => $table->date('akhir_kontrak')->nullable(),
                'no_jkn_kis' => fn () => $table->string('no_jkn_kis', 30)->nullable(),
                'no_kpj' => fn () => $table->string('no_kpj', 30)->nullable(),
                'tempat_lahir' => fn () => $table->string('tempat_lahir', 100)->nullable(),
                'tanggal_lahir' => fn () => $table->date('tanggal_lahir')->nullable(),
                'alamat_ktp' => fn () => $table->text('alamat_ktp')->nullable(),
                'kode_pos' => fn () => $table->string('kode_pos', 10)->nullable(),
                'jenis_kelamin' => fn () => $table->string('jenis_kelamin', 1)->nullable(),
                'gol_darah' => fn () => $table->string('gol_darah', 2)->nullable(),
                'status_perkawinan' => fn () => $table->string('status_perkawinan', 20)->nullable(),
                'jumlah_anak' => fn () => $table->unsignedSmallInteger('jumlah_anak')->nullable(),
                'nama_ibu_kandung' => fn () => $table->string('nama_ibu_kandung', 150)->nullable(),
                'no_hp' => fn () => $table->string('no_hp', 50)->nullable(),
                'email_aktif' => fn () => $table->string('email_aktif', 150)->nullable(),
                'pendidikan_terakhir' => fn () => $table->string('pendidikan_terakhir', 100)->nullable(),
            ];

            foreach ($columns as $column => $definition) {
                if (!Schema::hasColumn('pegawai_dtls', $column)) {
                    $definition();
                }
            }
        });
    }

    private function ensureSettingTable(): void
    {
        if (Schema::hasTable('setting')) {
            return;
        }

        Schema::create('setting', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perusahaan')->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon', 50)->nullable();
            $table->string('path_logo')->nullable();
            $table->string('lokasi_klinik', 100)->nullable();
            $table->unsignedInteger('radius_absensi')->default(100);
        });
    }

    private function ensureUnitKerjaTable(): void
    {
        if (Schema::hasTable('unitkerja')) {
            return;
        }

        Schema::create('unitkerja', function (Blueprint $table) {
            $table->id();
            $table->string('namaunit', 150);
            $table->string('lokasi', 100)->nullable();
            $table->decimal('umk', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    private function ensureMenusTable(): void
    {
        if (Schema::hasTable('menus')) {
            return;
        }

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('link')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('role')->nullable();
            $table->unsignedInteger('seq')->default(0);
            $table->string('icon')->nullable();
            $table->string('module')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureKelompokJamTable(): void
    {
        if (Schema::hasTable('kelompokjam')) {
            return;
        }

        Schema::create('kelompokjam', function (Blueprint $table) {
            $table->id();
            $table->string('shift', 100)->unique();
            $table->time('jammasuk')->nullable();
            $table->time('jampulang')->nullable();
            $table->time('jammasuk_sabtu')->nullable();
            $table->time('jampulang_sabtu')->nullable();
            $table->unsignedSmallInteger('toleransi_menit')->default(30);
        });
    }

    private function ensureJadwalTable(): void
    {
        if (Schema::hasTable('jadwal')) {
            return;
        }

        Schema::create('jadwal', function (Blueprint $table) {
            $table->bigIncrements('idjadwal');
            $table->date('tgl');
            $table->string('pegawai_nik', 20);
            $table->string('shift', 100)->nullable();
            $table->unique(['tgl', 'pegawai_nik']);
        });
    }

    private function ensurePresensiTable(): void
    {
        if (Schema::hasTable('presensi')) {
            return;
        }

        Schema::create('presensi', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20);
            $table->date('tgl_presensi');
            $table->time('jam_in')->nullable();
            $table->unsignedTinyInteger('inoutmode');
            $table->string('shift', 100)->nullable();
            $table->string('foto_in')->nullable();
            $table->string('lokasi', 100)->nullable();
            $table->timestamps();
            $table->index(['nik', 'tgl_presensi']);
        });
    }

    private function ensureLemburTable(): void
    {
        if (Schema::hasTable('lembur')) {
            return;
        }

        Schema::create('lembur', function (Blueprint $table) {
            $table->bigIncrements('idlembur');
            $table->string('nik', 20);
            $table->date('tgl_lembur');
            $table->timestamps();
            $table->unique(['nik', 'tgl_lembur']);
        });
    }

    private function ensurePengajuanIzinTable(): void
    {
        if (Schema::hasTable('pengajuan_izin')) {
            return;
        }

        Schema::create('pengajuan_izin', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20);
            $table->date('tgl_izin');
            $table->string('status', 10);
            $table->text('keterangan')->nullable();
            $table->unsignedTinyInteger('status_approved')->default(0);
            $table->timestamps();
        });
    }

    private function ensurePayrollTable(): void
    {
        if (Schema::hasTable('payroll')) {
            return;
        }

        Schema::create('payroll', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 7);
            $table->string('nik', 20);
            $table->string('nama')->nullable();
            $table->unsignedInteger('jmlabsen')->default(0);
            $table->string('lembur', 20)->nullable();
            $table->string('terlambat', 20)->nullable();
            $table->unsignedInteger('cuti')->default(0);
            $table->decimal('gaji', 15, 2)->default(0);
            $table->decimal('tunjangan', 15, 2)->nullable();
            $table->decimal('nominallembur', 15, 2)->default(0);
            $table->decimal('hln', 15, 2)->default(0);
            $table->decimal('bpjs_kes', 15, 2)->nullable();
            $table->decimal('bpjs_tk', 15, 2)->nullable();
            $table->decimal('kasbon', 15, 2)->nullable();
            $table->decimal('sisakasbon', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['periode', 'nik']);
        });
    }
};
