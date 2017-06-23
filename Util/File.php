<?php
namespace Colibri\Util;

/**
 * File helper.
 */
class File
{
    /**
     * Get mime-type og the file
     *
     * @param string $filePath
     *
     * @throws \Exception
     * @return string string with mime-type like 'image/jpeg' & etc.
     */
    public static function getMimeType($filePath)
    {
        $fInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($fInfo, $filePath);
        finfo_close($fInfo);

        return $mime;
    }
}
