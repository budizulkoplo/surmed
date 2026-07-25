<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $request->session()->put('login_area', 'admin');

        if (Auth::check()) {
            if (!$request->user()->hasRole('superadmin')) {
                return redirect()->route('mobile.home');
            }

            return redirect()->to($this->redirectTo('admin'));
        }

        $setting = Setting::first();

        return view('auth.login', [
            'setting' => $setting,
            'area' => 'admin',
            'formAction' => route('login.store'),
            'title' => 'LOGIN HRIS',
        ]);
    }

    public function mobileCreate(Request $request): View|RedirectResponse
    {
        $request->session()->put('login_area', 'mobile');

        if (Auth::check()) {
            return app(\App\Http\Controllers\Mobile\DashboardController::class)->index();
        }

        $setting = Setting::first();

        return view('auth.login', [
            'setting' => $setting,
            'area' => 'mobile',
            'formAction' => route('mobile.login.store'),
            'title' => 'Login',
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $area = (string) $request->input('area', 'mobile');
        $request->session()->put('login_area', $area === 'admin' ? 'admin' : 'mobile');

        if ($area === 'admin') {
            if (!$request->user()->hasRole('superadmin')) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->withErrors(['username' => 'Akses Login HRIS hanya untuk superadmin.']);
            }

            $request->session()->put([
                'active_project_id' => 'hris',
                'active_project_name' => 'HRIS Klinik Surya Medika',
                'active_project_module' => 'hris',
            ]);
        }

        return redirect()->to($this->redirectTo($area));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function redirectTo(string $area = 'mobile'): string
    {
        if ($area === 'admin') {
            return route('dashboard');
        }

        return route('mobile.home');
    }
}
