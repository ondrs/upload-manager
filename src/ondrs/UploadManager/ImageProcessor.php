<?php

namespace ondrs\UploadManager;

use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Image;
use Nette\Utils\ImageException;
use Nette\Utils\Strings;

class ImageProcessor
{

    /** @var string */
    private $tempDir;

    /** @var string[] */
    private $toDelete = [];


    public function __construct($tempDir)
    {
        $this->tempDir = $tempDir;
    }


    public function __destruct()
    {
        foreach ($this->toDelete as $path) {
            try {
                FileSystem::delete($path);
            } catch (\Nette\IOException $e) {
                // intently do nothing
            }
        }
    }


    /**
     * @param array $exif
     * @return int|NULL
     */
    public static function getOrientation(array $exif)
    {
        foreach ($exif as $key => $val) {

            if (strtolower($key) === 'orientation' && preg_match('/(-?\d+)/', $val, $matches)) {
                return (int)$matches[1];
            }

            if (is_array($exif[$key]) && $result = self::getOrientation($exif[$key])) {
                return $result;
            }
        }

        return NULL;
    }


    /**
     * @see https://stackoverflow.com/questions/35337709/invalid-sos-parameters-for-sequential-jpeg
     * @param string $path
     * @return Image
     * @throws \Nette\NotSupportedException
     * @throws \Nette\Utils\ImageException
     * @throws \ondrs\UploadManager\InvalidArgumentException
     */
    public static function fromString($path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException("File '$path' does not exists");
        }

        try {
            return Image::fromFile($path);

        } catch (ImageException $e) {

            if (!preg_match('/Invalid SOS parameters for sequential JPEG/i', $e->getMessage())) {
                throw $e;
            }

            $image = @imagecreatefromstring(file_get_contents($path));

            if (!$image) {
                throw $e;
            }

            return new Image($image);
        }
    }


    /**
     * @param FileUpload $fileUpload
     * @return Image
     * @throws \Nette\NotSupportedException
     * @throws \ondrs\UploadManager\InvalidArgumentException
     * @throws \Nette\Utils\ImageException
     */
    public function process(FileUpload $fileUpload)
    {
        $tempFile = "$this->tempDir/" . uniqid('', FALSE) . $fileUpload->getSanitizedName();

        $fileUpload->move($tempFile);
        $this->toDelete[] = $tempFile;

        $image = self::fromString($tempFile);

        if (!extension_loaded('exif')) {
            return $image;
        }

        // intently suppressed
        $exif = @exif_read_data($tempFile);

        if (!$exif) {
            return $image;
        }

        $bgrColor = Image::rgb(0, 0, 0);

        // GD doesn't use exif data at all, we have to fix the orientation manually
        // @see https://stackoverflow.com/questions/7489742/php-read-exif-data-and-adjust-orientation
        switch (self::getOrientation($exif)) {
            case 3:
            case 180:
                $image->rotate(180, $bgrColor);
                break;

            case 6:
            case -90:
                $image->rotate(-90, $bgrColor);
                break;

            case 8:
            case 90:
                $image->rotate(90, $bgrColor);
                break;
        }

        return $image;
    }

}
