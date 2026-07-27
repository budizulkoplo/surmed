<?php

namespace App\Http\Controllers\Mobile;

use App\Models\Pengajuanizin;  
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;
use App\Models\KelompokJam;
use App\Models\Jadwal;
use Carbon\Carbon;

class PresensiController extends BaseMobileController
{

    public function create()
    {
        $hariini = date("Y-m-d");
        $nik = $this->user->nik;
        $cek = DB::table('presensi')->where('tgl_presensi', $hariini)->where('nik', $nik)->count();
        return view('mobile.presensi.create', compact('cek'));
    }

    public function lembur()
    {
        $hariini = date("Y-m-d");
        $nik = $this->user->nik;
        $cek = DB::table('presensi')->where('tgl_presensi', $hariini)->where('nik', $nik)->count();
        return view('mobile.presensi.lembur', compact('cek'));
    }

    public function store(Request $request)
    {
        $nik = $this->user->nik;
        $tgl_presensi = date("Y-m-d");
        $jam = date("H:i:s");
        $lokasi = $request->lokasi;
        $image = $request->image;
        $inoutmode = (int) $request->inoutmode; // cast ke integer

        // Validasi gambar
        if (!$image) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gambar tidak ditemukan.'
            ]);
        }

        // Validasi mode
        if (!in_array($inoutmode, [1, 2, 3, 4])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mode presensi tidak valid.'
            ]);
        }

        // Cek presensi sesuai mode
        $cek = DB::table('presensi')
            ->where('nik', $nik)
            ->where('tgl_presensi', $tgl_presensi)
            ->where('inoutmode', $inoutmode)
            ->first();

        if ($cek) {
            $modeText = [
                1 => 'absen masuk',
                2 => 'absen pulang',
                3 => 'lembur masuk',
                4 => 'lembur pulang'
            ];
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah melakukan ' . $modeText[$inoutmode] . ' hari ini.'
            ]);
        }

        $detectedShift = in_array($inoutmode, [1, 2])
            ? $this->detectShiftForAttendance($tgl_presensi, $jam, $inoutmode)
            : null;

        // Simpan foto
        $modeFileMap = [
            1 => 'in',
            2 => 'out',
            3 => 'lembur_in',
            4 => 'lembur_out',
        ];
        $formatName = $nik . "-" . $tgl_presensi . "-" . $modeFileMap[$inoutmode];

        $image_parts = explode(";base64,", $image);
        if (count($image_parts) < 2) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format gambar tidak valid.'
            ]);
        }
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $formatName . ".png";
        $filePath = 'uploads/absensi/' . $fileName;

        // Simpan data presensi
        $data = [
            'nik' => $nik,
            'tgl_presensi' => $tgl_presensi,
            'jam_in' => $jam,
            'inoutmode' => $inoutmode,
            'foto_in' => $fileName,
            'lokasi' => $lokasi,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($detectedShift && Schema::hasColumn('presensi', 'shift')) {
            $data['shift'] = $detectedShift->shift;
        }

        $simpan = DB::table('presensi')->insert($data);

        if ($simpan) {
            if ($detectedShift) {
                Jadwal::updateOrCreate(
                    ['pegawai_nik' => $nik, 'tgl' => $detectedShift->jadwal_tgl ?? $tgl_presensi],
                    ['shift' => $detectedShift->shift]
                );
            }

            Storage::disk('public')->put($filePath, $image_base64);

            $messages = [
                1 => 'Absen masuk berhasil!',
                2 => 'Absen pulang berhasil!',
                3 => 'Lembur masuk berhasil!',
                4 => 'Lembur pulang berhasil!',
            ];

            if ($detectedShift) {
                $messages[$inoutmode] .= ' Shift terdeteksi: ' . $detectedShift->shift . '.';
            }

            $type = in_array($inoutmode, [1, 3]) ? 'in' : 'out';

            return response()->json([
                'status' => 'success',
                'message' => $messages[$inoutmode],
                'type' => $type,
                'redirect' => route('mobile.home'),
                'shift' => $detectedShift->shift ?? null,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Gagal menyimpan presensi, silakan coba lagi.'
        ]);
    }


    public function editprofile()
    {
        $nik = $this->user->nik;
        $karyawan = DB::table('users')->where('nik', $nik)->first();

        return view('mobile.presensi.editprofile', compact('karyawan'));
    }

    public function updateprofile(Request $request)
    {
        $nik = $this->user->nik;

        // Ambil data input
        $name     = $request->name;
        $nohp     = $request->nohp;
        $email    = $request->email;
        $alamat   = $request->alamat;
        $newNik   = $request->nik; // jika NIK boleh diedit

        $karyawan = DB::table('users')->where('nik', $nik)->first();
        if (!$karyawan) {
            return Redirect::back()->with(['error' => 'Data karyawan tidak ditemukan']);
        }

        // 🔒 Jika password diisi baru hash, jika tidak biarkan
        $data = [
            'name'   => $name,
            'nohp'   => $nohp,
            'email'  => $email,
            'alamat' => $alamat,
        ];

        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        // 📷 Proses foto
        if ($request->hasFile('foto')) {
            $foto = $newNik . '.' . $request->file('foto')->getClientOriginalExtension();
            $folderPath = 'uploads/karyawan/';

            // Hapus foto lama jika ada
            if (!empty($karyawan->foto) && Storage::disk('public')->exists($folderPath . $karyawan->foto)) {
                Storage::disk('public')->delete($folderPath . $karyawan->foto);
            }

            // Simpan foto baru
            $request->file('foto')->storeAs($folderPath, $foto, 'public');
            $data['foto'] = $foto;
        }

        // Jika NIK boleh diubah
        if ($newNik && $newNik !== $nik) {
            $data['nik'] = $newNik;
        }

        // 🚀 Update ke database
        $update = DB::table('users')->where('nik', $nik)->update($data);

        if ($update) {
            return Redirect::back()->with(['success' => 'Data berhasil diperbarui']);
        }

        return Redirect::back()->with(['error' => 'Tidak ada perubahan data']);
    }

    public function histori()
    {
        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni","Juli", "Agustus","September", "Oktober", "November", "Desember"];
        return view('mobile.presensi.histori', compact('namabulan'));
    }

    public function gethistori(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $nik = $this->user->nik;

        $histori = DB::table('presensi')
        ->whereRaw('MONTH(tgl_presensi)="'.$bulan.'"')
        ->whereRaw('YEAR(tgl_presensi)="'.$tahun.'"')
        ->where('nik',$nik)
        ->orderBy('tgl_presensi')
        ->get();

        return view('mobile.presensi.gethistori', compact('histori'));
    }
    public function izin()
    {
        $nik = $this->user->nik;
        $dataizin = DB::table('pengajuan_izin')->where('nik', $nik)->get();
        return view('mobile.presensi.izin', compact('dataizin'));
    }

    public function buatizin()
    {
        return view('mobile.presensi.buatizin');
    }

    public function storeizin(Request $request)
    {
        $nik = $this->user->nik;
        $tgl_izin = $request->tgl_izin;
        $status = $request->status;
        $keterangan = $request->keterangan;

        $data = [
            'nik' => $nik,
            'tgl_izin' => $tgl_izin,
            'status' => $status,
            'keterangan' => $keterangan
        ];

        $simpan = DB::table('pengajuan_izin')->insert($data);

        if( $simpan) {
            return redirect('/presensi/izin')->with(['success' => 'Data Berhasil Disimpan']);
        } else {
            return redirect('/presensi/izin')->with(['error' => 'Data Gagal Disimpan']);
        }
    }

    public function monitoring()
    {
        return view('mobile.presensi.monitoring');
    }

    public function getpresensi(Request $request)
    {
        $tanggal = $request->tanggal;
        $presensi = DB::table('presensi')
            ->select('mobile.presensi.*', 'name', 'nama_dept')
            ->join('karyawan', 'mobile.presensi.nik', '=', 'karyawan.nik')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->where('tgl_presensi', $tanggal)
            ->get();

            return view('mobile.presensi.getpresensi', compact('presensi'));
    }

    public function tampilkanpeta(Request $request)
    {
        $id = $request->id;
        $presensi = DB::table('presensi')->where('id', $id)
            ->join('karyawan', 'mobile.presensi.nik', '=', 'karyawan.nik')
            ->first();

        return view('mobile.presensi.showmap', compact('presensi'));
    }

    public function laporan()
    {
        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni","Juli", "Agustus","September", "Oktober", "November", "Desember"];
        $karyawan = DB::table('karyawan')->orderBy('name')->get();
        return view('mobile.presensi.laporan', compact('namabulan', 'karyawan'));
    }

    public function cetaklaporan(Request $request)
    {
        $nik = $request->nik;
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni","Juli", "Agustus","September", "Oktober", "November", "Desember"];
        $karyawan = DB::table('karyawan')->where('nik', $nik)
        ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
        ->first();

        $presensi = DB::table('presensi')
        ->where('nik',$nik)
        ->whereRaw('MONTH(tgl_presensi)="'.$bulan.'"')
        ->whereRaw('YEAR(tgl_presensi)="'.$tahun.'"')
        ->orderBy('tgl_presensi')
        ->get();

        if  (isset($_POST['exportexel'])) {
            $time = date("d-M-Y H:i:s");
            //Fungsi header dengan mengirimkan raw data exel
           header("Content-type: application/vnd-ms-exel");
            //Mendefiniskan Nama File Export "hasil-export.xls"
            header("Content-Disposition: attachment; filename=Laporan Presensi Ahad Pagi RS PKU $time.xls");
            return view('mobile.presensi.cetaklaporanexel', compact('bulan', 'tahun', 'namabulan',  'karyawan', 'presensi'));
        }
        return view('mobile.presensi.cetaklaporan', compact('bulan', 'tahun', 'namabulan',  'karyawan', 'presensi'));
    }

    public function rekap()
    {
        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni","Juli", "Agustus","September", "Oktober", "November", "Desember"];
        return view('mobile.presensi.rekap', compact('namabulan'));
    }

    public function cetakrekap(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni","Juli", "Agustus","September", "Oktober", "November", "Desember"];
        $rekap = DB::table('presensi')
        ->select('mobile.presensi.nik', 'karyawan.name', 'mobile.presensi.tgl_presensi', 'mobile.presensi.jam_in')
        ->join('karyawan', 'mobile.presensi.nik', '=', 'karyawan.nik')
        ->whereRaw('MONTH(tgl_presensi) = ?', [$bulan])
        ->whereRaw('YEAR(tgl_presensi) = ?', [$tahun]) 
        ->whereRaw('DAYOFWEEK(tgl_presensi) = 1') 
        ->orderBy('mobile.presensi.tgl_presensi', 'ASC') 
        ->get();

    if  (isset($_POST['exportexel'])) {
        $time = date("d-M-Y H:i:s");
        //Fungsi header dengan mengirimkan raw data exel
       header("Content-type: application/vnd-ms-exel");
        //Mendefiniskan Nama File Export "hasil-export.xls"
        header("Content-Disposition: attachment; filename=Rekap Presensi Ahad Pagi RS PKU $time.xls");
    }
        return view('mobile.presensi.cetakrekap', compact('bulan', 'tahun', 'namabulan', 'rekap'));
    }

    public function izinsakit(Request $request)
    {
        $query = Pengajuanizin::query();
        $query->select('id', 'tgl_izin', 'pengajuan_izin.nik', 'name', 'jabatan', 'status', 'status_approved', 'keterangan');
        $query->join('karyawan', 'pengajuan_izin.nik', '=', 'karyawan.nik');
        if (!empty($request->dari) && !empty($request->sampai)) {
            $query->whereBetween('tgl_izin', [$request->dari, $request->sampai]);
        }

        if (!empty($request->nik)) {
            $query->where('pengajuan_izin.nik', $request->nik);
        }

        if (!empty($request->name)) {
            $query->where('name', 'like', '%'. $request->name. '%');
        }

        if ($request->status_approved === '0' || $request->status_approved === '1' || $request->status_approved === '2') {
            $query->where('status_approved', $request->status_approved);
        }
        $query->orderBy('tgl_izin', 'desc');
        $izinsakit =$query->paginate(50);
        $izinsakit->appends($request->all());
        return view('mobile.presensi.izinsakit', compact('izinsakit'));
    }

    // Menampilkan semua pengajuan untuk approval (global)
    public function approvalizin(Request $request)
    {
        $query = Pengajuanizin::query();
        $query->select('pengajuan_izin.*', 'users.name', 'users.jabatan')
            ->join('users', 'pengajuan_izin.nik', '=', 'users.nik');

        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }
        if (!empty($request->status_approved)) {
            $query->where('status_approved', $request->status_approved);
        }
        if (!empty($request->dari) && !empty($request->sampai)) {
            $query->whereBetween('tgl_izin', [$request->dari, $request->sampai]);
        }

        // Filter berdasarkan input bulan (format yyyy-mm)
        if (!empty($request->bulan)) {
            $bulan = explode('-', $request->bulan)[1];
            $tahun = explode('-', $request->bulan)[0];
            $query->whereYear('tgl_izin', $tahun)
                ->whereMonth('tgl_izin', $bulan);
        }

        $query->orderBy('tgl_izin', 'desc');
        $izinsakit = $query->paginate(50);
        $izinsakit->appends($request->all());

        return view('mobile.presensi.approvalizin', compact('izinsakit'));
    }

    // Approve / Decline pengajuan
    public function approvedizin(Request $request)
    {
        $id = $request->id_izinsakit_form;
        $status_approved = $request->status_approved; // 1: Approve, 2: Decline

        $update = DB::table('pengajuan_izin')->where('id', $id)->update([
            'status_approved' => $status_approved
        ]);

        if ($update) {
            return Redirect::back()->with(['success' => 'Status berhasil diupdate']);
        } else {
            return Redirect::back()->with(['warning' => 'Gagal mengupdate status']);
        }
    }

    // Batalkan approval (set ke pending / 0)
    public function batalkanizin($id)
    {
        $update = DB::table('pengajuan_izin')->where('id', $id)->update([
            'status_approved' => 0
        ]);

        if ($update) {
            return Redirect::back()->with(['success' => 'Status berhasil dibatalkan']);
        } else {
            return Redirect::back()->with(['warning' => 'Gagal membatalkan status']);
        }
    }

    // Hapus pengajuan
    public function hapusizin($id)
    {
        $delete = DB::table('pengajuan_izin')->where('id', $id)->delete();

        if ($delete) {
            return Redirect::back()->with(['success' => 'Data berhasil dihapus']);
        } else {
            return Redirect::back()->with(['warning' => 'Gagal menghapus data']);
        }
    }
    
    public function cekRadius(Request $request)
    {
        $lokasiUser = explode(',', $request->lokasi);
        if (count($lokasiUser) != 2) {
            return response()->json(['status' => 'error', 'message' => 'Format lokasi tidak valid'], 400);
        }

        $latitude = floatval($lokasiUser[0]);
        $longitude = floatval($lokasiUser[1]);

        $clinic = $this->clinicLocation();
        [$unitLat, $unitLng] = [$clinic['lat'], $clinic['lng']];

        // Hitung jarak (meter)
        $earthRadius = 6371000; // meter
        $latFrom = deg2rad($latitude);
        $lonFrom = deg2rad($longitude);
        $latTo = deg2rad($unitLat);
        $lonTo = deg2rad($unitLng);
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        $distance = $earthRadius * $angle;

        if ($distance > $clinic['radius']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda berada di luar area absensi (' . round($distance, 1) . ' m dari Klinik Surya Medika)'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Anda berada dalam radius absensi (' . round($distance, 1) . ' m)'
        ]);
    }

    public function getUnitKerjaLocation(Request $request)
{
    if (!auth()->check()) {
        return response()->json(['status' => 'error', 'message' => 'User tidak terautentikasi'], 401);
    }

    $clinic = $this->clinicLocation();

    return response()->json([
        'status' => 'success',
            'data' => [
                'lat' => $clinic['lat'],
                'lng' => $clinic['lng'],
                'radius' => $clinic['radius'],
                'namaunit' => 'Klinik Surya Medika',
            ]
        ]);
}

    private function clinicLocation(): array
    {
        $setting = Setting::first();
        $location = $setting->lokasi_klinik ?? config('surmed.clinic_location', '-6.966667,110.416664');
        $parts = array_map('trim', explode(',', $location));

        if (count($parts) !== 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
            $parts = ['-6.966667', '110.416664'];
        }

        return [
            'lat' => (float) $parts[0],
            'lng' => (float) $parts[1],
            'radius' => (int) ($setting->radius_absensi ?? 100),
        ];
    }

    private function detectShiftForAttendance(string $tanggal, string $jam, int $inoutmode): ?KelompokJam
    {
        $targetColumn = $inoutmode === 1 ? 'jammasuk' : 'jampulang';
        $attendanceTime = Carbon::parse("$tanggal $jam");

        return KelompokJam::query()
            ->whereRaw('LOWER(shift) <> ?', ['libur'])
            ->get()
            ->first(function ($shift) use ($tanggal, $targetColumn, $attendanceTime) {
                $targetJam = KelompokJam::timeForDate($shift, $targetColumn, $tanggal);
                if (!$targetJam) {
                    return false;
                }

                $tolerance = (int) ($shift->toleransi_menit ?? 30);
                $candidateDates = [$tanggal];

                if ($targetColumn === 'jampulang') {
                    $candidateDates[] = Carbon::parse($tanggal)->subDay()->toDateString();
                }

                foreach ($candidateDates as $shiftDate) {
                    $shiftJamMasuk = $shift->jamMasukForDate($shiftDate);
                    $shiftJamPulang = $shift->jamPulangForDate($shiftDate);
                    $targetJamForDate = KelompokJam::timeForDate($shift, $targetColumn, $shiftDate);

                    if (!$targetJamForDate) {
                        continue;
                    }

                    $targetTime = Carbon::parse("$shiftDate $targetJamForDate");

                    if ($targetColumn === 'jampulang' && $shiftJamMasuk && $shiftJamPulang) {
                        $start = Carbon::parse("$shiftDate $shiftJamMasuk");
                        $end = Carbon::parse("$shiftDate $shiftJamPulang");
                        if ($end->lt($start)) {
                            $targetTime->addDay();
                        }
                    }

                    if ($attendanceTime->betweenIncluded(
                        $targetTime->copy()->subMinutes($tolerance),
                        $targetTime->copy()->addMinutes($tolerance)
                    )) {
                        $shift->jadwal_tgl = $shiftDate;
                        return true;
                    }
                }

                return false;
            });
    }
}
