<?php


use ondrs\UploadManager\Utils;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


class UtilsTest extends Tester\TestCase
{


    function testNormalizePath()
    {
        Assert::same('/aaa/aaaa/vvv', Utils::normalizePath('/////aaa/aaaa//vvv/////'));
    }


    function testMakeDirectoryRecursive()
    {
        $dir = TEMP_DIR . '/a/b/c/d/e';
        Assert::false(is_dir($dir));
        Utils::makeDirectoryRecursive($dir);
        Assert::true(is_dir($dir));
    }


    function testSanitizeLongPHPFileName()
    {
        $filename = TEMP_DIR . '/' . \Nette\Utils\Random::generate(150) . '.php';
        \Nette\Utils\FileSystem::write($filename, '');
        $fileInfo = new SplFileInfo($filename);

        $fileUpload = new \Nette\Http\FileUpload([
            'name' => $fileInfo->getBasename(),
            'type' => $fileInfo->getType(),
            'size' => $fileInfo->getSize(),
            'tmp_name' => $filename,
            'error' => 0
        ]);

        $filename = Utils::sanitizeFileName($fileUpload);

        Assert::equal(64, \Nette\Utils\Strings::length($filename));
        Assert::equal('php', $fileInfo->getExtension());
    }


    function testSanitizeLongHTMLFileName()
    {
        $filename = TEMP_DIR . '/' . \Nette\Utils\Random::generate(150) . '.html';
        \Nette\Utils\FileSystem::write($filename, '');
        $fileInfo = new SplFileInfo($filename);

        $fileUpload = new \Nette\Http\FileUpload([
            'name' => $fileInfo->getBasename(),
            'type' => $fileInfo->getType(),
            'size' => $fileInfo->getSize(),
            'tmp_name' => $filename,
            'error' => 0
        ]);

        $filename = Utils::sanitizeFileName($fileUpload);

        Assert::equal(65, \Nette\Utils\Strings::length($filename));
        Assert::equal('html', $fileInfo->getExtension());
    }


    function testSanitizeShortTXTFileName()
    {
        $filename = TEMP_DIR . '/' . \Nette\Utils\Random::generate(10) . '.txt';
        \Nette\Utils\FileSystem::write($filename, '');
        $fileInfo = new SplFileInfo($filename);

        $fileUpload = new \Nette\Http\FileUpload([
            'name' => $fileInfo->getBasename(),
            'type' => $fileInfo->getType(),
            'size' => $fileInfo->getSize(),
            'tmp_name' => $filename,
            'error' => 0
        ]);

        $filename = Utils::sanitizeFileName($fileUpload);

        Assert::equal(24, \Nette\Utils\Strings::length($filename));
        Assert::equal('txt', $fileInfo->getExtension());
    }


    function testFileUploadFromFileException()
    {
        Assert::exception(function() {
            Utils::fileUploadFromFile('aaa.bbb');
        }, '\ondrs\UploadManager\FileNotExistsException');
    }


    function testFileUploadFromFile()
    {
        $fileUpload = Utils::fileUploadFromFile(__DIR__ . '/data/focus.png');
        Assert::type('\Nette\Http\FileUpload', $fileUpload);
        Assert::true($fileUpload->isImage());
    }


}


run(new UtilsTest());
