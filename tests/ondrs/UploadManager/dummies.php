<?php

use Nette\Http\FileUpload;
use Nette\Utils\Image;
use ondrs\UploadManager\ImageProcessor;

class DummyImageProcessor extends ImageProcessor
{

    public function process(FileUpload $fileUpload): Image
    {
        return $fileUpload->toImage();
    }

}
