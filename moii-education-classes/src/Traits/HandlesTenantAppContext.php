<?php

namespace Moii\EducationClasses\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait HandlesTenantAppContext
{
    protected function getTenantUuid(Request $request): ?string
    {
        return $this->resolveUuid($this->getRawTenantValue($request), 'tenants');
    }

    protected function getAppUuid(Request $request): ?string
    {
        return $this->resolveUuid($this->getRawAppValue($request), 'apps');
    }

    protected function getRawTenantValue(Request $request): ?string
    {
        return $request->header('X-Tenant-ID')
            ?? $request->input('tenant_id')
            ?? $request->input('tenant_uuid');
    }

    protected function getRawAppValue(Request $request): ?string
    {
        return $request->header('X-App-ID')
            ?? $request->input('app_id')
            ?? $request->input('app_uuid');
    }

    protected function requireContext(Request $request): ?JsonResponse
    {
        $missing = [];

        if (empty($this->getRawTenantValue($request))) {
            $missing[] = 'tenant_id';
        }

        if (empty($this->getRawAppValue($request))) {
            $missing[] = 'app_id';
        }

        if ($missing === []) {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => 'Tenant ID and App ID are required',
            'missing' => $missing,
        ], 400);
    }

    private function resolveUuid(?string $value, string $table): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            return $value;
        }

        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return null;
        }

        return DB::table($table)->where('id', (int) $value)->value('uuid');
    }
}
