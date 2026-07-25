<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

// Controllers
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ProjectSelectionController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\HRISController;
use App\Http\Controllers\UnitKerjaController;
use App\Http\Controllers\PlottingUnitKerjaController;
use App\Http\Controllers\KelompokJamController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\PengajuanIzinController;
use App\Http\Controllers\PayrollController;

// Mobile
use App\Http\Controllers\Mobile\DashboardController;
use App\Http\Controllers\Mobile\PresensiController;
use App\Http\Controllers\Mobile\KalenderController;
/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
*/

// Mobile berada di root domain; admin desktop berada di /login.
Route::get('/', [AuthenticatedSessionController::class, 'mobileCreate'])->name('mobile.home');
Route::post('/masuk', [AuthenticatedSessionController::class, 'store'])->name('mobile.login.store');
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Pilih Project (akses tanpa middleware check.project)
Route::middleware(['auth', 'verified', 'global.app'])->group(function () {
    Route::get('/choose-project', fn () => redirect()->route('dashboard'))->name('choose.project');
    Route::post('/choose-project', fn () => redirect()->route('dashboard'))->name('choose.project.store');
});

// Semua route ini wajib: auth + verified + project sudah dipilih
Route::middleware(['auth', 'verified', 'check.project'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])
        ->middleware(['role:superadmin', 'global.app:admin'])
        ->name('dashboard');
    Route::get('/admin/pesanan-hari-ini', [AdminDashboardController::class, 'pesananHariIni'])
        ->middleware('role:superadmin');
    Route::get('/admin/data-pesanan-hari-ini', [AdminDashboardController::class, 'pesananHariIniData'])
        ->middleware('role:superadmin')
        ->name('dashboard.pesananHariIniData');

    // Profile
    Route::prefix('profile')->middleware(['role:superadmin', 'global.app'])->group(function () {
        
        Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::post('/', [ProfileController::class, 'upload'])->name('profile.upload');
    });

    // Users
    Route::prefix('hris/users')->middleware(['role:superadmin', 'global.app'])->group(function () {
        Route::get('/list', [UsersController::class, 'index'])->name('users.list');
        Route::get('/getdata', [UsersController::class, 'getdata'])->name('users.getdata');
        Route::post('/assignRole', [UsersController::class, 'kasihRole'])->name('users.assignRole');
        Route::post('/password/update', [UsersController::class, 'updatePassword'])->name('users.updatepassword');
        Route::post('/store', [UsersController::class, 'store'])->name('users.store');
        Route::get('/getcode', [UsersController::class, 'getcode'])->name('users.getcode');

        // Role management
        Route::get('/permission', [UserRoleController::class, 'PermissionByRole']);
        Route::post('/add', [UserRoleController::class, 'addRole']);
        Route::delete('/delr', [UserRoleController::class, 'deleteRole']);
        Route::delete('/delp', [UserRoleController::class, 'deletePermission']);
    });

    // Pegawai
    Route::prefix('pegawai')->middleware(['role:superadmin', 'global.app'])->group(function () {
        Route::get('/list', [PegawaiController::class, 'index'])->name('pegawai.list');
        Route::get('/getdata', [PegawaiController::class, 'getdata'])->name('pegawai.getdata');
        Route::post('/store', [PegawaiController::class, 'store'])->name('pegawai.store');
        Route::get('/getcode', [PegawaiController::class, 'getcode'])->name('pegawai.getcode');
        Route::get('/{id}', [PegawaiController::class, 'show'])->name('pegawai.show');   // untuk edit (ambil data 1 pegawai)
        Route::delete('/{id}', [PegawaiController::class, 'destroy'])->name('pegawai.destroy'); // untuk hapus
    });

    Route::prefix('hris')->middleware(['role:superadmin', 'global.app'])->group(function () {
        Route::get('/absensi', [AbsensiController::class, 'index'])->name('hris.absensi');
        Route::get('/absensi/getdata', [AbsensiController::class, 'getAbsensiData'])->name('hris.absensi.getdata');
    });

    // Setting
    Route::prefix('hris/setting')->middleware(['role:superadmin', 'global.app'])->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('setting.index');
        Route::post('/', [SettingController::class, 'update'])->name('setting.update');
    });

    // Roles
    Route::prefix('hris/roles')->middleware(['role:superadmin', 'global.app'])->group(function () {
        Route::get('/list', [UserRoleController::class, 'index'])->name('roles.list');
        Route::get('/permission', [UserRoleController::class, 'PermissionByRole']);
        Route::post('/add', [UserRoleController::class, 'addRole']);
        Route::delete('/delr', [UserRoleController::class, 'deleteRole']);
        Route::delete('/delp', [UserRoleController::class, 'deletePermission']);
        Route::post('/swcp', [UserRoleController::class, 'PermissionfromRole'])->name('roles.switch');
    });

    // Menu
    Route::prefix('hris/menu')->middleware(['role:superadmin', 'global.app'])->group(function () {
        Route::get('/list', [MenuController::class, 'index'])->name('menu.list');
        Route::get('/data/{role}', [MenuController::class, 'datamenu'])->name('menu.data');
        Route::put('/update', [MenuController::class, 'update'])->name('menu.update');
    });

    // Laporan
    Route::prefix('laporan')->middleware(['role:superadmin', 'global.app'])->group(function () {
        Route::get('/transaksi-armada', [LaporanController::class, 'transaksiArmada'])->name('laporan.transaksi_armada');
        Route::get('/transaksi-armada/data', [LaporanController::class, 'transaksiArmadaData'])->name('laporan.transaksi_armada.data');
        Route::get('/project', [LaporanController::class, 'laporanProject'])->name('laporan.project');
        Route::get('/vendor', [LaporanController::class, 'laporanVendor'])->name('laporan.vendor');
    });

    // Static file (private doc/img)
    Route::prefix('doc')->group(function () {
        Route::get('download/{filename}', function ($filename) {
            if (!Auth::check()) abort(403);
            $path = storage_path("app/private/doc/{$filename}");
            if (!file_exists($path)) abort(404);
            return Response::download($path);
        });
        Route::get('file/{path}/{filename}', function ($path, $filename) {
            if (!Auth::check()) abort(403);
            $path = storage_path("app/private/img/{$path}/{$filename}");
            if (!File::exists($path)) abort(404);
            $file = File::get($path);
            $type = File::mimeType($path);
            return Response::make($file, 200)->header("Content-Type", $type);
        });
    });
});

// UI untuk mobile end users
Route::middleware(['auth'])->prefix('presensi')->name('mobile.presensi.')->group(function () {
    Route::get('/create', [PresensiController::class, 'create'])->name('create');
    Route::post('/store', [PresensiController::class, 'store'])->name('store');
    Route::get('/lembur', [PresensiController::class, 'lembur'])->name('lembur');
    // 🔹 Tambahkan route ini
    Route::post('/cek-radius', [PresensiController::class, 'cekRadius'])->name('cekRadius');
    // 🔹 Route untuk ambil lokasi unit kerja
    Route::get('/get-unitkerja-location', [PresensiController::class, 'getUnitKerjaLocation'])
            ->name('getUnitKerjaLocation');

    //Izin
    Route::get('/izin', [PresensiController::class, 'izin']);
    Route::get('/buatizin', [PresensiController::class, 'buatizin']);
    Route::post('/storeizin', [PresensiController::class, 'storeizin']);
    Route::post('/cekpengajuanizin', [PresensiController::class, 'cekpengajuanizin']);

    // Approval Izin/Sakit/Cuti
    Route::get('/approvalizin', [PresensiController::class, 'approvalizin']);
    Route::post('/approvedizin', [PresensiController::class, 'approvedizin']);
    Route::post('/batalkanizin/{id}', [PresensiController::class, 'batalkanizin']);
    Route::delete('/hapusizin/{id}', [PresensiController::class, 'hapusizin']);

    //Edit Profile
    Route::get('/editprofile', [PresensiController::class, 'editprofile']);
    Route::post('{nik}/updateprofile', [PresensiController::class, 'updateprofile']);

    //Histori
    Route::get('/histori', [PresensiController::class, 'histori']);
    Route::post('/gethistori', [PresensiController::class, 'gethistori']);
});

Route::post('/check-lembur', function (Illuminate\Http\Request $r) {
    $user = Auth::user();
    $exists = \App\Models\Lembur::where('nik', $user->nik)
        ->whereDate('tgl_lembur', $r->tgl)
        ->exists();
    return response()->json(['exists' => $exists]);
})->middleware('auth')->name('mobile.check-lembur');


Route::middleware(['auth'])->name('mobile.')->group(function () {
    // Modul Kalender
    Route::prefix('kalender')->name('kalender.')->group(function () {
        Route::get('/', [KalenderController::class, 'index'])->name('index'); // Tampilan utama kalender
        Route::post('/', [KalenderController::class, 'index']);
        Route::get('/lembur', [KalenderController::class, 'lembur'])->name('lembur'); // Halaman lembur
        Route::get('/statistik', [KalenderController::class, 'statistik'])->name('statistik'); // Statistik per bulan
    });

    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/', [App\Http\Controllers\Mobile\PayrollController::class, 'index'])->name('index');
        Route::get('/{tahun}/{bulan}', [App\Http\Controllers\Mobile\PayrollController::class, 'detail'])->name('detail');
        Route::get('/download/{id}', [App\Http\Controllers\Mobile\PayrollController::class, 'downloadSlip'])->name('download');
    });
});

Route::redirect('/mobile/home', '/');
Route::get('/mobile/presensi/{path?}', fn (?string $path = null) => redirect('/presensi'.($path ? '/'.$path : '')))->where('path', '.*');
Route::get('/mobile/kalender/{path?}', fn (?string $path = null) => redirect('/kalender'.($path ? '/'.$path : '')))->where('path', '.*');
Route::get('/mobile/payroll/{path?}', fn (?string $path = null) => redirect('/payroll'.($path ? '/'.$path : '')))->where('path', '.*');

Route::prefix('master')->middleware(['auth', 'verified', 'check.project', 'role:superadmin', 'global.app'])->group(function () {
    Route::get('/unitkerja', fn () => redirect()->route('dashboard'))->name('master.unitkerja');
    Route::get('/unitkerja/data', fn () => response()->json(['data' => []]))->name('master.unitkerja.data');
    Route::get('/unitkerja/{id}', fn () => response()->json(['message' => 'Unit kerja tidak digunakan'], 410))->name('master.unitkerja.show');
    Route::post('/unitkerja', fn () => response()->json(['message' => 'Unit kerja tidak digunakan'], 410))->name('master.unitkerja.store');
    Route::delete('/unitkerja/{id}', fn () => response()->json(['message' => 'Unit kerja tidak digunakan'], 410))->name('master.unitkerja.destroy');
});

Route::prefix('master')->middleware(['auth', 'verified', 'check.project', 'role:superadmin', 'global.app'])->group(function () {
    Route::get('/plotting-unitkerja', fn () => redirect()->route('dashboard'))->name('plotting.unitkerja');
    Route::get('/plotting-unitkerja/data', fn () => response()->json(['data' => []]))->name('plotting.unitkerja.data');
    Route::post('/plotting-unitkerja/update', fn () => response()->json(['message' => 'Unit kerja tidak digunakan'], 410))->name('plotting.unitkerja.update');
});

Route::prefix('master')->middleware(['auth', 'verified', 'check.project', 'role:superadmin', 'global.app'])->group(function () {
    Route::get('kelompokjam', [KelompokJamController::class, 'index'])->name('master.kelompokjam');
    Route::get('kelompokjam/data', [KelompokJamController::class, 'getdata'])->name('master.kelompokjam.data');
    Route::post('kelompokjam/store', [KelompokJamController::class, 'store'])->name('master.kelompokjam.store');
    Route::get('kelompokjam/{id}', [KelompokJamController::class, 'show']);
    Route::delete('kelompokjam/{id}', [KelompokJamController::class, 'destroy']);
});

Route::prefix('master')->middleware(['auth', 'verified', 'check.project', 'role:superadmin', 'global.app'])->group(function () {
    Route::get('jadwal', [JadwalController::class, 'index'])->name('master.jadwal');
    Route::get('jadwal/pegawai', [JadwalController::class, 'getPegawai'])->name('master.jadwal.pegawai');
    Route::post('jadwal/update', [JadwalController::class, 'updateShift'])->name('master.jadwal.update');
    Route::post('jadwal/generate', [JadwalController::class, 'generateOtomatis'])->name('master.jadwal.generate');
    Route::post('jadwal/update-lembur', [JadwalController::class, 'updateLembur'])->name('master.jadwal.update_lembur');

});

Route::prefix('hris')->middleware(['auth', 'verified', 'check.project', 'role:superadmin', 'global.app'])->group(function () {
    Route::get('pengajuan-izin', [PengajuanIzinController::class, 'index'])->name('hris.pengajuanizin');
    Route::get('pengajuan-izin/data', [PengajuanIzinController::class, 'getdata'])->name('hris.pengajuanizin.data');
    Route::get('pengajuan-izin/show/{id}', [PengajuanIzinController::class, 'show'])->name('hris.pengajuanizin.show');
    Route::post('pengajuan-izin/store', [PengajuanIzinController::class, 'store'])->name('hris.pengajuanizin.store');
    Route::delete('pengajuan-izin/{id}', [PengajuanIzinController::class, 'destroy'])->name('hris.pengajuanizin.destroy');
    Route::get('pengajuan-izin/select2/pegawai', [PengajuanIzinController::class, 'getPegawaiSelect2'])->name('hris.pengajuanizin.select2pegawai');
    
    // Laporan Rekap Absensi
    Route::get('laporan/rekap-absensi', [LaporanController::class, 'rekapAbsensi'])->name('hris.laporan.rekap_absensi');
    Route::get('laporan/rekap-absensi/data', [LaporanController::class, 'rekapAbsensiData'])->name('hris.laporan.rekap_absensi.data');
    Route::post('laporan/rekap-absensi/export-payroll', [LaporanController::class, 'exportPayroll'])->name('hris.laporan.rekap_absensi.export_payroll');
    // Laporan Payroll
    Route::get('laporan/payroll', [LaporanController::class, 'laporanPayroll'])->name('hris.laporan.payroll');
    Route::get('laporan/payroll/data', [LaporanController::class, 'laporanPayrollData'])->name('hris.laporan.payroll.data');
    // Laporan Monitoring Presensi
    Route::get('laporan/monitoring-presensi', [LaporanController::class, 'monitoringPresensi'])->name('hris.laporan.monitoring_presensi');
    Route::get('laporan/monitoring-presensi/data', [LaporanController::class, 'monitoringPresensiData'])->name('hris.laporan.monitoring_presensi.data');
    Route::post('laporan/monitoring-presensi/export', [LaporanController::class, 'exportMonitoringPresensi'])->name('hris.laporan.monitoring_presensi.export');

    // === Payroll (Tabel Gaji) ===
    Route::get('payroll', [PayrollController::class, 'index'])->name('hris.payroll.index');
    Route::get('payroll/data', [PayrollController::class, 'getData'])->name('hris.payroll.data');
    Route::post('payroll/update', [PayrollController::class, 'updateManual'])->name('hris.payroll.update_manual');
    Route::get('payroll/slip/{payroll_id}', [PayrollController::class, 'downloadSlip'])->name('hris.payroll.slip');
});
require __DIR__ . '/auth.php';
