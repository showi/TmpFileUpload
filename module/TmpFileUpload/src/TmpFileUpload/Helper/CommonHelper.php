<?php
/*
Copyright (c) 2014 Joachim Basmaison

This file is part of TmpFileUpload <https://github.com/showi/TmpFileUpload>

TmpFileUpload is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

See the GNU General Public License for more details.
*/

namespace TmpFileUpload\Helper;
use TmpFileUpload\Exception;

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

    public static function validUntil($delta = '+0min')
    {
        $datetime = new \DateTime('NOW');
        $datetime->modify($delta);
//         return date('Y-m-d H:i:s', strtotime($expire_in));
        return $datetime->format('Y-m-d H:i:s');
    }

    public static function getUploadMaxFilesize()
    {
        $max_upload = CommonHelper::convertPHPSizeToBytes(
            ini_get('upload_max_filesize'));
        $max_post = CommonHelper::convertPHPSizeToBytes(
            ini_get('post_max_size'));
        $memory_limit = CommonHelper::convertPHPSizeToBytes(
            ini_get('memory_limit'));
        return min($max_upload, $max_post, $memory_limit);
    }

    public static function formatBytes($bytes, $precision = 2)
    {
        $units = array(
            'B',
            'KB',
            'MB',
            'GB',
            'TB'
        );

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public static function startsWith($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    public static function endsWith($haystack, $needle)
    {
        return $needle === "" ||
             substr($haystack, - strlen($needle)) === $needle;
    }

    public static function removeMeta($binary, $path) {
        if (!is_executable($binary)) {
            throw new Exception\InvalidBinaryException($binary);
        }
        $return = null;
        $cmd = escapeshellcmd("$binary rm $path");
        system($cmd, $return);
        if($return != 0) {
        	return False;
        }
        return True;
    }

    public static function isImage($mime) {
        if (CommonHelper::startsWith($mime, 'image')) {
            return True;
        }
        return False;
    }

    public static function link($url, $txt) {
        return "<a href=\"$url\">$txt</a>";
    }

    public static function getFileTable($serviceLocator) {
        return $serviceLocator->get('TmpFileUpload\Model\FileTable');
    }

    public static function getMimeTable($serviceLocator) {
        return $serviceLocator->get('TmpFileUpload\Model\MimeTable');
    }
}
;