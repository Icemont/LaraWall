<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomersIpsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'service_id' => $this->service_id,
            'package_id' => $this->package_id,
            'subscription_id' => $this->subscription_id,
            'customer_id' => $this->customer_id,
            'ip' => $this->ip,
            'status' => $this->status,
            'updated' => strtotime($this->updated_at),
        ];
    }
}
