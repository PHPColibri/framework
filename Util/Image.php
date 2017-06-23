<?php
namespace Colibri\Util;

/**
 * Description of image
 *
 * @author         Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package        xTeam
 * @version        1.00.0
 */
class Image
{
    const RESIZE_FILLED = 'filled';
    const RESIZE_CROPPED = 'cropped';

    /**
     *
     * @param string $path
     * @param int    $width
     * @param int    $height
     * @param string $rszType
     * @param int    $bgColor
     *
     * @return string binary stream with thumbnail
     * @throws \Exception
     */
    static
    public function createThumbnail($path, $width = 100, $height = 100, $rszType = self::RESIZE_FILLED, $bgColor = 0xfff5ee)
    {
        $img = self::createFromFileByMime($path);

        // Build the thumbnail
        $new_img = ImageCreateTrueColor($width, $height);

        if ($rszType == self::RESIZE_FILLED) {
            // Fill the image gray
            if (!@imagefilledrectangle($new_img, 0, 0, $width - 1, $height - 1, $bgColor))
                throw new \Exception('can`t create thumbnail: can`t fill new image by bg-color');
        }

        // Resize and copy to the new image
        list(
            $src_x, $src_y, $src_w, $src_h,
            $dst_x, $dst_y, $dst_w, $dst_h,
            ) = self::calcResizeParams($rszType, $img, $width, $height);
        if (!@imagecopyresampled($new_img, $img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h))
            throw new \Exception('can`t create thumbnail: can`t resize image');

        return self::getJpegToVar($new_img);
    }

    static private function calcResizeParams($rType, $img, $tmb_w, $tmb_h)
    {
        list($width, $height) = self::getImageSize($img);

        switch ($rType) {
            case self::RESIZE_FILLED:
                $tmb_ratio = $tmb_w / $tmb_h;
                $img_ratio = $width / $height;

                if ($tmb_ratio > $img_ratio) {
                    $new_width  = $img_ratio * $tmb_h;
                    $new_height = $tmb_h;
                } else {
                    $new_width  = $tmb_w;
                    $new_height = $tmb_w / $img_ratio;
                }

                if ($new_height > $tmb_h)
                    $new_height = $tmb_h;
                if ($new_width > $tmb_w)
                    $new_height = $tmb_w;

                return [
                    0,                        // $src_x
                    0,                        // $src_y
                    $width,                   // $src_w
                    $height,                  // $src_h
                    ($tmb_w - $new_width) / 2,  // $dst_x
                    ($tmb_h - $new_height) / 2,  // $dst_y
                    $new_width,               // $dst_w
                    $new_height,              // $dst_h
                ];

            case self::RESIZE_CROPPED:
                if ($tmb_w != $tmb_h)
                    throw new \Exception('this resize type implemented only for square thumbnails');

                if ($width > $height) {
                    $x = ($width - $height) / 2;
                    $y = 0;
                    $w =
                    $h = $height;
                } else {
                    $x = 0;
                    $y = ($height - $width) / 2;
                    $w =
                    $h = $width;
                }


                return [
                    $x,       // $src_x
                    $y,       // $src_y
                    $w,       // $src_w
                    $h,       // $src_h
                    0,        // $dst_x
                    0,        // $dst_y
                    $tmb_w,  // $dst_w
                    $tmb_h,  // $dst_h
                ];

            default:
                throw new \Exception('unknown resize type');
        }
    }

    /**
     * @param string $path
     *
     * @return resource GD image
     * @throws \Exception
     */
    static private function createFromFileByMime($path)
    {
        $mime = File::getMimeType($path);
        switch ($mime) {
            case 'image/jpeg':
                $img = imagecreatefromjpeg($path);
                break;
            case 'image/gif':
                $img = imagecreatefromgif($path);
                break;
            case 'image/png':
                $img = imagecreatefrompng($path);
                break;
            default:
                throw new \Exception('can`t create thumbnail: unknown image type');
        }

        if (!$img)
            throw new \Exception('can`t create thumbnail: can`t create image handle from file ' . $path);

        return $img;
    }

    /**
     * @param resource $img GD image
     *
     * @return array
     * @throws \Exception
     */
    static private function getImageSize($img)
    {
        $width  = imageSX($img);
        $height = imageSY($img);
        if (!$width || !$height)
            throw new \Exception('can`t create thumbnail: can`t get image size, invalid image width or height');

        return [$width, $height];
    }

    static private function getJpegToVar($img)
    {
        // Use a output buffering to load the image into a variable
        ob_start();
        if (!@imagejpeg($img)) {
            ob_end_clean();
            throw new \Exception('can`t create thumbnail: can`t output thumbnail into var');
        }
        $imageVariable = ob_get_contents();
        ob_end_clean();

        return $imageVariable;
    }
}
