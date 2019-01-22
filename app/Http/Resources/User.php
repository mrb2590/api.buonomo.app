<?php

namespace App\Http\Resources;

use App\Http\Resources\Avatar as AvatarResource;
use App\Http\Resources\Drive\Folder as FolderResource;
use App\Http\Resources\Role as RoleResource;
use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'slug' => $this->slug,
            'folder_id' => $this->folder_id,
            'allocated_drive_bytes' => $this->allocated_drive_bytes,
            'formatted_allocated_drive_bytes' => $this->formatted_allocated_drive_bytes,
            'used_drive_bytes' => $this->used_drive_bytes,
            'formatted_used_drive_bytes' => $this->formatted_used_drive_bytes,
            'email_verified_at' => !$this->email_verified_at
                ? null
                : $this->email_verified_at->timestamp,
            'deleted_at' => !$this->deleted_at ? null : $this->deleted_at->timestamp,
            'created_at' => $this->created_at->timestamp,
            'updated_at' => $this->updated_at->timestamp,
            'folder' => new FolderResource($this->whenLoaded('folder')),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'avatar' => new AvatarResource($this->whenLoaded('avatar')),
        ];
    }
}
