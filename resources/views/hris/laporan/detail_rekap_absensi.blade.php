<x-app-layout>

    <x-slot name="pagetitle">
        Detail Laporan Kehadiran
    </x-slot>

    <div class="app-content-header">
        <div class="container-fluid">

            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-0">
                        <i class="bi bi-calendar-check"></i>
                        Detail Kehadiran Karyawan
                    </h3>

                    <small class="text-muted">
                        Periode {{ $awal->translatedFormat('d M Y') }}
                        s/d
                        {{ $akhir->translatedFormat('d M Y') }}
                    </small>
                </div>

                <div class="col-md-4 text-end">
                    <a href="{{ route('hris.laporan.rekap_absensi') }}"
                       class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Kembali
                    </a>

                    <button onclick="window.print()"
                            class="btn btn-primary">
                        <i class="bi bi-printer"></i>
                        Cetak
                    </button>
                </div>
            </div>

        </div>
    </div>


    <div class="app-content">
        <div class="container-fluid">

            <div class="card card-info card-outline">

                <div class="card-body">

                    {{-- INFORMASI KARYAWAN --}}
                    <div class="row mb-4">

                        <div class="col-md-6">

                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td width="130"><strong>Nama Staff</strong></td>
                                    <td width="10">:</td>
                                    <td>{{ $pegawai->name }}</td>
                                </tr>

                                <tr>
                                    <td><strong>Finger ID</strong></td>
                                    <td>:</td>
                                    <td>{{ $pegawai->finger_id ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><strong>NIP / NIK</strong></td>
                                    <td>:</td>
                                    <td>{{ $pegawai->nik }}</td>
                                </tr>

                            </table>

                        </div>


                        <div class="col-md-6">

                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td width="100"><strong>Jabatan</strong></td>
                                    <td width="10">:</td>
                                    <td>{{ $pegawai->jabatan ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><strong>Unit</strong></td>
                                    <td>:</td>
                                    <td>{{ optional($pegawai->unitkerja)->namaunit ?? '-' }}</td>
                                </tr>
                            </table>

                        </div>

                    </div>


                    {{-- TABEL ABSENSI --}}
                    <div class="table-responsive">

                        <table class="table table-bordered table-sm table-striped align-middle">

                            <thead class="table-light text-center">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Hari</th>
                                    <th>Tipe</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Keluar</th>
                                    <th>Total Jam<br>Kerja</th>
                                    <th>Total<br>Kurang Jam</th>
                                    <th>Datang<br>Telat</th>
                                    <th>Pulang<br>Cepat</th>
                                    <th>Tidak Absen<br>(Hari)</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($data as $row)

                                    <tr class="{{ strtolower($row['hari']) === 'minggu' ? 'table-danger' : '' }}">

                                        <td class="text-center">
                                            {{ \Carbon\Carbon::parse($row['tanggal'])->format('d M Y') }}
                                        </td>

                                        <td class="text-center">
                                            {{ $row['hari'] }}
                                        </td>

                                        <td class="text-center">
                                            {{ $row['tipe'] }}
                                        </td>

                                        <td class="text-center">
                                            {{ $row['jam_masuk'] }}
                                        </td>

                                        <td class="text-center">
                                            {{ $row['jam_keluar'] }}
                                        </td>

                                        <td class="text-center">
                                            {{ $row['total_jam_kerja'] }}
                                        </td>

                                        <td class="text-center">
                                            {{ $row['kurang_jam'] }}
                                        </td>

                                        <td class="text-center">
                                            {{ $row['datang_telat'] }}
                                        </td>

                                        <td class="text-center">
                                            {{ $row['pulang_cepat'] }}
                                        </td>

                                        <td class="text-center">
                                            {{ $row['tidak_absen'] }}
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>


                    <div class="mt-3">
                        <strong>Total Hadir Kerja :</strong>
                        {{ $totalHadir }} Hari
                    </div>

                </div>

            </div>

        </div>
    </div>


    <x-slot name="jscustom">
        <style>
            @media print {

                .app-content-header,
                .main-header,
                .main-sidebar,
                .btn {
                    display: none !important;
                }

                .app-content {
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .card {
                    border: none !important;
                    box-shadow: none !important;
                }

            }
        </style>
    </x-slot>

</x-app-layout>