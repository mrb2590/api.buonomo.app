<?php

namespace App\Http\Resources\Drive;

use App\Http\Resources\Drive\FolderTreeFolder as FolderTreeFolderResource;
use Illuminate\Http\Resources\Json\JsonResource;

class FolderTreeFolder extends JsonResource
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
            // 'folder_id' => $this->folder_id,
            // 'path' => $this->path,
            // 'folder' => new FolderTreeFolderResource($this->whenLoaded('folder')),
            'children' => FolderTreeFolderResource::collection(
                $this->whenLoaded('folders_recursive')
            ),
            // 'files_count' => $this->filesCount,
            // 'folders_count' => $this->foldersCount,
            // 'total_files' => $this->totalFilesCount,
            // 'total_folders' => $this->totalFoldersCount,
        ];
    }
}
