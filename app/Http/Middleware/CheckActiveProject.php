<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckActiveProject
{
    public function handle(Request $request, Closure $next)
    {
        // HRIS SurMed tidak lagi memakai halaman pilih project.
        if (!session()->has('active_project_id') && $request->is('login')) {
            return $next($request);
        }

        if (session('login_area') === 'mobile' && (
            $request->is('dashboard')
            || $request->is('hris*')
            || $request->is('master*')
            || $request->is('pegawai*')
            || $request->is('laporan*')
        )) {
            return redirect()->route('mobile.home');
        }

        if (!session()->has('active_project_id') && ($request->is('dashboard') || $request->is('hris*'))) {
            session()->put([
                'active_project_id' => 'hris',
                'active_project_name' => 'HRIS Klinik Surya Medika',
                'active_project_module' => 'hris',
            ]);
        }

        // Boleh akses choose-project, logout & mobile tanpa project aktif.
        if (!session()->has('active_project_id')
            && !$request->is('choose-project*')
            && !$request->is('logout')
            && !$request->is('mobile*'))
        {
            return redirect()->route('mobile.home');
        }

        return $next($request);
    }
}
