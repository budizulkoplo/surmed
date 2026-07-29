<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('menus')) {
            return;
        }

        $hasLink = Schema::hasColumn('menus', 'link');
        $hasParent = Schema::hasColumn('menus', 'parent_id');
        $hasRole = Schema::hasColumn('menus', 'role');
        $hasSeq = Schema::hasColumn('menus', 'seq');
        $hasIcon = Schema::hasColumn('menus', 'icon');
        $hasModule = Schema::hasColumn('menus', 'module');
        $hasTimestamps = Schema::hasColumn('menus', 'created_at') && Schema::hasColumn('menus', 'updated_at');
        $hasDeletedAt = Schema::hasColumn('menus', 'deleted_at');

        $referenceMenu = null;

        if ($hasLink) {
            $referenceQuery = DB::table('menus')
                ->whereIn('link', [
                    'hris.laporan.monitoring_presensi',
                    'hris.laporan.rekap_absensi',
                    'hris.laporan.payroll',
                ]);

            if ($hasSeq) {
                $referenceQuery->orderByDesc('seq');
            }

            $referenceMenu = $referenceQuery->first();
        }

        $payload = [
            'name' => 'Monitoring Ahad Pagi',
        ];

        if ($hasLink) {
            $payload['link'] = 'hris.laporan.monitoring_ahad_pagi';
        }

        if ($hasParent) {
            $payload['parent_id'] = $referenceMenu->parent_id
                ?? DB::table('menus')->where('name', 'Laporan HRIS')->value('id')
                ?? $this->findHrisReportParentId($hasModule)
                ?? null;
        }

        if ($hasRole) {
            $payload['role'] = $referenceMenu->role ?? ';superadmin;admin;';
        }

        if ($hasSeq) {
            $payload['seq'] = ((int) ($referenceMenu->seq ?? 60)) + 1;
        }

        if ($hasIcon) {
            $payload['icon'] = 'bi bi-sunrise';
        }

        if ($hasModule) {
            $payload['module'] = $referenceMenu->module ?? 'hris';
        }

        if ($hasTimestamps) {
            $payload['created_at'] = now();
            $payload['updated_at'] = now();
        }

        if ($hasDeletedAt) {
            $payload['deleted_at'] = null;
        }

        $existing = $hasLink
            ? DB::table('menus')->where('link', 'hris.laporan.monitoring_ahad_pagi')->first()
            : DB::table('menus')->where('name', 'Monitoring Ahad Pagi')->first();

        if ($existing) {
            unset($payload['created_at']);

            if ($hasTimestamps) {
                $payload['updated_at'] = now();
            }

            DB::table('menus')->where('id', $existing->id)->update($payload);
            return;
        }

        DB::table('menus')->insert($payload);
    }

    public function down(): void
    {
        if (!Schema::hasTable('menus')) {
            return;
        }

        if (Schema::hasColumn('menus', 'link')) {
            DB::table('menus')->where('link', 'hris.laporan.monitoring_ahad_pagi')->delete();
            return;
        }

        DB::table('menus')->where('name', 'Monitoring Ahad Pagi')->delete();
    }

    private function findHrisReportParentId(bool $hasModule): ?int
    {
        $query = DB::table('menus')->where('name', 'Laporan');

        if ($hasModule) {
            $query->where('module', 'hris');
        }

        $id = $query->value('id');

        return $id ? (int) $id : null;
    }
};
