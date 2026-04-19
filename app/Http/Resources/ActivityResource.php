<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'client_id'    => $this->client_id,
            'contact_id'   => $this->contact_id,
            'type'         => $this->type,
            'title'        => $this->title,
            'body'         => $this->body,
            'due_at'       => $this->due_at,
            'completed_at' => $this->completed_at,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
