<?php

namespace App\Http\Controllers;

use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\View\View;

class UnitKerjaController extends Controller
{
    public function index(): View
    {
        return view('master.unitkerja.list');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'namaunit' => 'required|string|max:150',
            'lokasi'   => 'nullable|string|max:150',
            'umk'      => 'nullable|numeric|min:0',
        ]);

        $fid = $request->fidunit ?? null;

        if (!empty($fid)) {
            $id = $fid;
            if (!is_numeric($fid)) {
                try {
                    $id = Crypt::decryptString($fid);
                } catch (DecryptException $e) {}
            }
            $unit = UnitKerja::find($id);
            if (!$unit) {
                return response()->json(['success' => false, 'message' => 'Unit kerja tidak ditemukan'], 404);
            }
        } else {
            $unit = new UnitKerja();
        }

        $unit->namaunit = $request->namaunit;
        $unit->lokasi   = $request->lokasi;
        $unit->umk      = $request->umk;
        $unit->save();

        return response()->json([
            'success' => true,
            'message' => 'Data Unit Kerja berhasil disimpan',
            'data'    => $unit
        ]);
    }

    public function getdata(Request $request)
    {
        $unit = UnitKerja::select(['id', 'namaunit', 'lokasi', 'umk']);
        return DataTables::of($unit)
            ->addIndexColumn()
            ->addColumn('umk', fn($row) => number_format($row->umk, 0, ',', '.'))
            ->addColumn('aksi', function ($row) {
                $enc_id = Crypt::encryptString($row->id);
                return '
                    <span class="badge bg-info btn-edit" data-id="'.$enc_id.'" style="cursor:pointer;"><i class="bi bi-pencil-square"></i></span>
                    <span class="badge bg-danger btn-hapus" data-id="'.$enc_id.'" style="cursor:pointer;"><i class="fa-solid fa-trash-can"></i></span>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show($id)
    {
        if (!is_numeric($id)) {
            try {
                $id = Crypt::decryptString($id);
            } catch (DecryptException $e) {}
        }

        $unit = UnitKerja::find($id);
        if (!$unit) {
            return response()->json(['success' => false, 'message' => 'Unit kerja tidak ditemukan'], 404);
        }

        return response()->json($unit);
    }

    public function destroy($id)
    {
        if (!is_numeric($id)) {
            try {
                $id = Crypt::decryptString($id);
            } catch (DecryptException $e) {}
        }

        $unit = UnitKerja::find($id);
        if (!$unit) {
            return response()->json(['success' => false, 'message' => 'Unit kerja tidak ditemukan'], 404);
        }

        $unit->delete();
        return response()->json(['success' => true, 'message' => 'Unit kerja dihapus']);
    }
}
