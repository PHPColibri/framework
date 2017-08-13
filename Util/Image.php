<?php
namespace Colibri\Util;

/**
 * Image manipulations.
 */
class Image
{
    /**
     * Type of resize.
     */
    const RESIZE_FILLED = 'filled';
    /**
     * Type of resize.
     */
    const RESIZE_CROPPED = 'cropped';

    /**
     * @param string $path
     * @param int    $width
     * @param int    $height
     * @param string $resizeType
     * @param int    $bgColor
     *
     * @return string binary stream with thumbnail
     *
     * @throws \Exception
     */
    public static function createThumbnail($path, $width = 100, $height = 100, $resizeType = self::RESIZE_FILLED, $bgColor = 0xfff5ee)
    {
        $img = self::createFromFileByMime($path);

        // Build the thumbnail
        $new_img = imagecreatetruecolor($width, $height);

        if ($resizeType == self::RESIZE_FILLED) {
            // Fill the image gray
            if ( ! imagefilledrectangle($new_img, 0, 0, $width - 1, $height - 1, $bgColor)) {
                throw new \Exception('can`t create thumbnail: can`t fill new image by bg-color');
            }
        }

        // Resize and copy to the new image
        list(
            $src_x, $src_y, $src_w, $src_h,
            $dst_x, $dst_y, $dst_w, $dst_h
            )
            = self::calcResizeParams($resizeType, $img, $width, $height);
        if ( ! imagecopyresampled($new_img, $img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)) {
            throw new \Exception('can`t create thumbnail: can`t resize image');
        }

        return self::getJpegToVar($new_img);
    }

    /**
     * @param string   $resizeType
     * @param resource $img
     * @param int      $tmbWidth
     * @param int      $tmbHeight
     *
     * @return array
     *
     * @throws \Exception
     */
    private static function calcResizeParams($resizeType, $img, $tmbWidth, $tmbHeight)
    {
        list($width, $height) = self::getImageSize($img);

        switch ($resizeType) {
            case self::RESIZE_FILLED:
                $tmb_ratio = $tmbWidth / $tmbHeight;
                $img_ratio = $width / $height;

                if ($tmb_ratio > $img_ratio) {
                    $new_width  = $img_ratio * $tmbHeight;
                    $new_height = $tmbHeight;
                } else {
                    $new_width  = $tmbWidth;
                    $new_height = $tmbWidth / $img_ratio;
                }

                if ($new_height > $tmbHeight) {
                    $new_height = $tmbHeight;
                }
                if ($new_width > $tmbWidth) {
                    $new_height = $tmbWidth;
                }

                return [
                    0,                              // $src_x
                    0,                              // $src_y
                    $width,                         // $src_w
                    $height,                        // $src_h
                    ($tmbWidth - $new_width) / 2,   // $dst_x
                    ($tmbHeight - $new_height) / 2, // $dst_y
                    $new_width,                     // $dst_w
                    $new_height,                    // $dst_h
                ];

            case self::RESIZE_CROPPED:
                if ($tmbWidth != $tmbHeight) {
                    throw new \Exception('this resize type implemented only for square thumbnails');
                }

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
                    $x,         // $src_x
                    $y,         // $src_y
                    $w,         // $src_w
                    $h,         // $src_h
                    0,          // $dst_x
                    0,          // $dst_y
                    $tmbWidth,  // $dst_w
                    $tmbHeight, // $dst_h
                ];

            default:
                throw new \Exception('unknown resize type');
        }
    }

    /**
     * @param string $path
     *
     * @return resource GD image
     *
     * @throws \Exception
     */
    private static function createFromFileByMime($path)
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

        if ( ! $img) {
            throw new \Exception('can`t create thumbnail: can`t create image handle from file ' . $path);
        }

        return $img;
    }

    /**
     * @param resource $img GD image
     *
     * @return array
     *
     * @throws \Exception
     */
    private static function getImageSize($img)
    {
        $width  = imagesx($img);
        $height = imagesy($img);
        if ( ! $width || ! $height) {
            throw new \Exception('can`t create thumbnail: can`t get image size, invalid image width or height');
        }

        return [$width, $height];
    }

    /**
     * @param resource $img
     *
     * @return string
     *
     * @throws \Exception
     */
    private static function getJpegToVar($img)
    {
        // Use a output buffering to load the image into a variable
        ob_start();
        if ( ! imagejpeg($img)) {
            ob_end_clean();
            throw new \Exception('can`t create thumbnail: can`t output thumbnail into var');
        }
        $imageVariable = ob_get_contents();
        ob_end_clean();

        return $imageVariable;
    }
}
