<?php

namespace App\Models\Drive;

class Server
{
    /**
     * Format the filesize.
     *
     * @param integer $sizeInBytes
     * @return string
     */
    public static function formatBytes($sizeInBytes)
    {
        $decimals = '2';
        $size = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($sizeInBytes) - 1) / 3);
        $sizeReadable = (float) sprintf("%.2f", $sizeInBytes / pow(1024, $factor));
        $sizeReadable .= ' ' . @$size[$factor];

        return $sizeReadable;
    }

    /**
     * Return all server data.
     *
     * @return array
     */
    public static function data()
    {
        $totalSpace = disk_total_space(storage_path());

        return [
            'total' => self::totalSpace(),
            'free' => self::freeSpace(),
            'used' => self::usedSpace(),
        ];
    }

    /**
     * Get the total space on the server.
     *
     * @return array
     */
    public static function totalSpace()
    {
        $totalSpace = disk_total_space(storage_path());

        return [
            'bytes' => $totalSpace,
            'formatted' => self::formatBytes($totalSpace),
        ];
    }

    /**
     * Get the total free space on the server.
     *
     * @return array
     */
    public static function freeSpace()
    {
        $freeSpace = disk_free_space(storage_path());

        return [
            'bytes' => $freeSpace,
            'formatted' => self::formatBytes($freeSpace),
        ];
    }

    /**
     * Get the total used space on the server.
     *
     * @return array
     */
    public static function usedSpace()
    {
        $usedSpace = self::totalSpace()['bytes'] - self::freeSpace()['bytes'];

        return [
            'bytes' => $usedSpace,
            'formatted' => self::formatBytes($usedSpace),
        ];
    }
}
