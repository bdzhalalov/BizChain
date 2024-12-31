<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchProfitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_purchase_price' => (int) $this->total_cost,
            'total_price' => (int) $this->total_sales,
            'profit' => (int) $this->profit,
        ];
    }
}
