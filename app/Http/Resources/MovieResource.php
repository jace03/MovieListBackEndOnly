<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
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
            'year' => $this->year,
            'added_by' => $this->added_by,
            'rating' => $this->rating,
            'genre' => $this->genre,
            'decade' => $this->decade,
            'rank' => $this->rank,
            'watched' => $this->watched,
            'notes' => $this->notes,
            'poster_url' => $this->poster_url,
            'created_at' => $this->created_at,
        ];
    }
}
