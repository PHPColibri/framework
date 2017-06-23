<?php
namespace Colibri\Util;
/**
 * Description of CFile
 *
 * @author         Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @version        1.00.0
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
    static
    public function getMimeType($filePath)
    {
        $fInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($fInfo, $filePath);
        finfo_close($fInfo);

        return $mime;
    }
}

