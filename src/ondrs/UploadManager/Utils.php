<?php

namespace ondrs\UploadManager;

use Nette\Http\FileUpload;
use Nette\Utils\Strings;

class Utils
{


    public static function normalizePath(string $path): string
    {
        $path = preg_replace('~/+~', '/', $path);
        return rtrim($path, '/');
    }


    public static function getSuffix(string $filename): string
    {
        $fileInfo = new \SplFileInfo($filename);

        return $fileInfo->getExtension();
    }


    public static function sanitizeFileName(FileUpload $fileUpload): string
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


    public static function makeDirectoryRecursive(string $dir): void
    {
        $dir = self::normalizePath($dir);

        if (is_dir($dir)) {
            return;
        }

        $subDirs = explode('/', $dir);
        $path = '';

        foreach ($subDirs as $subDir) {

            $path .= $subDir;

            if (!@is_dir($path)) {
                @mkdir($path);
                @chmod($path, 0777);
            }

            $path .= '/';
        }
    }


    /**
     * @param string $filename
     * @return FileUpload
     * @throws \ondrs\UploadManager\FileNotExistsException
     */
    public static function fileUploadFromFile(string $filename): FileUpload
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
