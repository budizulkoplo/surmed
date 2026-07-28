<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class KelompokJam extends Model
{
    use HasFactory;

    public const LATE_TOLERANCE_SECONDS = 299; // 4 menit 59 detik

    protected $table = 'kelompokjam';
    protected $primaryKey = 'id';
    public $timestamps = false; // jika tidak ada created_at/updated_at

    protected $fillable = [
        'shift',
        'jammasuk',
        'jampulang',
        'jammasuk_sabtu',
        'jampulang_sabtu',
        'toleransi_menit',
    ];

    public function jamMasukForDate($date): ?string
    {
        return self::timeForDate($this, 'jammasuk', $date);
    }

    public function jamPulangForDate($date): ?string
    {
        return self::timeForDate($this, 'jampulang', $date);
    }

    public static function timeForDate(object $shift, string $column, $date): ?string
    {
        $saturdayColumn = $column . '_sabtu';

        if (Carbon::parse($date)->isSaturday() && !empty($shift->{$saturdayColumn})) {
            return $shift->{$saturdayColumn};
        }

        return $shift->{$column} ?? null;
    }

    public static function isLateBySeconds(int $seconds): bool
    {
        return $seconds > self::LATE_TOLERANCE_SECONDS;
    }
}
