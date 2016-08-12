<?php

use \Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';


class ImageManagerTest extends Tester\TestCase
{

    /** @var  \Mockery\MockInterface */
    private $storage;

    /** @var  \ondrs\UploadManager\Managers\ImageManager */
    private $imageManager;


    function setUp()
    {
        $this->storage = Mockery::mock(\ondrs\UploadManager\Storages\IStorage::class);

        $this->imageManager = new \ondrs\UploadManager\Managers\ImageManager($this->storage, TEMP_DIR);
    }


    function testUploadImage()
    {
        $filePath = __DIR__ . '/../data/test-image.jpg';

        $this->storage->shouldReceive('bulkSave')->with([
            [$this->imageManager->getTempDir() . '/test-image_8bea6ad6b.jpg', 'namespace/test-image_8bea6ad6b.jpg'],
            [$this->imageManager->getTempDir() . '/800_test-image_8bea6ad6b.jpg', 'namespace/800_test-image_8bea6ad6b.jpg'],
            [$this->imageManager->getTempDir() . '/250_test-image_8bea6ad6b.jpg', 'namespace/250_test-image_8bea6ad6b.jpg'],
        ]);

        $fileInfo = $this->imageManager->upload('namespace', \ondrs\UploadManager\Utils::fileUploadFromFile($filePath));

        Assert::type(SplFileInfo::class, $fileInfo);
        Assert::false(is_dir($this->imageManager->getTempDir()));   // temp dir has to be cleaned
    }


    function testUploadNonImage()
    {
        Assert::exception(function () {
            $this->imageManager->upload('namespace', \ondrs\UploadManager\Utils::fileUploadFromFile(__DIR__ . '/../data/test-image.text'));
        }, InvalidArgumentException::class);
    }


    function testDelete()
    {
        $variants = ['800_image.jpg', '250_image.jpg', 'image.jpg'];

        $this->storage->shouldReceive('find')
            ->with('namespace', $variants)
            ->andReturn(array_combine($variants, $variants))
            ->getMock()
            ->shouldReceive('bulkDelete')
            ->with($variants);

        $this->imageManager->delete('namespace', 'image.jpg');
    }

}


run(new ImageManagerTest());
