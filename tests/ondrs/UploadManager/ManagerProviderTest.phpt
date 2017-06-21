<?php


use ondrs\UploadManager\ManagerContainer;
use ondrs\UploadManager\ManagerProvider;
use ondrs\UploadManager\Managers\FileManager;
use ondrs\UploadManager\Managers\ImageManager;
use ondrs\UploadManager\Utils;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/dummies.php';


class ManagerProviderTest extends Tester\TestCase
{

    /** @var  \Mockery\MockInterface */
    private $managerContainer;

    /** @var  DummyImageProcessor */
    private $dummyImageProcessor;

    /** @var  ManagerProvider */
    private $managerProvider;


    function setUp()
    {
        $diContainer = Mockery::mock(\Nette\DI\Container::class);
        $diContainer->shouldReceive('findByType')->andReturn([]);

        $storage = Mockery::mock(\ondrs\UploadManager\Storages\IStorage::class);

        $this->dummyImageProcessor = new DummyImageProcessor(TEMP_DIR);

        $this->managerContainer = new ManagerContainer($diContainer);

        $this->managerContainer->register(new FileManager($storage));
        $this->managerContainer->register(new ImageManager($storage, $this->dummyImageProcessor, TEMP_DIR));

        $this->managerProvider = new ManagerProvider($this->managerContainer);
    }


    function testGet()
    {
        $png = Utils::fileUploadFromFile(__DIR__ . '/data/focus.png');
        $jpg = Utils::fileUploadFromFile(__DIR__ . '/data/test-image.jpg');
        $text = Utils::fileUploadFromFile(__DIR__ . '/data/test-file.txt');
        $php = Utils::fileUploadFromFile(__DIR__ . '/data/test-file.php');

        Assert::type(ImageManager::class, $this->managerProvider->get($png));
        Assert::type(FileManager::class, $this->managerProvider->get($text));
        Assert::type(ImageManager::class, $this->managerProvider->get($jpg));
        Assert::type(FileManager::class, $this->managerProvider->get($php));
    }


}


run(new ManagerProviderTest());
