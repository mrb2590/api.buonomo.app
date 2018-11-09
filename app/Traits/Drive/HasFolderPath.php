<?php

namespace App\Traits\Drive;

use Illuminate\Http\Request;

trait HasFolderPath
{
    /**
     * Get a relative path based on the depth level of the current folder.
     * 
     * @param  int   $depth
     * @param  bool  $leadingSlash
     */
    public function getRelativePath(int $depth, bool $leadingSlash = true)
    {
        $parts = explode('/', $this->path);
        $reversedParts = array_reverse($parts);
        $newParts = [];
        $depthCount = 0;

        foreach ($reversedParts as $part) {
            $newParts[] = $part;

            if (++$depthCount > $depth) {
                break;
            }
        }

        $relativePath = ltrim(implode('/', array_reverse($newParts)), '/');

        $relativePath = $leadingSlash ? '/'.$relativePath : $relativePath;

        return $relativePath;
    }
}
