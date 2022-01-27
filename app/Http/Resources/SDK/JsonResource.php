<?php

namespace App\Http\Resources\SDK;

use Illuminate\Http\Resources\Json\JsonResource as LaravelJsonResource;

class JsonResource extends LaravelJsonResource
{
    public static function collection($collection)
    {
        return parent::collection(
            collect($collection)->transform(fn($item) => new Model((array) $item))
        );
    }
}
