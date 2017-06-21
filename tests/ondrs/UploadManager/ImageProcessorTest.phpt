<?php

use ondrs\UploadManager\ImageProcessor;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


class ImageProcessorTest extends Tester\TestCase
{

    /** @var  ImageProcessor */
    private $imageProcessor;


    function setUp()
    {
        $this->imageProcessor = new ImageProcessor(TEMP_DIR);
    }


    function testGetOrientation()
    {
        $arr = [
            'aaa' => 'aaa',
            'ccc' => 'ccc',
            '@foo' => [
                'Orientation' => 'Rotate 90',
            ]
        ];

        Assert::equal(90, ImageProcessor::getOrientation($arr));

        $arr = [
            'aaa' => 'aaa',
            'ccc' => 'ccc',
            '@foo' => [
                '@foo' => [
                    'Orientation' => 'Rotate -90',
                ]
            ]
        ];

        Assert::equal(-90, ImageProcessor::getOrientation($arr));

        $arr = [
            'aaa' => 'aaa',
            'ccc' => 'ccc',
            '@foo' => [
                '@foo' => [
                    'Orientation' => 6,
                ]
            ]
        ];

        Assert::equal(6, ImageProcessor::getOrientation($arr));

        $arr = [
            'aaa' => 'aaa',
            'ccc' => 'ccc',
            '@foo' => [
                '@foo' => [
                    'Orientation' => '',
                ]
            ]
        ];

        Assert::equal(NULL, ImageProcessor::getOrientation($arr));

        $arr = [
            'aaa' => 'aaa',
            'ccc' => 'ccc',
            '@foo' => [
                '@foo' => [
                    'aa' => '',
                ]
            ]
        ];

        Assert::equal(NULL, ImageProcessor::getOrientation($arr));

        $arr = [
            'aaa' => 'aaa',
            'ccc' => 'ccc',
            '@foo' => [
                '@foo' => [

                ]
            ],
            'Orientation' => 6,
        ];

        Assert::equal(6, ImageProcessor::getOrientation($arr));
    }


    function testProcessRotated()
    {
        $source = __DIR__ . '/data/exif-rotated-90.jpg';
        $temp = TEMP_DIR . '/exif-rotated-90.jpg';

        // must be copied bcs ImageProcessor::process uses FileUpload::move which will move the file
        \Nette\Utils\FileSystem::copy($source, $temp);

        $processed = $this->imageProcessor->process(
            \ondrs\UploadManager\Utils::fileUploadFromFile($temp)
        );

        list($width, $height) = getimagesize($source);

        // image should rotate, width and height flips
        Assert::equal($processed->getWidth(), $height);
        Assert::equal($processed->getHeight(), $width);
    }


    function testProcessNormal()
    {
        $source = __DIR__ . '/data/test-image.jpg';
        $temp = TEMP_DIR . '/test-image.jpg';

        // must be copied bcs ImageProcessor::process uses FileUpload::move which will move the file
        \Nette\Utils\FileSystem::copy($source, $temp);

        $processed = $this->imageProcessor->process(
            \ondrs\UploadManager\Utils::fileUploadFromFile($temp)
        );

        list($width, $height) = getimagesize($source);

        // image should NOT rotate, width and height are the same
        Assert::equal($processed->getWidth(), $width);
        Assert::equal($processed->getHeight(), $height);
    }


    function testProcessUnsportedExif()
    {
        // this image causes  E_WARNING: exif_read_data(594a568808552focus.png): File not supported
        // if warnings on exif_read_data() function are not suppressed via @
        $source = __DIR__ . '/data/focus.png';
        $temp = TEMP_DIR . '/focus.png';

        // must be copied bcs ImageProcessor::process uses FileUpload::move which will move the file
        \Nette\Utils\FileSystem::copy($source, $temp);

        $processed = $this->imageProcessor->process(
            \ondrs\UploadManager\Utils::fileUploadFromFile($temp)
        );

        Assert::type(\Nette\Utils\Image::class, $processed);
    }


    function testInvalidSosParameters()
    {
        $source = __DIR__ . '/data/invalid-sos-parameters.jpg';
        $temp = TEMP_DIR . '/invalid-sos-parameters.jpg';

        // must be copied bcs ImageProcessor::process uses FileUpload::move which will move the file
        \Nette\Utils\FileSystem::copy($source, $temp);

        $processed = $this->imageProcessor->process(
            \ondrs\UploadManager\Utils::fileUploadFromFile($temp)
        );

        Assert::type(\Nette\Utils\Image::class, $processed);
    }


}


run(new ImageProcessorTest());
