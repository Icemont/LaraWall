<?php

namespace App\Admin\Dashboard;

use Illuminate\Support\Facades\DB;

class Info
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function statistics()
    {
        $servers = DB::table('servers')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
        $active_servers = $servers->firstWhere('status', 1);

        $customers = DB::table('customers')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
        $active_customers = $customers->firstWhere('status', 1);

        $envs = [
            ['name' => 'Current Time', 'value' => now()],
            ['name' => 'Total / Active Servers', 'value' =>
                $servers->sum('total') . ' / ' . ($active_servers ? $active_servers->total : 0)],
            ['name' => 'Total / Active Customers', 'value' =>
                $customers->sum('total') . ' / ' . ($active_customers ? $active_customers->total : 0)],
        ];

        return view('admin.dashboard.statistics', compact('envs'));
    }
}
