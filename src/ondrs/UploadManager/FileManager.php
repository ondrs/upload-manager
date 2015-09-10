<?php

namespace ondrs\UploadManager;


use Nette\Http\FileUpload;
use Nette\Object;
use Nette\Utils\FileSystem;
use SplFileInfo;

class FileManager extends Object implements IUploadManager
{

    /** @var array */
    private $blacklist = [
        'php',
    ];

    /** @var string */
    private $basePath;

    /** @var string */
    private $relativePath;


    /**
     * @param string $basePath
     * @param string $relativePath
     * @param null|array|string $blacklist
     */
    public function __construct($basePath, $relativePath, $blacklist = NULL)
    {
        $this->basePath = $basePath;
        $this->relativePath = $relativePath;

        if ($blacklist !== NULL) {
            $this->setBlacklist($blacklist);
        }
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * @param array|string $blacklist
     */
    public function setBlacklist($blacklist)
    {
        $this->blacklist = is_array($blacklist) ? $blacklist : [$blacklist];
    }

    /**
     * @return array
     */
    public function getBlacklist()
    {
        return $this->blacklist;
    }


    /**
     * @param FileUpload $fileUpload
     * @param NULL|string $dir
     * @return SplFileInfo
     * @throws NotAllowedFileException
     */
    public function upload(FileUpload $fileUpload, $dir = NULL)
    {
        $path = $this->basePath . '/' . $this->relativePath;

        if ($dir !== NULL) {
            $path .= '/' . $dir;
        }

        $path = Utils::normalizePath($path);
        Utils::makeDirectoryRecursive($path);

        $filename = Utils::sanitizeFileName($fileUpload);

        $fileInfo = new SplFileInfo($filename);
        $suffix = $fileInfo->getExtension();

        if (in_array($suffix, $this->blacklist)) {
            throw new NotAllowedFileException("Upload of the file with $suffix suffix is not allowed.");
        }

        $fileUpload->move($path . '/' . $filename);

        return new SplFileInfo($path . '/' . $filename);
    }


    /**
     * @param $dir
     * @param $filename
     * @return mixed|void
     */
    public function delete($dir, $filename)
    {
        $dir = $this->getBasePath() . '/' . $this->getRelativePath() . '/' . $dir;
        $dir = Utils::normalizePath($dir);

        FileSystem::delete($dir . '/' . $filename);
    }
}
