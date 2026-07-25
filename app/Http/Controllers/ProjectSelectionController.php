<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;

class ProjectSelectionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userRole = $user->getRoleNames()->first(); // contoh: 'admin', 'hrd', 'superadmin', dst.

        // 🔹 Subquery: ambil id terkecil per module sesuai role
        $sub = Menu::whereNotNull('module')
            ->where('module', '!=', 'project')
            ->where(function ($q) use ($userRole) {
                $q->where('role', 'like', "%;$userRole;%")
                ->orWhere('role', 'like', "$userRole;%")
                ->orWhere('role', 'like', "%;$userRole")
                ->orWhere('role', '=', $userRole);
            })
            ->selectRaw('MIN(id) as min_id')
            ->groupBy('module');

        // 🔹 Ambil data module + icon dari id terkecil
        $modules = Menu::joinSub($sub, 'sub', function ($join) {
                $join->on('menus.id', '=', 'sub.min_id');
            })
            ->orderBy('menus.module')
            ->get(['menus.module', 'menus.icon']);

        // 🔹 Tidak ambil projects lagi
        $projects = collect();

        return view('projects.choose', compact('projects', 'modules'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // 🔸 Sekarang hanya perlu handle module saja
        $request->validate([
            'module' => 'required|string',
        ]);

        $module = $request->module;

        session([
            'active_project_id' => $module,       // biar kompatibel dengan session lama
            'active_project_name' => ucfirst($module),
            'active_project_module' => $module,
        ]);

        // 🔹 Redirect dinamis berdasarkan module
        if ($module === 'mobile') {
            return redirect()->route('mobile.home')
                ->with('success', "Module '$module' berhasil dipilih!");
        }

        return redirect()->route('dashboard')
            ->with('success', "Module '$module' berhasil dipilih!");
    }
}
