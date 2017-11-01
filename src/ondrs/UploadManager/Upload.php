<?php

namespace ondrs\UploadManager;

use Nette\Http\FileUpload;
use Nette\Http\IRequest;
use Nette\SmartObject;
use ondrs\UploadManager\Managers\IManager;
use SplFileInfo;

class Upload
{
    use SmartObject;

    /** @var IRequest */
    private $httpRequest;

    /** @var ManagerProvider */
    private $managerProvider;

    /** @var array */
    public $onQueueBegin = [];

    /** @var array */
    public $onQueueComplete = [];

    /** @var array */
    public $onFileBegin = [];

    /** @var array */
    public $onFileComplete = [];

    /** @var array */
    public $onError = [];


    public function __construct(IRequest $request, ManagerProvider $managerProvider)
    {
        $this->httpRequest = $request;
        $this->managerProvider = $managerProvider;
    }


    /**
     * @param string $namespace
     * @return SplFileInfo[]
     */
    public function filesToDir($namespace)
    {
        $uploadedFiles = [];

        $this->onQueueBegin($this->httpRequest->getFiles());

        foreach ($this->httpRequest->getFiles() as $file) {

            if (is_array($file)) {

                foreach ($file as $f) {
                    try {
                        $uploadedFiles[] = $this->singleFileToDir($namespace, $f);
                    } catch (UploadErrorException $e) {
                        $this->onError($f, $e);
                    }
                }

            } else {
                try {
                    $uploadedFiles[] = $this->singleFileToDir($namespace, $file);
                } catch (UploadErrorException $e) {
                    $this->onError($file, $e);
                }
            }
        }

        $this->onQueueComplete($this->httpRequest->getFiles(), $uploadedFiles);

        return $uploadedFiles;
    }


    /**
     * @param string $namespace
     * @param FileUpload $fileUpload
     * @return SplFileInfo
     * @throws UploadErrorException
     */
    public function singleFileToDir($namespace, FileUpload $fileUpload)
    {
        if ($error = $fileUpload->getError()) {
            throw new UploadErrorException($error);
        }

        /** @var IManager $manager */
        $manager = $this->managerProvider->get($fileUpload);

        $relativePath = Utils::normalizePath($manager->getStorage()->getRelativePath() . '/' . $namespace);

        $this->onFileBegin($fileUpload, $relativePath);

        $uploadedFile = $manager->upload($namespace, $fileUpload);

        $this->onFileComplete($uploadedFile, $fileUpload, $relativePath);

        return $uploadedFile;
    }

} 
