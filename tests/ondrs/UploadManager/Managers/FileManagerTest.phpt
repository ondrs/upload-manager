<?php


use ondrs\UploadManager\NotAllowedFileException;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';


class FileManagerTest extends Tester\TestCase
{

    /** @var  \Mockery\MockInterface */
    private $storage;

    /** @var  \ondrs\UploadManager\Managers\FileManager */
    private $fileManager;


    function setUp()
    {
        $this->storage = Mockery::mock(\ondrs\UploadManager\Storages\IStorage::class);

        $this->fileManager = new \ondrs\UploadManager\Managers\FileManager($this->storage);
    }


    function testUpload()
    {
        $this->storage->shouldReceive('save')->andReturn(__DIR__ . '/../data/focus.png');

        $uploadedFile = $this->fileManager->upload('namespace', \ondrs\UploadManager\Utils::fileUploadFromFile(__DIR__ . '/../data/focus.png'));

        Assert::type(SplFileInfo::class, $uploadedFile);
        Assert::same('focus.png', $uploadedFile->getBasename());
    }


    function testUploadBlacklistedFile()
    {
        Assert::exception(function () {
            $this->fileManager->upload('namespace', \ondrs\UploadManager\Utils::fileUploadFromFile(__DIR__ . '/../data/test-file.php'));
        }, NotAllowedFileException::class);
    }
}


run(new FileManagerTest());
