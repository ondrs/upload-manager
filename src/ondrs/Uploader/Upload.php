<?php
/**
 * Created by PhpStorm.
 * User: Ondra
 * Date: 8.8.14
 * Time: 19:09
 */

namespace ondrs\Uploader;


use Nette\Http\FileUpload;
use Nette\Http\Request;
use Nette\Object;


class Upload extends Object
{

    /** @var Request */
    private $httpRequest;

    /** @var \ondrs\Uploader\ImageManager */
    private $imageManager;

    /** @var \ondrs\Uploader\FileManager */
    private $fileManager;

    /** @var array */
    public $onSuccess;


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
     *
     */
    public function listen()
    {
        $dir = $this->httpRequest->getQuery('dir');

        foreach ($this->httpRequest->getFiles() as $file) {

            if (is_array($file)) {
                foreach ($file as $f) {
                    $this->upload($f, $dir);
                }

            } else {
                $this->upload($file, $dir);
            }
        }
    }


    /**
     * @param FileUpload $fileUpload
     * @param null $dir
     * @return \SplFileInfo
     */
    public function upload(FileUpload $fileUpload, $dir = NULL)
    {
        if ($fileUpload->isImage()) {
            $uploadedFile = $this->imageManager->upload($fileUpload, $dir);
        } else {
            $uploadedFile = $this->fileManager->upload($fileUpload, $dir);
        }

        $this->onSuccess($fileUpload, $uploadedFile, $dir);

        return $uploadedFile;
    }


} 
