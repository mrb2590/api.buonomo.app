<?php

namespace App\Http\Resources\Drive;

use App\Http\Resources\Drive\Folder as FolderResource;
use App\Http\Resources\PublicUser as PublicUserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class File extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'extension' => $this->extension,
            'mime_type' => $this->mime_type,
            'folder_id' => $this->folder_id,
            'size' => $this->size,
            'formatted_size' => $this->formatted_size,
            'path' => $this->path,
            'deleted_at' => !$this->deleted_at ?: $this->deleted_at->timestamp,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp,
            'owned_by' => new PublicUserResource($this->owned_by),
            'created_by' => new PublicUserResource($this->created_by),
            'updated_by' => new PublicUserResource($this->updated_by),
            'folder' => new FolderResource($this->whenLoaded('folder')),
        ];
    }
}
