<?php

use ondrs\UploadManager\Managers\ImageManager;
use ondrs\UploadManager\Storages\IStorage;
use ondrs\UploadManager\Utils;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../dummies.php';

class ImageManagerTest extends Tester\TestCase
{

    /** @var  \Mockery\MockInterface */
    private $storage;

    /** @var  DummyImageProcessor */
    private $dummyImageProcessor;

    /** @var  ImageManager */
    private $imageManager;


    function setUp()
    {
        $this->storage = Mockery::mock(IStorage::class);

        $this->dummyImageProcessor = new DummyImageProcessor(TEMP_DIR);

        $this->imageManager = new ImageManager($this->storage, $this->dummyImageProcessor, TEMP_DIR);
    }


    function testUploadImage()
    {
        $filePath = __DIR__ . '/../data/test-image.jpg';

        $expectedArgs = [
            [$this->imageManager->getTempDir() . '/test-image_8bea6ad6b.jpeg', 'namespace/test-image_8bea6ad6b.jpeg'],
            [$this->imageManager->getTempDir() . '/800_test-image_8bea6ad6b.jpeg', 'namespace/800_test-image_8bea6ad6b.jpeg'],
            [$this->imageManager->getTempDir() . '/250_test-image_8bea6ad6b.jpeg', 'namespace/250_test-image_8bea6ad6b.jpeg'],
        ];

        $this->storage->shouldReceive('bulkSave')
            ->once()
            ->with($expectedArgs)
            ->andReturn([new SplFileInfo($filePath)]);

        $fileInfo = $this->imageManager->upload('namespace', Utils::fileUploadFromFile($filePath));

        Assert::type(SplFileInfo::class, $fileInfo);
        Assert::false(is_dir($this->imageManager->getTempDir()));   // temp dir has to be cleaned
    }


    function testUploadNonImage()
    {
        Assert::exception(function () {
            $this->imageManager->upload('namespace', Utils::fileUploadFromFile(__DIR__ . '/../data/test-image.text'));
        }, InvalidArgumentException::class);
    }


    function testDelete()
    {
        $variants = ['800_image.jpg', '250_image.jpg', 'image.jpg'];
        $returnDelete = [];
        $returnFind = [];

        foreach ($variants as $variant) {
            $returnDelete[] = "namespace/$variant";
            $returnFind[$variant] = new SplFileInfo($variant);
        }

        $this->storage->shouldReceive('find')
            ->with('namespace', $variants)
            ->andReturn($returnFind)
            ->getMock()
            ->shouldReceive('bulkDelete')
            ->with($returnDelete);

        $this->imageManager->delete('namespace', 'image.jpg');
    }

}


run(new ImageManagerTest());
