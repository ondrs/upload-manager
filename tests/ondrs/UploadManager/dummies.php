<?php

class DummyImageProcessor extends \ondrs\UploadManager\ImageProcessor
{

    public function process(\Nette\Http\FileUpload $fileUpload)
    {
        return $fileUpload->toImage();
    }

}
