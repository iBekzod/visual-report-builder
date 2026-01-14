<?php

namespace Ibekzod\VisualReportBuilder\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'model' => $this->model,
            'configuration' => $this->configuration,
            'view_options' => $this->view_options,
            'user_id' => $this->user_id,
            'template_id' => $this->template_id,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],
            'shared_with' => $this->shares->map(function ($share) {
                return [
                    'id' => $share->user?->id,
                    'name' => $share->user?->name,
                    'email' => $share->user?->email,
                    'can_edit' => $share->can_edit,
                    'can_share' => $share->can_share,
                ];
            }),
            'can_edit' => $this->canEdit(auth()->id()),
            'can_share' => $this->canShare(auth()->id()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
