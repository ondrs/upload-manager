<?php


use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


class UtilsTest extends Tester\TestCase
{


    function testNormalizePath()
    {
        Assert::same('/aaa/aaaa/vvv', \ondrs\Uploader\Utils::normalizePath('/////aaa/aaaa//vvv/////'));
    }


    function testMakeDirectoryRecursive()
    {
        $dir = TEMP_DIR . '/a/b/c/d/e';
        Assert::false(is_dir($dir));
        \ondrs\Uploader\Utils::makeDirectoryRecursive($dir);
        Assert::true(is_dir($dir));
    }


}


run(new UtilsTest());
