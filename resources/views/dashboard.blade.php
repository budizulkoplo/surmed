<x-app-layout>
    <x-slot name="pagetitle">Dashboard</x-slot>

    <div class="app-content">
        <div class="container-fluid my-4">
            @php
                $periode = \Carbon\Carbon::create(
                    $ringkasanAbsensi['tahun'] ?? now()->year, 
                    $ringkasanAbsensi['bulan'] ?? now()->month, 
                    1
                )->translatedFormat('F Y');
            @endphp

            <!-- Header dengan Periode -->
            <div class="dashboard-header mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h1 class="dashboard-title">
                            <i class="nav-icon bi bi-people"></i> Dashboard HRIS
                        </h1>
                        <p class="text-muted mb-0">Periode: {{ $periode }}</p>
                    </div>
                    
                </div>
            </div>

            <!-- Stats Cards Grid -->
            <div class="row g-4 mb-4">
                <!-- Pegawai Aktif -->
                <div class="col-xl-4 col-md-6">
                    <div class="stat-card stat-primary">
                        <div class="stat-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-info">
                            <h3>{{ $ringkasanAbsensi['pegawai_aktif'] }}</h3>
                            <p>Pegawai Aktif</p>
                        </div>
                    </div>
                </div>

                <!-- Persentase Kehadiran -->
                <div class="col-xl-4 col-md-6">
                    <div class="stat-card stat-success">
                        <div class="stat-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3>{{ $ringkasanAbsensi['persentase_kehadiran'] }}%</h3>
                            <p>Rate Kehadiran</p>
                        </div>
                    </div>
                </div>

                <!-- Total Izin -->
                <div class="col-xl-4 col-md-6">
                    <div class="stat-card stat-warning">
                        <div class="stat-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="stat-info">
                            <h3>{{ $ringkasanAbsensi['total_izin'] ?? '0' }}</h3>
                            <p>Total Izin</p>
                        </div>
                    </div>
                </div>

                
            </div>

            <!-- Quick Summary -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card quick-summary-card">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4 border-end">
                                    <div class="summary-item">
                                        <div class="summary-value">{{ $ringkasanAbsensi['pegawai_aktif'] }}</div>
                                        <div class="summary-label">Total Pegawai</div>
                                    </div>
                                </div>
                                <div class="col-md-4 border-end">
                                    <div class="summary-item">
                                        <div class="summary-value">{{ $ringkasanAbsensi['persentase_kehadiran'] }}%</div>
                                        <div class="summary-label">Kehadiran</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="summary-item">
                                        <div class="summary-value">{{ $ringkasanAbsensi['total_izin'] ?? '0' }}</div>
                                        <div class="summary-label">Izin</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --success: #10b981;
            --success-light: #ecfdf5;
            --warning: #f59e0b;
            --warning-light: #fffbeb;
            --info: #06b6d4;
            --info-light: #ecfeff;
            --dark: #374151;
            --light: #f8fafc;
            --border: #e5e7eb;
        }

        /* Reset dan base styles */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #374151;
            background-color: #f8fafc;
        }

        .dashboard-header {
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .dashboard-title {
            color: var(--dark);
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 0.25rem;
        }

        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .stat-primary { border-left: 4px solid var(--primary); }
        .stat-success { border-left: 4px solid var(--success); }
        .stat-warning { border-left: 4px solid var(--warning); }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.25rem;
            color: white;
        }

        .stat-primary .stat-icon { background: var(--primary); }
        .stat-success .stat-icon { background: var(--success); }
        .stat-warning .stat-icon { background: var(--warning); }
        .stat-info .stat-icon { background: var(--info); }

        .stat-info h3 {
            margin: 0;
            font-weight: 700;
            color: var(--dark);
            font-size: 1.5rem;
            line-height: 1.2;
        }

        .stat-info p {
            margin: 0.25rem 0 0;
            color: #6b7280;
            font-size: 0.875rem;
            line-height: 1.4;
        }

        /* Service Info Card */
        .service-info-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            height: 100%;
        }

        .service-info-card .card-header {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 1.25rem 1.5rem;
        }

        .service-info-card .card-title {
            color: var(--dark);
            font-weight: 600;
            font-size: 1.1rem;
            margin: 0;
            line-height: 1.4;
        }

        .service-details {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.4;
        }

        .detail-value {
            color: var(--dark);
            font-weight: 600;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        /* Unit Distribution Card */
        .unit-distribution-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            height: 100%;
        }

        .unit-distribution-card .card-header {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 1.25rem 1.5rem;
        }

        .unit-list {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .unit-item {
            padding: 1rem;
            background: var(--light);
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .unit-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .unit-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .unit-count {
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            line-height: 1.4;
        }

        .unit-progress {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }

        .unit-progress .progress {
            flex: 1;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .unit-progress .progress-bar {
            background: var(--primary);
            height: 100%;
            transition: width 0.3s ease;
        }

        .progress-percentage {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--dark);
            min-width: 45px;
            line-height: 1.4;
        }

        .unit-location {
            font-size: 0.8rem;
            line-height: 1.4;
        }

        /* Quick Summary Card */
        .quick-summary-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .quick-summary-card .card-body {
            padding: 1.5rem;
        }

        .summary-item {
            padding: 0.5rem;
        }

        .summary-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: white !important;
            line-height: 1.2;
        }

        .summary-label {
            font-size: 0.875rem;
            opacity: 0.9;
            color: white !important;
            line-height: 1.4;
        }

        /* Pastikan semua teks di quick summary card berwarna putih */
        .quick-summary-card .summary-value,
        .quick-summary-card .summary-label {
            color: white !important;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-title {
                font-size: 1.5rem;
            }
            
            .stat-card {
                padding: 1.25rem;
            }
            
            .stat-icon {
                width: 45px;
                height: 45px;
                font-size: 1.1rem;
            }
            
            .stat-info h3 {
                font-size: 1.3rem;
            }
            
            .unit-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .unit-progress {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .progress-percentage {
                align-self: flex-end;
            }
            
            .quick-summary-card .row .col-md-3 {
                border-right: none !important;
                border-bottom: 1px solid rgba(255,255,255,0.2);
                padding: 1rem 0;
            }
            
            .quick-summary-card .row .col-md-3:last-child {
                border-bottom: none;
            }

            .summary-value {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .stat-card {
                flex-direction: column;
                text-align: center;
            }
            
            .stat-icon {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .service-info-card .card-body,
            .unit-distribution-card .card-body {
                padding: 1rem;
            }
            
            .detail-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
        }

        /* Tambahan untuk memastikan konsistensi font */
        .card {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .table {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
</x-app-layout>
