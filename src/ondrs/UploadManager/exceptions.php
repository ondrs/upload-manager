<?php

namespace ondrs\UploadManager;

class Exception extends \Exception
{

}

class UploadErrorException extends Exception
{

    /** @var array of translated error codes */
    public static $messages = [
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help. ',
    ];


    public function __construct($code)
    {
        if (!$code === UPLOAD_ERR_OK) {
            throw new Exception('Upload is OK, why you are throwing en exception?');
        }

        $message = isset(self::$messages[$code])
            ? self::$messages[$code]
            : 'Unknown error code';

        parent::__construct($message, $code);
    }
}


class InvalidArgumentException extends \InvalidArgumentException
{

}


class FileNotExistsException extends InvalidArgumentException
{

}


class NotAllowedFileException extends Exception
{

}
