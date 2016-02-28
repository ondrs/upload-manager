<?php

namespace ondrs\UploadManager;


use Nette\Http\FileUpload;
use Nette\Utils\Strings;

class Utils
{

    /**
     * @param string $path
     * @return string
     */
    public static function normalizePath($path)
    {
        $path = preg_replace('~/+~', '/', $path);
        return rtrim($path, '/');
    }


    /**
     * @param string $filename
     * @return string
     */
    public static function getSuffix($filename)
    {
        $fileInfo = new \SplFileInfo($filename);

        return $fileInfo->getExtension();
    }


    /**
     * @param FileUpload $fileUpload
     * @return string
     */
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

        if (is_dir($dir)) {
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


    /**
     * @param string $filename
     * @return FileUpload
     */
    public static function fileUploadFromFile($filename)
    {
        if (!file_exists($filename)) {
            throw new FileNotExistsException("File '$filename' does not exists");
        }

        $file = new \SplFileInfo($filename);

        return new FileUpload([
            'name' => $file->getBasename(),
            'type' => $file->getType(),
            'size' => $file->getSize(),
            'tmp_name' => $filename,
            'error' => 0
        ]);
    }

} 
