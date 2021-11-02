<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ServerDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $ips = DB::table('service_package')
            ->leftJoin('packages', 'service_package.package_id', '=', 'packages.id')
            ->leftJoin('subscriptions', 'packages.id', '=', 'subscriptions.package_id')
            ->leftJoin('customers', 'subscriptions.customer_id', '=', 'customers.id')
            ->leftJoin('customer_ips', 'customers.id', '=', 'customer_ips.customer_id')
            ->select('service_package.service_id', 'service_package.package_id', 'subscriptions.id AS subscription_id',
                'customer_ips.id', 'customer_ips.customer_id', 'customer_ips.ip', 'customer_ips.status', 'customer_ips.updated_at')
            ->whereIn('service_package.service_id', $this->services->pluck('id'))
            ->where('packages.status', '=', 1)
            ->where('subscriptions.status', '=', 1)
            // ->where('subscriptions.expiry', '>', now()->toDateTimeString())
            ->where('customers.status', '=', 1)
            ->get();

        return [
            'status' => $this->status ? 'ok' : 'disabled',
            'data' => [
                'id' => $this->id,
                'services' => ServiceResource::collection($this->services),
                'customer_ips' => CustomersIpsResource::collection($ips),
            ]
        ];
    }
}
