<?php


use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


class FileManagerTest extends Tester\TestCase
{

    /** @var \ondrs\Uploader\FileManager */
    private $fileManager;

    const RELATIVE_PATH = 'FileManager';


    function setUp()
    {
        $this->fileManager = new \ondrs\Uploader\FileManager(TEMP_DIR, 'FileManager');
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

        Assert::true($this->fileManager->upload($fileUpload) instanceof \SplFileInfo);
        Assert::true(file_exists(TEMP_DIR . '/FileManager/test-file.txt'));
    }


    function testDeleteFile()
    {
        $filePath = TEMP_DIR . '/test-file2.txt';

        copy(__DIR__ . '/data/test-file.txt', $filePath);

        Assert::true(file_exists($filePath));

        $this->fileManager->delete(TEMP_DIR, 'test-file2.txt');
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

        Assert::true($this->fileManager->upload($fileUpload) instanceof \SplFileInfo);
        Assert::true(file_exists(TEMP_DIR . '/FileManager/test-image.jpg'));
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
        }, 'ondrs\Uploader\NotAllowedFileException');
    }
}


run(new FileManagerTest());
