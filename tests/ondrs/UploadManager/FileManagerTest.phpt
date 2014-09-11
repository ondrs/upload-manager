<?php


use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


class FileManagerTest extends Tester\TestCase
{

    /** @var \ondrs\UploadManager\FileManager */
    private $fileManager;


    function setUp()
    {
        $this->fileManager = new \ondrs\UploadManager\FileManager(TEMP_DIR, 'FileManager');
    }


    function testUploadTxtFile()
    {
        $filePath = TEMP_DIR . '/test-file.txt';

        copy(__DIR__ . '/data/test-file.txt', $filePath);

        $file = new \SplFileInfo($filePath);

        $fileUpload = new \Nette\Http\FileUpload([
            'name' => $file->getBasename(),
            'type' => $file->getType(),
            'size' => $file->getSize(),
            'tmp_name' => $filePath,
            'error' => 0
        ]);

        $uploaded = $this->fileManager->upload($fileUpload);

        Assert::true($uploaded instanceof \SplFileInfo);
        Assert::equal('txt', $uploaded->getExtension());
        Assert::true(file_exists(TEMP_DIR . '/FileManager/' . $uploaded->getFilename()));
    }


    function testDeleteFile()
    {
        $filePath = TEMP_DIR . '/FileManager/test-file2.txt';

        copy(__DIR__ . '/data/test-file.txt', $filePath);

        Assert::true(file_exists($filePath));

        $this->fileManager->delete(NULL, 'test-file2.txt');
        Assert::false(file_exists($filePath));
    }


    function testUploadImageFile()
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

        $uploaded = $this->fileManager->upload($fileUpload);

        Assert::true($uploaded instanceof \SplFileInfo);
        Assert::equal('jpg', $uploaded->getExtension());
        Assert::true(file_exists(TEMP_DIR . '/FileManager/' . $uploaded->getFilename()));
    }


    function testFailUpload()
    {
        $filePath = TEMP_DIR . '/test-file.php';

        copy(__DIR__ . '/data/test-file.php', $filePath);

        $file = new \SplFileInfo($filePath);

        $fileUpload = new \Nette\Http\FileUpload([
            'name' => $file->getBasename(),
            'type' => $file->getType(),
            'size' => $file->getSize(),
            'tmp_name' => $filePath,
            'error' => 0
        ]);

        Assert::exception(function () use ($fileUpload) {
            $this->fileManager->upload($fileUpload);
        }, 'ondrs\UploadManager\NotAllowedFileException');
    }


    function testUploadFileWithLongName()
    {
        $filePath = TEMP_DIR . '/' . \Nette\Utils\Random::generate(150) .  '.txt';

        copy(__DIR__ . '/data/test-file.txt', $filePath);

        $file = new \SplFileInfo($filePath);

        $fileUpload = new \Nette\Http\FileUpload([
            'name' => $file->getBasename(),
            'type' => $file->getType(),
            'size' => $file->getSize(),
            'tmp_name' => $filePath,
            'error' => 0
        ]);

        $uploaded = $this->fileManager->upload($fileUpload);
        $filename = $uploaded->getFilename();

        Assert::true($uploaded instanceof \SplFileInfo);
        Assert::equal('txt', $uploaded->getExtension());

        Assert::equal(64, \Nette\Utils\Strings::length($filename));
    }
}


run(new FileManagerTest());
