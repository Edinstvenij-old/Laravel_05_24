<?php

namespace App\Http\Resources\Products;

use App\Http\Resources\Categories\CategoriesCollection;
use App\Http\Resources\Images\ImagesCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResourceV2 extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'prices' => [
                'price' => $this->price,
            ],
            'categories' => new CategoriesCollection($this->categories),
            'images' => new ImagesCollection($this->images),
        ];
    }
}
