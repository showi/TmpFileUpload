<?php
namespace TmpFileUpload\Helper;

class CommonHelper {

    public static function convertPHPSizeToBytes($sSize)
    {
        if (is_numeric($sSize)) {
            return $sSize;
        }
        $sSuffix = substr($sSize, - 1);
        $iValue = substr($sSize, 0, - 1);
        switch (strtoupper($sSuffix)) {
            case 'P':
                $iValue *= 1024;
            case 'T':
                $iValue *= 1024;
            case 'G':
                $iValue *= 1024;
            case 'M':
                $iValue *= 1024;
            case 'K':
                $iValue *= 1024;
                break;
        }
        return $iValue;
    }

    public static function randomString($length, $strong = True)
    {
        $bytes = openssl_random_pseudo_bytes($length / 2, $strong);
        return bin2hex($bytes);
    }

    public static function validUntil($expire_in)
    {
        $datetime = new \DateTime('now');
        $datetime->modify($expire_in);
        return $datetime->format('Y-m-d H:i:s');
    }

    public static function getUploadMaxFilesize()
    {
        $max_upload = CommonHelper::convertPHPSizeToBytes(ini_get('upload_max_filesize'));
        $max_post = CommonHelper::convertPHPSizeToBytes(ini_get('post_max_size'));
        $memory_limit = CommonHelper::convertPHPSizeToBytes(ini_get('memory_limit'));
        return min($max_upload, $max_post, $memory_limit);
    }

    public static function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
};