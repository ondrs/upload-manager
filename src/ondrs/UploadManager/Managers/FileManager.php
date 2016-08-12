<?php

namespace ondrs\UploadManager\Managers;

use Nette\Http\FileUpload;
use Nette\Object;
use ondrs\UploadManager\NotAllowedFileException;
use ondrs\UploadManager\Storages\IStorage;
use ondrs\UploadManager\Utils;
use SplFileInfo;

class FileManager extends Object implements IManager
{

    /** @var IStorage */
    private $storage;

    /** @var array */
    private $blacklist = [
        'php',
    ];


    public function __construct(IStorage $storage, array $blacklist = [])
    {
        $this->storage = $storage;

        if (count($blacklist)) {
            $this->setBlacklist($blacklist);
        }
    }


    /**
     * @param array $blacklist
     */
    public function setBlacklist(array $blacklist)
    {
        $this->blacklist = $blacklist;
    }


    /**
     * @return array
     */
    public function getBlacklist()
    {
        return $this->blacklist;
    }


    /**
     * @return IStorage
     */
    public function getStorage()
    {
        return $this->storage;
    }


    /**
     * @param string $namespace
     * @param FileUpload $fileUpload
     * @return SplFileInfo
     * @throws NotAllowedFileException
     */
    public function upload($namespace, FileUpload $fileUpload)
    {
        $filename = Utils::sanitizeFileName($fileUpload);

        $fileInfo = new SplFileInfo($filename);
        $suffix = $fileInfo->getExtension();

        if (in_array($suffix, $this->blacklist)) {
            throw new NotAllowedFileException("Upload of the file with $suffix suffix is not allowed.");
        }

        $destination = $this->storage->save($fileUpload->getTemporaryFile(), "$namespace/$filename");

        return new SplFileInfo($destination);
    }


    /**
     * @param string $namespace
     * @param string $filename
     * @return void
     */
    public function delete($namespace, $filename)
    {
        $this->storage->delete("$namespace/$filename");
    }
}
