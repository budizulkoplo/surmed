<x-app-layout>
    <x-slot name="pagetitle">Setting Perusahaan</x-slot>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card card-info card-outline">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('setting.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama Perusahaan</label>
                            <input type="text" name="nama_perusahaan" class="form-control"
                                   value="{{ old('nama_perusahaan', $setting->nama_perusahaan ?? '') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $setting->alamat ?? '') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="telepon" class="form-control"
                                   value="{{ old('telepon', $setting->telepon ?? '') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lokasi Klinik</label>
                            <input type="text" name="lokasi_klinik" class="form-control"
                                   value="{{ old('lokasi_klinik', $setting->lokasi_klinik ?? '') }}"
                                   placeholder="-6.966667,110.416664">
                            <small class="text-muted">Format latitude,longitude untuk radius absensi Klinik Surya Medika.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Radius Absensi (meter)</label>
                            <input type="number" name="radius_absensi" class="form-control"
                                   value="{{ old('radius_absensi', $setting->radius_absensi ?? 100) }}"
                                   min="1">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Logo</label>
                            <input type="file" name="logo" class="form-control">
                            @if(!empty($setting->path_logo))
                                <img src="{{ asset($setting->path_logo) }}" 
                                    alt="Logo" class="mt-2" height="80">
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
