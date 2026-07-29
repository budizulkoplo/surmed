<x-app-layout>
    <x-slot name="pagetitle">Monitoring Absensi Ahad Pagi</x-slot>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0"><i class="bi bi-sunrise"></i> Monitoring Absensi Ahad Pagi</h3>
                </div>
                <div class="col-sm-6 text-end">
                    <div class="d-flex justify-content-end gap-2">
                        <input type="date" id="tanggal" class="form-control form-control-sm w-auto" value="{{ $tanggal }}">
                        <button class="btn btn-primary btn-sm" onclick="reloadTable()">
                            <i class="bi bi-search"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-warning card-outline">
                <div class="card-body">
                    <table id="tblAhadPagi" class="table table-sm table-bordered table-striped w-100" style="font-size: small;">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>NIK / Nama</th>
                                <th>Jam Hadir</th>
                                <th>Foto</th>
                                <th>Lokasi</th>
                                <th>Kajian</th>
                                <th>Pemateri</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="jscustom">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script> window.JSZip = JSZip; </script>

        <script>
            let table;

            function reloadTable() {
                if (table) table.ajax.reload();
            }

            $(document).ready(function () {
                table = $('#tblAhadPagi').DataTable({
                    processing: true,
                    pageLength: 50,
                    lengthMenu: [25, 50, 100, 200],
                    ajax: {
                        url: "{{ route('hris.laporan.monitoring_ahad_pagi.data') }}",
                        data: function(d) {
                            d.tanggal = $('#tanggal').val();
                        },
                        dataSrc: "data"
                    },
                    columns: [
                        { data: null, render: (data, type, row, meta) => meta.row + 1 },
                        { data: 'tgl_presensi', className: 'text-center', render: d => d || '-' },
                        { data: null, render: d => `
                            <small class="text-muted">${d.nik || '-'}<br></small>
                            <strong>${d.nama_lengkap || '-'}</strong>
                            <div class="text-muted">${d.jabatan || '-'}</div>
                        ` },
                        { data: 'jam_in', render: d => d || '-', className: 'text-center' },
                        { data: 'foto_url', className: 'text-center', render: d => d ? `
                            <a href="${d}" target="_blank">
                                <img src="${d}" class="img-thumbnail" width="50">
                            </a>
                        ` : '-' },
                        { data: 'lokasi', className: 'text-center', render: d => d ? `
                            <a href="https://maps.google.com/?q=${d}" target="_blank" class="btn btn-outline-info btn-sm">
                                <i class="bi bi-geo-alt"></i> Lihat
                            </a>
                        ` : '-' },
                        { data: 'judul', render: d => d || '-' },
                        { data: 'pemateri', render: d => d || '-' },
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
                        }
                    ],
                    ordering: false
                });
            });
        </script>
    </x-slot>
</x-app-layout>
