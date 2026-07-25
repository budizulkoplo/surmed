<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\KelompokJam;
use App\Models\Jadwal;
use App\Models\Lembur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;



class JadwalController extends Controller

{

    public function index()

    {

        $kelompokjam = KelompokJam::orderBy('id')->get();

        return view('master.jadwal.index', compact('kelompokjam'));

    }

    public function getPegawai(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $pegawai = User::select('id', 'nik', 'name', 'jabatan')
            ->orderBy('name')
            ->get();

        $daysInMonth = Carbon::create($tahun, $bulan, 1)->daysInMonth;
        $tgl = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $tgl[] = Carbon::create($tahun, $bulan, $d)->toDateString();
        }

        $jadwal = Jadwal::whereMonth('tgl', $bulan)
            ->whereYear('tgl', $tahun)
            ->whereIn('pegawai_nik', $pegawai->pluck('nik'))
            ->get();

        $lembur = Lembur::whereMonth('tgl_lembur', $bulan)
            ->whereYear('tgl_lembur', $tahun)
            ->whereIn('nik', $pegawai->pluck('nik'))
            ->get();

        // Buat object lembur untuk JS
        $lemburDB = [];
        foreach ($lembur as $l) {
            $lemburDB[$l->nik][$l->tgl_lembur] = true;
        }

        return response()->json([
            'pegawai' => $pegawai,
            'tgl' => $tgl,
            'jadwal' => $jadwal,
            'lemburDB' => $lemburDB
        ]);
    }

    public function updateShift(Request $request)

    {

        $request->validate([

            'pegawai_nik' => 'required|string',

            'tgl' => 'required|date',

            'shift' => 'nullable|string|max:100',

        ]);



        Jadwal::updateOrCreate(

            [

                'pegawai_nik' => $request->pegawai_nik,

                'tgl' => $request->tgl,

            ],

            [

                'shift' => $request->shift,

            ]

        );



        return response()->json(['success' => true, 'message' => 'Shift berhasil disimpan.']);

    }



    public function generateOtomatis(Request $request)

    {

        $request->validate([

            'bulan' => 'required|integer|min:1|max:12',

            'tahun' => 'required|integer|min:2000',

        ]);



        $bulan = $request->bulan;

        $tahun = $request->tahun;

        $pegawaiList = User::select('nik', 'name')

            ->get();



        $pola = ['Pagi', 'Pagi', 'Siang', 'Siang', 'Malam', 'Malam', 'Libur', 'Libur'];



        DB::beginTransaction();

        try {

            $daysInMonth = Carbon::create($tahun, $bulan, 1)->daysInMonth;



            foreach ($pegawaiList as $p) {

                // cari shift tanggal 1

                $firstShift = Jadwal::where('pegawai_nik', $p->nik)

                    ->whereMonth('tgl', $bulan)

                    ->whereYear('tgl', $tahun)

                    ->whereDay('tgl', 1)

                    ->value('shift');



                // kalau belum ada shift tgl 1, skip

                if (!$firstShift) {

                    continue;

                }



                // cari posisi start di pola

                $startIndex = array_search($firstShift, $pola);

                if ($startIndex === false) $startIndex = 0;



                // generate semua hari

                for ($d = 1; $d <= $daysInMonth; $d++) {

                    $tgl = Carbon::create($tahun, $bulan, $d)->toDateString();

                    $shift = $pola[($startIndex + $d - 1) % count($pola)];



                    Jadwal::updateOrCreate(

                        ['pegawai_nik' => $p->nik, 'tgl' => $tgl],

                        ['shift' => $shift]

                    );

                }

            }



            DB::commit();

            return response()->json(['success' => true, 'message' => 'Jadwal otomatis berhasil digenerate.']);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Gagal generate: ' . $e->getMessage()]);

        }

    }

    public function updateLembur(Request $request)
    {
        $request->validate([
            'nik' => 'required|integer',
            'tgl_lembur' => 'required|date',
            'checked' => 'required|boolean',
        ]);

        if ($request->checked) {
            // Simpan lembur baru
            Lembur::firstOrCreate([
                'nik' => $request->nik,
                'tgl_lembur' => $request->tgl_lembur,
            ]);
        } else {
            // Hapus lembur
            Lembur::where('nik', $request->nik)
                ->where('tgl_lembur', $request->tgl_lembur)
                ->delete();
        }

        return response()->json(['success' => true]);
    }

}

