<?php


use ondrs\UploadManager\Upload;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


class UploadTest extends Tester\TestCase
{

    /** @var  \Mockery\MockInterface */
    private $httpRequest;

    /** @var  \Mockery\MockInterface */
    private $managerProvider;

    /** @var  \Mockery\MockInterface */
    private $manager;

    /** @var  \Mockery\MockInterface */
    private $storage;

    /** @var Upload */
    private $upload;


    function setUp()
    {
        $this->httpRequest = Mockery::mock(\Nette\Http\IRequest::class);
        $this->managerProvider = Mockery::mock(\ondrs\UploadManager\ManagerProvider::class);
        $this->manager = Mockery::mock(\ondrs\UploadManager\Managers\IManager::class);
        $this->storage = Mockery::mock(\ondrs\UploadManager\Storages\IStorage::class);

        $this->upload = new Upload($this->httpRequest, $this->managerProvider);
    }


    function testSingleFileToDir()
    {
        $this->managerProvider->shouldReceive('get')
            ->andReturn($this->manager);

        $this->storage->shouldReceive('getRelativePath')
            ->andReturn('relativePath');

        $this->manager->shouldReceive('getStorage')
            ->andReturn($this->storage);

        $fileUpload = \ondrs\UploadManager\Utils::fileUploadFromFile(__DIR__ . '/data/focus.png');

        $this->manager->shouldReceive('upload')
            ->with('namespace', $fileUpload)
            ->andReturn(new SplFileInfo(__DIR__ . '/data/focus.png'));

        $this->upload->onFileBegin[] = function(\Nette\Http\FileUpload $fileUpload, $relativePath) {
            Assert::same('focus.png', $fileUpload->getName());
            Assert::same('relativePath/namespace', $relativePath);
        };

        $this->upload->onFileComplete[] = function(SplFileInfo $uploadedFile, \Nette\Http\FileUpload $fileUpload, $relativePath) {
            Assert::same('focus.png', $uploadedFile->getBasename());
            Assert::same('focus.png', $fileUpload->getName());
            Assert::same('relativePath/namespace', $relativePath);
        };

        $uploadedFile = $this->upload->singleFileToDir('namespace', $fileUpload);

        Assert::type(SplFileInfo::class, $uploadedFile);
        Assert::same('focus.png', $uploadedFile->getBasename());
    }

}


run(new UploadTest());
