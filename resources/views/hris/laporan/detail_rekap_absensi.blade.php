<x-app-layout>

    <x-slot name="pagetitle">
        Detail Laporan Kehadiran
    </x-slot>

    {{-- HEADER HALAMAN (TIDAK IKUT DICETAK) --}}
    <div class="app-content-header no-print">
        <div class="container-fluid">

            <div class="row align-items-center">
                <div class="col-md-8">

                    <h3 class="mb-0">
                        <i class="bi bi-calendar-check"></i>
                        Detail Kehadiran Karyawan
                    </h3>

                    <small class="text-muted">
                        Periode
                        {{ $awal->translatedFormat('d M Y') }}
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

                    <button type="button"
                            onclick="window.print()"
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

            {{-- ======================================================
                 AREA KHUSUS YANG AKAN DICETAK
            ======================================================= --}}
            <div id="printArea">

                {{-- HEADER KHUSUS CETAK --}}
                <div class="print-header d-none">

                    <h3 class="mb-1">
                        LAPORAN DETAIL KEHADIRAN KARYAWAN
                    </h3>

                    <div>
                        Periode
                        {{ $awal->translatedFormat('d F Y') }}
                        s/d
                        {{ $akhir->translatedFormat('d F Y') }}
                    </div>

                    <hr>

                </div>


                <div class="card card-info card-outline">

                    <div class="card-body">

                        {{-- ===============================
                             INFORMASI KARYAWAN
                        ================================ --}}
                        <div class="row mb-4">

                            <div class="col-md-6">

                                <table class="table table-borderless table-sm mb-0 info-table">

                                    <tr>
                                        <td width="130">
                                            <strong>Nama Staff</strong>
                                        </td>
                                        <td width="10">:</td>
                                        <td>{{ $pegawai->name }}</td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <strong>Finger ID</strong>
                                        </td>
                                        <td>:</td>
                                        <td>
                                            {{ $pegawai->finger_id ?? '-' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <strong>NIP / NIK</strong>
                                        </td>
                                        <td>:</td>
                                        <td>
                                            {{ $pegawai->nik }}
                                        </td>
                                    </tr>

                                </table>

                            </div>


                            <div class="col-md-6">

                                <table class="table table-borderless table-sm mb-0 info-table">

                                    <tr>
                                        <td width="100">
                                            <strong>Jabatan</strong>
                                        </td>
                                        <td width="10">:</td>
                                        <td>
                                            {{ $pegawai->jabatan ?? '-' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <strong>Unit</strong>
                                        </td>
                                        <td>:</td>
                                        <td>
                                            {{ optional($pegawai->unitkerja)->namaunit ?? '-' }}
                                        </td>
                                    </tr>

                                </table>

                            </div>

                        </div>


                        {{-- ===============================
                             TABEL ABSENSI
                        ================================ --}}
                        <div class="table-responsive">

                            <table class="table table-bordered table-sm table-striped align-middle attendance-table">

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

                                    @forelse($data as $row)

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

                                    @empty

                                        <tr>
                                            <td colspan="10"
                                                class="text-center text-muted">
                                                Tidak ada data absensi.
                                            </td>
                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>


                        {{-- ===============================
                             RINGKASAN
                        ================================ --}}
                        <div class="mt-3 pt-2 border-top">

                            <strong>Total Hadir Kerja :</strong>
                            {{ $totalHadir }} Hari

                        </div>

                    </div>

                </div>

            </div>
            {{-- END PRINT AREA --}}

        </div>
    </div>


    <x-slot name="jscustom">

        <style>

            /* ===============================
               TAMPILAN NORMAL
            ================================ */

            .attendance-table th,
            .attendance-table td {
                vertical-align: middle;
            }

            .info-table td {
                padding-top: 2px;
                padding-bottom: 2px;
            }


            /* ===============================
               KHUSUS PRINT
            ================================ */

            @media print {

                @page {
                    size: A4 landscape;
                    margin: 10mm;
                }

                /*
                 * Sembunyikan seluruh isi halaman
                 */
                body * {
                    visibility: hidden !important;
                }

                /*
                 * Tampilkan hanya area laporan
                 */
                #printArea,
                #printArea * {
                    visibility: visible !important;
                }

                /*
                 * Letakkan laporan di awal halaman cetak
                 */
                #printArea {
                    position: absolute !important;
                    left: 0 !important;
                    top: 0 !important;
                    width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                /*
                 * Header khusus cetak
                 */
                .print-header {
                    display: block !important;
                    text-align: center;
                    margin-bottom: 15px;
                }

                /*
                 * Hilangkan tampilan Bootstrap card
                 */
                #printArea .card {
                    border: none !important;
                    box-shadow: none !important;
                }

                #printArea .card-body {
                    padding: 0 !important;
                }

                /*
                 * Hilangkan efek striped jika tidak diinginkan,
                 * tetapi warna hari Minggu tetap terlihat
                 */
                .table-striped > tbody > tr:nth-of-type(odd) > * {
                    --bs-table-accent-bg: transparent !important;
                }

                /*
                 * Tabel memenuhi lebar kertas
                 */
                .attendance-table {
                    width: 100% !important;
                    font-size: 10px !important;
                }

                .attendance-table th,
                .attendance-table td {
                    padding: 4px !important;
                }

                /*
                 * Hindari baris tabel terpotong
                 */
                .attendance-table tr {
                    page-break-inside: avoid !important;
                }

                /*
                 * Header tabel otomatis muncul lagi
                 * jika laporan lebih dari satu halaman
                 */
                .attendance-table thead {
                    display: table-header-group;
                }

                /*
                 * Pastikan warna dan background ikut tercetak
                 */
                * {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

            }

        </style>

    </x-slot>

</x-app-layout>