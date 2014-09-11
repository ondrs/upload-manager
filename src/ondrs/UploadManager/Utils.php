<?php
/**
 * Created by PhpStorm.
 * User: Ondra
 * Date: 8.8.14
 * Time: 19:09
 */

namespace ondrs\UploadManager;


use Nette\Http\FileUpload;
use Nette\Utils\Strings;

class Utils
{

    /**
     * @param $path
     * @return string
     */
    public static function normalizePath($path)
    {
        $path = preg_replace('~/+~', '/', $path);
        return rtrim($path, '/');
    }


    public static function sanitizeFileName(FileUpload $fileUpload)
    {
        $filename = $fileUpload->getSanitizedName();
        $filename = Strings::lower($filename);

        $fileInfo = new \SplFileInfo($filename);
        $suffix = $fileInfo->getExtension();
        $basename = $fileInfo->getBasename(".$suffix");

        $hash = md5($fileUpload->getContents());
        $hash = Strings::substring($hash, 0, 9);

        return Strings::substring($basename, 0, 50) . "_$hash.$suffix";
    }


    /**
     * @param string $dir
     */
    public static function makeDirectoryRecursive($dir)
    {
        $dir = self::normalizePath($dir);

        if(is_dir($dir)) {
            return;
        }

        $subDirs = explode('/', $dir);
        $path = '';

        foreach ($subDirs as $subDir) {

            $path .= $subDir;

            if (!is_dir($path)) {
                @mkdir($path);
                @chmod($path, 0777);
            }

            $path .= '/';
        }
    }

} 
