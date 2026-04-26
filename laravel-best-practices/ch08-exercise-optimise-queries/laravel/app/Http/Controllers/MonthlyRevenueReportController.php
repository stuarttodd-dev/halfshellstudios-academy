<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MonthlyRevenueReportController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $key = 'reports:monthly_paid_revenue:6m:' . now()->format('Y-m-d-H');

        $rows = Cache::remember($key, 60, function () {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'sqlite') {
                return DB::table('orders')
                    ->selectRaw("strftime('%Y-%m', created_at) as month, SUM(total) as revenue")
                    ->where('status', 'paid')
                    ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
                    ->groupByRaw("strftime('%Y-%m', created_at)")
                    ->orderBy('month')
                    ->get();
            }

            return DB::table('orders')
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m-01") as month, SUM(total) as revenue')
                ->where('status', 'paid')
                ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
                ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m-01")')
                ->orderBy('month')
                ->get();
        });

        return response()->json(['data' => $rows]);
    }
}
