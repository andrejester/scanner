<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VisitorStatistikController extends Controller
{
    //
    public function monthly($year)
    {
        $data = Cache::remember(
            "visitors:unique:$year",
            now()->addMinutes(30),
            function () use ($year) {

                $rows = Visitor::selectRaw('
                    MONTH(created_at) as month,
                    COUNT(DISTINCT ip_address) as total
                ')
                    ->whereYear('created_at', $year)
                    ->groupBy('month')
                    ->pluck('total', 'month')
                    ->toArray();

                $result = array_fill(0, 12, 0);
                foreach ($rows as $month => $total) {
                    $result[$month - 1] = (int) $total;
                }

                return $result;
            }
        );

        return response()->json([
            'data' => $data
        ]);
    }


    public function monthlyActivity($year)
    {
        $activity = Cache::remember(
            "visitors:activity:$year",
            now()->addMinutes(30),
            function () use ($year) {

                $rows = Visitor::selectRaw('
                    MONTH(created_at) as month,
                    COUNT(*) as total
                ')
                    ->whereYear('created_at', $year)
                    ->groupBy('month')
                    ->pluck('total', 'month')
                    ->toArray();

                $result = array_fill(0, 12, 0);
                foreach ($rows as $month => $total) {
                    $result[$month - 1] = (int) $total;
                }

                return $result;
            }
        );

        return response()->json([
            'activity' => $activity
        ]);
    }
}
