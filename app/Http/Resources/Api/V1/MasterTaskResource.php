<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterTaskResource extends JsonResource
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
            'description' => $this->description,
            'before_photo' => $this->before_photo_path ? [
                'url' => asset('storage/'.$this->before_photo_path),
                'status' => $this->before_status,
            ] : null,
            'after_photo' => $this->after_photo_path ? [
                'url' => asset('storage/'.$this->after_photo_path),
                'status' => $this->after_status,
            ] : null,
        ];
    }
}
