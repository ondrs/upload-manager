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
     * @param Request $request
     * @param ImageManager $imageManager
     * @param FileManager $fileManager
     */
    public function __construct(Request $request, ImageManager $imageManager, FileManager $fileManager)
    {
        $this->httpRequest = $request;
        $this->imageManager = $imageManager;
        $this->fileManager = $fileManager;
    }


    /**
     * @param null|string $dir
     */
    public function listen($dir = NULL)
    {
        $uploadedFiles = [];

        $this->onQueueBegin($this->httpRequest->getFiles(), $dir);

        foreach ($this->httpRequest->getFiles() as $file) {

            if (is_array($file)) {
                foreach ($file as $f) {
                    $uploadedFiles[] = $this->upload($f, $dir);
                }

            } else {
                $uploadedFiles[] = $this->upload($file, $dir);
            }
        }

        $this->onQueueComplete($this->httpRequest->getFiles(), $uploadedFiles, $dir);
    }


    /**
     * @param FileUpload $fileUpload
     * @param null $dir
     * @return \SplFileInfo
     */
    public function upload(FileUpload $fileUpload, $dir = NULL)
    {
        $this->onFileBegin($fileUpload, $dir);

        if ($fileUpload->isImage()) {
            $uploadedFile = $this->imageManager->upload($fileUpload, $dir);
        } else {
            $uploadedFile = $this->fileManager->upload($fileUpload, $dir);
        }

        $this->onFileComplete($fileUpload, $uploadedFile, $dir);

        return $uploadedFile;
    }


} 
