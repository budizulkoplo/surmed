<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Presensi;
use App\Models\PengajuanIzin;
use App\Models\KelompokJam;
use App\Models\Jadwal;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::now();
        $bulan = $today->month;
        $tahun = $today->year;

        // ==============================
        // INFORMASI LAYANAN
        // ==============================
        $setting = \DB::table('setting')->first();
        $informasiLayanan = null;

        if ($setting) {
            // ambil tagihan terbaru berdasarkan periode
            $latestTagihan = \DB::table('tagihan')
                ->where('setting_id', $setting->id)
                ->orderByDesc('periode')
                ->first();

            $latestTagihan = \DB::table('tagihan')
    ->where('setting_id', $setting->id)
    ->orderByDesc('periode')
    ->first();

if ($latestTagihan) {
    $start = Carbon::parse($setting->start_layanan); // info awal layanan
    $periode = Carbon::parse($latestTagihan->periode);
    $tanggalTagihan = Carbon::parse($latestTagihan->tanggal_tagihan);
    $jatuhTempo = Carbon::parse($latestTagihan->jatuh_tempo);

    // status sekarang ambil dari kolom status tagihan
    $status = match ($latestTagihan->status) {
        'paid' => 'Lunas',
        'unpaid' => 'Tagihan Aktif',
        'overdue' => 'Lewat Jatuh Tempo',
        default => 'Aktif',
    };

    $informasiLayanan = [
        'nama_perusahaan' => $setting->nama_perusahaan,
        'biaya' => number_format($latestTagihan->total ?? 1000000, 0, ',', '.'),
        'start_layanan' => $start->format('d M Y'),
        'periode_tagihan' => $periode->format('M Y'),
        'tagihan_berikut' => $tanggalTagihan->format('d M Y'),
        'jatuh_tempo' => $jatuhTempo->format('d M Y'),
        'status' => $status,
    ];
}

        }

        // ==============================
        // DATA PEGAWAI AKTIF
        // ==============================
        $pegawaiAktif = User::with('unitKerja')
            ->where('status', 'aktif')
            ->whereHas('pegawaiDtl') // hanya user yang punya data di pegawai_dtl
            ->get();


        // ambil semua izin bulan ini
        $izinBulanIni = PengajuanIzin::whereYear('tgl_izin', $tahun)
            ->whereMonth('tgl_izin', $bulan)
            ->where('status_approved', 1)
            ->get()
            ->groupBy('nik');

        // ambil semua jadwal bulan ini
        $jadwalBulanIni = Jadwal::whereBetween('tgl', [
                Carbon::createFromDate($tahun, $bulan, 1)->toDateString(),
                Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString()
            ])
            ->get()
            ->groupBy('pegawai_nik');

        $dataPegawai = [];
        $totalTerlambatSemuaPegawai = 0;

        foreach ($pegawaiAktif as $pegawai) {
            $totalTerlambat = 0;
            $awal = Carbon::createFromDate($tahun, $bulan, 1);
            $akhir = $awal->copy()->endOfMonth();
            $cursor = $awal->copy();

            $jadwalPegawai = $jadwalBulanIni->get($pegawai->nik) ?? collect();
            $jadwalPegawaiByDate = $jadwalPegawai->keyBy('tgl');

            $presensiPegawai = Presensi::where('nik', $pegawai->nik)
                ->whereYear('tgl_presensi', $tahun)
                ->whereMonth('tgl_presensi', $bulan)
                ->get()
                ->groupBy('tgl_presensi');

            while ($cursor->lte($akhir)) {
                $tgl = $cursor->format('Y-m-d');

                // cek izin
                $izin = $izinBulanIni->get($pegawai->nik)?->firstWhere('tgl_izin', $tgl);
                if ($izin) {
                    $cursor->addDay();
                    continue;
                }

                $jadwalHariIni = $jadwalPegawaiByDate->get($tgl);
                $shiftName = $jadwalHariIni->shift ?? 'office';
                $jamShift = KelompokJam::where('shift', $shiftName)->first();
                $jamMasuk = $jamShift->jammasuk ?? null;

                $presensiHariIni = $presensiPegawai->get($tgl)?->firstWhere('inoutmode', 1);

                if ($jamMasuk && $presensiHariIni?->jam_in) {
                    try {
                        $jamMasukDt = Carbon::parse("$tgl $jamMasuk");
                        $jamPresensi = Carbon::parse("$tgl {$presensiHariIni->jam_in}");

                        // ===== LOGIKA SHIFT MALAM =====
                        if (strtolower($shiftName) === 'malam') {
                            // jam masuk malam (misal 22:00), presensi jam < 12:00 → geser ke hari berikut
                            if ($jamPresensi->lt($jamMasukDt)) {
                                $jamPresensi->addDay();
                            }
                        }

                        if ($jamPresensi->gt($jamMasukDt)) {
                            $terlambat = $jamMasukDt->diffInMinutes($jamPresensi);
                            $totalTerlambat += $terlambat;
                        }
                    } catch (\Exception $e) {}
                }

                $cursor->addDay();
            }

            $totalTerlambatSemuaPegawai += $totalTerlambat;
            $izinCount = $izinBulanIni->get($pegawai->nik)?->count() ?? 0;

            $dataPegawai[] = [
                'nama' => $pegawai->name,
                'nik' => $pegawai->nik,
                'total_terlambat' => $totalTerlambat,
                'izin' => $izinCount,
                'unit_kerja' => $pegawai->unitKerja->namaunit ?? null
            ];
        }

        // ==============================
        // Ringkasan kehadiran
        // ==============================
        $totalHadir = Presensi::whereYear('tgl_presensi', $tahun)
            ->whereMonth('tgl_presensi', $bulan)
            ->distinct('nik')
            ->count('nik');

        $persentaseKehadiran = count($pegawaiAktif) > 0
            ? round(($totalHadir / count($pegawaiAktif)) * 100, 2)
            : 0;

        // ==============================
        // Total izin bulan ini
        // ==============================
        $totalIzin = \App\Models\PengajuanIzin::whereYear('tgl_izin', $tahun)
            ->whereMonth('tgl_izin', $bulan)
            ->where('status_approved', 1)
            ->count();

        $ringkasanAbsensi = [
            'pegawai_aktif' => count($pegawaiAktif),
            'persentase_kehadiran' => $persentaseKehadiran,
            'total_terlambat' => $totalTerlambatSemuaPegawai,
            'total_izin' => $totalIzin,
            'data_per_pegawai' => $dataPegawai,
            'nama_klinik' => 'Klinik Surya Medika',
            'bulan' => $bulan,
            'tahun' => $tahun
        ];

        return view('dashboard', compact('informasiLayanan', 'ringkasanAbsensi'));
    }
}
