<?php

namespace ondrs\UploadManager;


use Nette\Http\FileUpload;

interface IUploadManager
{

    /**
     * @return string
     */
    public function getRelativePath();

    /**
     * @return string
     */
    public function getBasePath();

    /**
     * @param FileUpload $fileUpload
     * @param NULL|string $dir
     * @return \SplFileInfo
     */
    public function upload(FileUpload $fileUpload, $dir = NULL);


    /**
     * @param string $dir
     * @param string $filename
     * @return void
     */
    public function delete($dir, $filename);

} 
