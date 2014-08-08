<?php


use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


class ImageManagerTest extends Tester\TestCase
{

    /** @var \ondrs\Uploader\ImageManager */
    private $imageManager;

    const RELATIVE_PATH = 'ImageManager';


    function setUp()
    {
        $this->imageManager = new \ondrs\Uploader\ImageManager(TEMP_DIR, 'ImageManager');
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

        Assert::true($this->imageManager->upload($fileUpload) instanceof \SplFileInfo);
        Assert::true(file_exists(TEMP_DIR . '/ImageManager/test-image.jpg'));
        Assert::true(file_exists(TEMP_DIR . '/ImageManager/800_test-image.jpg'));
        Assert::true(file_exists(TEMP_DIR . '/ImageManager/250_test-image.jpg'));

        // bcs image is small and it shouldn't be resized
        Assert::equal(md5_file(TEMP_DIR . '/ImageManager/test-image.jpg'), md5_file(TEMP_DIR . '/ImageManager/800_test-image.jpg'));
        Assert::equal(md5_file(TEMP_DIR . '/ImageManager/250_test-image.jpg'), md5_file(TEMP_DIR . '/ImageManager/800_test-image.jpg'));
    }


    function testDeleteImages()
    {
        $img = TEMP_DIR . '/test-image.jpg';
        $img800 = TEMP_DIR . '/800_test-image.jpg';
        $img250 = TEMP_DIR . '/250_test-image.jpg';

        copy(__DIR__ . '/data/test-image.jpg', $img);
        copy(__DIR__ . '/data/test-image.jpg', $img800);
        copy(__DIR__ . '/data/test-image.jpg', $img250);

        Assert::true(file_exists($img));
        Assert::true(file_exists($img800));
        Assert::true(file_exists($img250));

        $this->imageManager->delete(TEMP_DIR, 'test-image.jpg');

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

        Assert::true($this->imageManager->upload($fileUpload) instanceof \SplFileInfo);

        Assert::true(file_exists(TEMP_DIR . '/ImageManager/test-image-big.jpg'));
        Assert::true(file_exists(TEMP_DIR . '/ImageManager/800_test-image-big.jpg'));
        Assert::true(file_exists(TEMP_DIR . '/ImageManager/250_test-image-big.jpg'));

        $orig = \Nette\Utils\Image::fromFile(TEMP_DIR . '/ImageManager/test-image-big.jpg');

        Assert::true($orig->getWidth() === 1280);
    }


}


run(new ImageManagerTest());
