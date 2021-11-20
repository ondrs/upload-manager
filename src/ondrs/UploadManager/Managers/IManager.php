<?php

namespace ondrs\UploadManager\Managers;

use Nette\Http\FileUpload;
use ondrs\UploadManager\Storages\IStorage;

interface IManager
{

    public function getStorage(): IStorage;


    public function upload(string $namespace, FileUpload $fileUpload): \SplFileInfo;


    public function delete(string $namespace, string $filename);

} 
