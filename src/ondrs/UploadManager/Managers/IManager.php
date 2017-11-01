<?php

namespace ondrs\UploadManager\Managers;

use Nette\Http\FileUpload;
use ondrs\UploadManager\Storages\IStorage;

interface IManager
{

    /**
     * @return IStorage
     */
    function getStorage();


    /**
     * @param string     $namespace
     * @param FileUpload $fileUpload
     * @return \SplFileInfo
     */
    function upload($namespace, FileUpload $fileUpload);


    /**
     * @param string $namespace
     * @param string $filename
     * @return void
     */
    function delete($namespace, $filename);

} 
