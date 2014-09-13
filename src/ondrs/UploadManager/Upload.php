<?php
/**
 * Created by PhpStorm.
 * User: Ondra
 * Date: 8.8.14
 * Time: 19:09
 */

namespace ondrs\UploadManager;


use Nette\Http\FileUpload;
use Nette\Http\Request;
use Nette\Object;


class Upload extends Object
{

    /** @var Request */
    private $httpRequest;

    /** @var \ondrs\UploadManager\ImageManager */
    private $imageManager;

    /** @var \ondrs\UploadManager\FileManager */
    private $fileManager;

    /** @var array */
    public $onQueueBegin;

    /** @var array */
    public $onQueueComplete;

    /** @var array */
    public $onFileBegin;

    /** @var array */
    public $onFileComplete;


    /**
     * @param ImageManager $imageManager
     * @param FileManager $fileManager
     * @param Request $request
     */
    public function __construct(ImageManager $imageManager, FileManager $fileManager, Request $request)
    {
        $this->imageManager = $imageManager;
        $this->fileManager = $fileManager;
        $this->httpRequest = $request;
    }

    /**
     * @param \ondrs\UploadManager\FileManager $fileManager
     */
    public function setFileManager($fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * @return \ondrs\UploadManager\FileManager
     */
    public function getFileManager()
    {
        return $this->fileManager;
    }

    /**
     * @param \ondrs\UploadManager\ImageManager $imageManager
     */
    public function setImageManager($imageManager)
    {
        $this->imageManager = $imageManager;
    }

    /**
     * @return \ondrs\UploadManager\ImageManager
     */
    public function getImageManager()
    {
        return $this->imageManager;
    }


    /**
     * @param null|string $dir
     * @return array
     */
    public function filesToDir($dir = NULL)
    {
        $uploadedFiles = [];

        $this->onQueueBegin($this->httpRequest->getFiles());

        foreach ($this->httpRequest->getFiles() as $file) {

            if (is_array($file)) {
                foreach ($file as $f) {
                    $uploadedFiles[] = $this->singleFileToDir($f, $dir);
                }

            } else {
                $uploadedFiles[] = $this->singleFileToDir($file, $dir);
            }
        }

        $this->onQueueComplete($this->httpRequest->getFiles(), $uploadedFiles);

        return $uploadedFiles;
    }


    /**
     * @param FileUpload $fileUpload
     * @param null|string $dir
     * @return \SplFileInfo
     */
    public function singleFileToDir(FileUpload $fileUpload, $dir = NULL)
    {
        $name = $fileUpload->isImage() ? 'imageManager' : 'fileManager';

        /** @var IUploadManager $usedManager */
        $usedManager = $this->$name;
        $path = Utils::normalizePath($usedManager->getRelativePath() . '/' . $dir);

        $this->onFileBegin($fileUpload, $path);

        $uploadedFile = $usedManager->upload($fileUpload, $dir);

        $this->onFileComplete($fileUpload, $uploadedFile, $path);

        return $uploadedFile;
    }


} 
