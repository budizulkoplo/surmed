<x-app-layout>
    <x-slot name="pagetitle">Laporan Rekap Absensi</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0"><i class="bi bi-clipboard-data"></i> Laporan Rekap Absensi</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                        <label for="tgl_awal" class="mb-0 small text-muted">Tgl Awal</label>
                        <input type="date" id="tgl_awal" class="form-control form-control-sm w-auto"
                               value="{{ $tglAwal }}" onchange="reloadTable()">

                        <label for="tgl_akhir" class="mb-0 small text-muted">Tgl Akhir</label>
                        <input type="date" id="tgl_akhir" class="form-control form-control-sm w-auto"
                               value="{{ $tglAkhir }}" onchange="reloadTable()">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-body">
                    <table id="tblRekap" class="table table-sm table-bordered table-striped" style="width: 100%; font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Hari Kerja</th>
                                <th>Hari Presensi</th>
                                <th>Absen Lengkap</th>
                                <th>Tidak Lengkap</th>
                                <th>Total Jam Kerja</th>
                                <th>Total Lembur</th>
                                <th>Terlambat (Jam:Menit)</th>
                                <th>Ahad Pagi</th>
                                <th>Cuti</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== CUSTOM SCRIPT ========== --}}
    <x-slot name="jscustom">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script> window.JSZip = JSZip; </script>

        <script>
            let table;

            function reloadTable() {
                if (table) table.ajax.reload();
            }

            $(document).ready(function () {
                table = $('#tblRekap').DataTable({
                    processing: true,
                    pageLength: 100, // ✅ Default tampil 100 data
                    lengthMenu: [25, 50, 100, 200, 500],
                    ajax: {
                        url: "{{ route('hris.laporan.rekap_absensi.data') }}",
                        data: function(d) {
                            d.tgl_awal = $('#tgl_awal').val();
                            d.tgl_akhir = $('#tgl_akhir').val();
                        },
                        dataSrc: "data"
                    },
                    columns: [
                        { data: null, render: (data, type, row, meta) => meta.row + 1 },
                        {
                            data: null,
                            render: (data) => {

                                const tglAwal = $('#tgl_awal').val();
                                const tglAkhir = $('#tgl_akhir').val();

                                const url = "{{ url('hris/laporan/rekap-absensi') }}/" 
                                            + data.nik 
                                            + "/detail?tgl_awal=" + tglAwal 
                                            + "&tgl_akhir=" + tglAkhir;

                                return `
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <strong>${data.nama}</strong>

                                        <a href="${url}" 
                                        class="btn btn-primary btn-sm"
                                        title="Lihat Detail Absensi">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                    </div>
                                `;
                            }
                        },
                        { data: 'hari_kerja', className: 'text-center' },
                        { data: 'jml_absensi', className: 'text-center' },
                        { data: 'absen_lengkap', className: 'text-center' },
                        { data: 'absen_tidak_lengkap', className: 'text-center' },
                        { data: 'total_jam_kerja', className: 'text-center' },
                        { data: 'lembur', className: 'text-center' },
                        { data: 'terlambat', className: 'text-center' },
                        { data: 'ahad_pagi', className: 'text-center' },
                        { data: 'cuti', className: 'text-center' },
                        { data: 'total', className: 'text-center fw-bold' },
                    ],
                    dom:
                        "<'row mb-2'<'col-md-6 d-flex align-items-center'B><'col-md-6 d-flex justify-content-end'f>>" +
                        "<'row mb-2'<'col-md-6'l><'col-md-6 text-end'i>>" +
                        "<'row'<'col-12'tr>>" +
                        "<'row mt-2'<'col-md-6'i><'col-md-6 d-flex justify-content-end'p>>",
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '<i class="bi bi-file-earmark-excel"></i> Export Excel',
                            className: 'btn btn-success btn-sm',
                            exportOptions: { columns: ':visible' }
                        },
                        {
                            text: '<i class="bi bi-save"></i> Export to Payroll',
                            className: 'btn btn-warning btn-sm',
                            action: function () {
                                let tglAwal = $('#tgl_awal').val();
                                let tglAkhir = $('#tgl_akhir').val();

                                Swal.fire({
                                    title: 'Export ke Payroll?',
                                    text: "Data periode " + tglAwal + " s/d " + tglAkhir + " akan dimasukkan ke tabel payroll.",
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonText: 'Ya, Export',
                                    cancelButtonText: 'Batal'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $.post("{{ route('hris.laporan.rekap_absensi.export_payroll') }}", {
                                            _token: '{{ csrf_token() }}',
                                            tgl_awal: tglAwal,
                                            tgl_akhir: tglAkhir
                                        })
                                        .done((res) => {
                                            Swal.fire('Berhasil!', res.message, 'success');
                                        })
                                        .fail((xhr) => {
                                            Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                                        });
                                    }
                                });
                            }
                        }
                    ],
                    ordering: false
                });
            });
        </script>

    </x-slot>
</x-app-layout>
