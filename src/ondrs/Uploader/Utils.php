<?php
/**
 * Created by PhpStorm.
 * User: Ondra
 * Date: 8.8.14
 * Time: 19:09
 */

namespace ondrs\Uploader;


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
                chmod($path, 0775);
            }

            $path .= '/';
        }
    }

} 
