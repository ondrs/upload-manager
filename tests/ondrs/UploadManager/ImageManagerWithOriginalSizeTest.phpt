<?php


use ondrs\UploadManager\ImageManager;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


class ImageManagerWithOriginalSize extends Tester\TestCase
{

    /** @var ImageManager */
    private $imageManager;


    function setUp()
    {
        $this->imageManager = new ImageManager(TEMP_DIR, 'ImageManagerWithOriginalSize');
        $this->imageManager->saveOriginal();
    }


    function testUploadSmallImage()
    {
        $filePath = TEMP_DIR . '/test-image.jpg';

        copy(__DIR__ . '/data/test-image.jpg', $filePath);

        $file = new \SplFileInfo($filePath);

        $fileUpload = new \Nette\Http\FileUpload([
            'name' => $file->getBasename(),
            'type' => $file->getType(),
            'size' => $file->getSize(),
            'tmp_name' => $filePath,
            'error' => 0
        ]);

        $uploaded = $this->imageManager->upload($fileUpload);
        $filename = $uploaded->getFilename();

        Assert::true($uploaded instanceof \SplFileInfo);
        Assert::equal('jpg', $uploaded->getExtension());

        Assert::true(file_exists(TEMP_DIR . '/ImageManagerWithOriginalSize/' . $filename));
        Assert::true(file_exists(TEMP_DIR . '/ImageManagerWithOriginalSize/800_' . $filename));
        Assert::true(file_exists(TEMP_DIR . '/ImageManagerWithOriginalSize/250_' . $filename));
        Assert::true(file_exists(TEMP_DIR . '/ImageManagerWithOriginalSize/orig_' . $filename));

        // bcs image is small and it shouldn't be resized
        Assert::equal(md5_file(TEMP_DIR . '/ImageManagerWithOriginalSize/' . $filename), md5_file(TEMP_DIR . '/ImageManagerWithOriginalSize/800_' . $filename));
        Assert::equal(md5_file(TEMP_DIR . '/ImageManagerWithOriginalSize/250_' . $filename), md5_file(TEMP_DIR . '/ImageManagerWithOriginalSize/800_' . $filename));
    }


    function testDeleteImages()
    {
        $img = TEMP_DIR . '/ImageManagerWithOriginalSize/test-image.jpg';
        $img800 = TEMP_DIR . '/ImageManagerWithOriginalSize/800_test-image.jpg';
        $img250 = TEMP_DIR . '/ImageManagerWithOriginalSize/250_test-image.jpg';
        $imgOrig = TEMP_DIR . '/ImageManagerWithOriginalSize/orig_test-image.jpg';

        copy(__DIR__ . '/data/test-image.jpg', $img);
        copy(__DIR__ . '/data/test-image.jpg', $img800);
        copy(__DIR__ . '/data/test-image.jpg', $img250);
        copy(__DIR__ . '/data/test-image.jpg', $imgOrig);

        Assert::true(file_exists($img));
        Assert::true(file_exists($img800));
        Assert::true(file_exists($img250));
        Assert::true(file_exists($imgOrig));

        $this->imageManager->delete(NULL, 'test-image.jpg');

        Assert::false(file_exists($img));
        Assert::false(file_exists($img800));
        Assert::false(file_exists($img250));
        Assert::false(file_exists($imgOrig));
    }


}


run(new ImageManagerWithOriginalSize());
