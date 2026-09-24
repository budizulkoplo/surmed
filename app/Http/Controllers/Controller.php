<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function attendancePeriod(int $month, int $year): array
    {
        $akhir = Carbon::create($year, $month, 25)->endOfDay();
        $awal = $akhir->copy()->subMonthNoOverflow()->day(26)->startOfDay();

        return [$awal, $akhir];
    }

    protected function currentAttendancePeriod(): array
    {
        return $this->attendancePeriod((int) now()->format('m'), (int) now()->format('Y'));
    }
}
