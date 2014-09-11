<?php


use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


class ImageManagerTest extends Tester\TestCase
{

    /** @var \ondrs\UploadManager\ImageManager */
    private $imageManager;


    function setUp()
    {
        $this->imageManager = new \ondrs\UploadManager\ImageManager(TEMP_DIR, 'ImageManager');
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

        Assert::true(file_exists(TEMP_DIR . '/ImageManager/' . $filename));
        Assert::true(file_exists(TEMP_DIR . '/ImageManager/800_' . $filename));
        Assert::true(file_exists(TEMP_DIR . '/ImageManager/250_' . $filename));

        // bcs image is small and it shouldn't be resized
        Assert::equal(md5_file(TEMP_DIR . '/ImageManager/' . $filename), md5_file(TEMP_DIR . '/ImageManager/800_' . $filename));
        Assert::equal(md5_file(TEMP_DIR . '/ImageManager/250_' . $filename), md5_file(TEMP_DIR . '/ImageManager/800_' . $filename));
    }


    function testDeleteImages()
    {
        $img = TEMP_DIR . '/ImageManager/test-image.jpg';
        $img800 = TEMP_DIR . '/ImageManager/800_test-image.jpg';
        $img250 = TEMP_DIR . '/ImageManager/250_test-image.jpg';

        copy(__DIR__ . '/data/test-image.jpg', $img);
        copy(__DIR__ . '/data/test-image.jpg', $img800);
        copy(__DIR__ . '/data/test-image.jpg', $img250);

        Assert::true(file_exists($img));
        Assert::true(file_exists($img800));
        Assert::true(file_exists($img250));

        $this->imageManager->delete(NULL, 'test-image.jpg');

        Assert::false(file_exists($img));
        Assert::false(file_exists($img800));
        Assert::false(file_exists($img250));
    }


    function testUploadBigImage()
    {
        $filePath = TEMP_DIR . '/test-image-big.jpg';

        copy(__DIR__ . '/data/test-image-big.jpg', $filePath);

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

        Assert::true(file_exists(TEMP_DIR . '/ImageManager/' . $filename));
        Assert::true(file_exists(TEMP_DIR . '/ImageManager/800_' . $filename));
        Assert::true(file_exists(TEMP_DIR . '/ImageManager/250_' . $filename));

        $orig = \Nette\Utils\Image::fromFile(TEMP_DIR . '/ImageManager/' . $filename);

        Assert::true($orig->getWidth() === 1680);
    }


    function testUploadImageWithLongName()
    {
        $filePath = TEMP_DIR . '/' . \Nette\Utils\Random::generate(150) .  '.jpg';

        copy(__DIR__ . '/data/test-image-big.jpg', $filePath);

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

        Assert::equal(64, \Nette\Utils\Strings::length($filename));
    }


}


run(new ImageManagerTest());
