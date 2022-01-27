<?php

namespace App\Http\Resources;

use \App\Http\Resources\SDK\JsonResource;

class ServerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ipAddress' => $this->ipAddress,
            'connected' => $this->connected,
            'online' => $this->online,
        ];
    }
}
