<?php


use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';


class S3StorageTest extends Tester\TestCase
{

    /** @var  \ondrs\UploadManager\Storages\S3Storage */
    private $s3Storage;

    const STORAGE_NAMESPACE = 's3-storage-test';


    function setUp()
    {
        $config = [
            'region' => 'eu-central-1',
            'version' => '2006-03-01',
        ];

        $credentialsFile = TEMP_DIR . '/../../aws.credentials.php';

        if (file_exists($credentialsFile)) {
            $config['credentials'] = require $credentialsFile;
        }

        $s3Client = new \Aws\S3\S3Client($config);

        $this->s3Storage = new \ondrs\UploadManager\Storages\S3Storage('upload-manager-test', self::STORAGE_NAMESPACE, $s3Client);
    }


    function testSave()
    {
        $namespace = uniqid('testSave');

        $source = __DIR__ . '/../data/focus.png';
        $url = $this->s3Storage->save($source, "$namespace/focus.png");

        Assert::truthy(@file_get_contents($url));
    }


    function testBulkSave()
    {
        $namespace = uniqid('testBulkSave');

        $files = [
            [__DIR__ . '/../data/focus.png', "$namespace/focus.png"],
            [__DIR__ . '/../data/test-file.php', "$namespace/test-file.php"],
        ];

        $results = $this->s3Storage->bulkSave($files);

        Assert::count(2, $results);

        foreach ($results as $url) {
            Assert::truthy(@file_get_contents($url));
        }
    }


    function testDelete()
    {
        $namespace = uniqid('testDelete');

        $source = __DIR__ . '/../data/focus.png';
        $url = $this->s3Storage->save($source, "$namespace/focus.png");

        Assert::truthy(@file_get_contents($url));

        $this->s3Storage->delete("$namespace/focus.png");

        Assert::false(@file_get_contents($url));
    }


    function testBulkDelete()
    {
        $namespace = uniqid('testBulkDelete');

        $files = [
            [__DIR__ . '/../data/focus.png', "$namespace/focus.png"],
            [__DIR__ . '/../data/test-file.php', "$namespace/test-file.php"],
        ];

        $results = $this->s3Storage->bulkSave($files);

        Assert::count(2, $results);

        foreach ($results as $url) {
            Assert::truthy(@file_get_contents($url));
        }

        $this->s3Storage->bulkDelete([
            "$namespace/focus.png",
            "$namespace/test-file.php",
        ]);

        foreach ($results as $url) {
            Assert::false(@file_get_contents($url));
        }
    }


    function testFind()
    {
        $namespace = uniqid('testFind');

        $files = [
            [__DIR__ . '/../data/focus.png', "$namespace/focus.png"],
            [__DIR__ . '/../data/test-file.php', "$namespace/test-file.php"],
            [__DIR__ . '/../data/test-file.txt', "$namespace/test-file.txt"],
            [__DIR__ . '/../data/test-image.jpg', "$namespace/test-image.jpg"],
            [__DIR__ . '/../data/test-image-big.jpg', "$namespace/test-image-big.jpg"],
        ];

        $results = $this->s3Storage->bulkSave($files);

        Assert::count(5, $results);

        foreach ($results as $url) {
            Assert::truthy(@file_get_contents($url));
        }

        Assert::count(2, $this->s3Storage->find($namespace, '*.jpg'));
        Assert::count(1, $this->s3Storage->find($namespace, '*.png'));
        Assert::count(1, $this->s3Storage->find($namespace, '*.php'));
        Assert::count(3, $this->s3Storage->find($namespace, ['*.jpg', '*.png']));

        foreach ($this->s3Storage->find($namespace, ['*.jpg', '*.png']) as $filePath => $fileInfo) {
            Assert::truthy(file_get_contents($filePath));
            Assert::type(SplFileInfo::class, $fileInfo);
        }
    }


}


run(new S3StorageTest());
