<?php


use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';


class FileStorageTest extends Tester\TestCase
{

    /** @var  \ondrs\UploadManager\Storages\FileStorage */
    private $fileStorage;

    const STORAGE_NAMESPACE = 'FileStorage';


    function setUp()
    {
        $this->fileStorage = new \ondrs\UploadManager\Storages\FileStorage(TEMP_DIR, self::STORAGE_NAMESPACE);
    }


    function testSave()
    {
        $source = __DIR__ . '/../data/focus.png';
        $savedFile = $this->fileStorage->save($source, 'image/focus.png');

        Assert::true(is_file($savedFile));
        Assert::same(file_get_contents($source), file_get_contents($savedFile));
    }


    function testDelete()
    {
        $source = __DIR__ . '/../data/focus.png';
        $filename = uniqid('focus') . '.png';
        $dest = $this->fileStorage->getBasePath() . '/' . $this->fileStorage->getRelativePath() . '/' . $filename;

        copy($source, $dest);

        Assert::true(file_exists($dest));
        $this->fileStorage->delete($filename);
        Assert::false(file_exists($dest));
    }


    function testBulkSave()
    {
        $namespace = uniqid('image');

        $files = [
            [__DIR__ . '/../data/focus.png', "$namespace/focus.png"],
            [__DIR__ . '/../data/test-file.php', "$namespace/test-file.php"],
        ];

        $results = $this->fileStorage->bulkSave($files);

        Assert::count(2, $results);

        foreach ($results as $file) {
            Assert::true(file_exists($file));
        }
    }


    function testFind()
    {
        $storage = new \ondrs\UploadManager\Storages\FileStorage(__DIR__ . '/../../UploadManager', 'data');

        Assert::count(4, $storage->find('', '*.jpg'));
        Assert::count(1, $storage->find('', '*.png'));
        Assert::count(1, $storage->find('', '*.php'));
        Assert::count(5, $storage->find('', ['*.jpg', '*.png']));

        foreach ($storage->find('', ['*.jpg', '*.png']) as $filePath => $fileInfo) {
            Assert::true(file_exists($filePath));
            Assert::type(SplFileInfo::class, $fileInfo);
        }
    }


}


run(new FileStorageTest());
