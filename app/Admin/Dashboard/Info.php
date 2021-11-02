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

        $customers = DB::table('customers')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $envs = [
            ['name' => 'Current Time', 'value' => now()],
            ['name' => 'Total / Active Servers', 'value' =>
                $servers->sum('total') . ' / ' . $servers->firstWhere('status', 1)->total],
            ['name' => 'Total / Active Customers', 'value' =>
                $customers->sum('total') . ' / ' . $customers->firstWhere('status', 1)->total],
        ];

        return view('admin.dashboard.statistics', compact('envs'));
    }
}
