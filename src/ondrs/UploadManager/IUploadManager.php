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
     * @param null|string $dir
     * @return \SplFileInfo
     */
    public function upload(FileUpload $fileUpload, $dir = NULL);


    /**
     * @param $dir
     * @param $filename
     * @return mixed
     */
    public function delete($dir, $filename);

} 
