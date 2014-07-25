<?php
namespace Colibri\Util;
/**
 * Description of CFile
 *
 * @author		Александр Чибрикин aka alek13 <chibrikinalex@mail.ru>
 * @version		1.00.0
 */
class File
{
	/**
	 * Get mime-type og the file
	 * @param string $filepath
	 * @return string string with mime-type like 'image/jpeg' & etc.
	 */
static
	public		function	getMimeType($filepath)
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo,$filepath);
		finfo_close($finfo);
		
		return $mime;
	}
}

