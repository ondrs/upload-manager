<?php

namespace ondrs\UploadManager\Storages;

use Aws\Result;
use Aws\S3\S3Client;
use GuzzleHttp\Promise;
use ondrs\UploadManager\Utils;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class S3Storage implements IStorage
{

    /** @var string */
    private $basePath;

    /** @var string */
    private $relativePath;

    /** @var S3Client */
    private $s3Client;


    /**
     * @param string $basePath
     * @param string $relativePath
     * @param S3Client $s3Client
     */
    public function __construct($basePath, $relativePath, S3Client $s3Client)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->relativePath = rtrim(ltrim($relativePath, '/'), '/');
        $this->s3Client = $s3Client;
    }


    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }


    /**
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }


    /**
     * @internal
     * @param string $source
     * @param string $destination
     * @return Promise\Promise
     */
    public function s3Upload($source, $destination)
    {
        return $this->s3Client->putObjectAsync([
            'Bucket' => $this->basePath,
            'Key' => Utils::normalizePath($this->relativePath . '/' . $destination),
            'SourceFile' => $source,
            'ACL' => 'public-read',
            'StorageClass' => 'REDUCED_REDUNDANCY',
        ]);
    }


    /**
     * @param string $source
     * @param string $destination
     * @return string
     */
    public function save($source, $destination)
    {
        /** @var Result $result */
        $result = $this->s3Upload($source, $destination)->wait();

        return $result->toArray()['ObjectURL'];
    }


    /**
     * @param array $files of [$source, $destination]
     * @return array
     */
    public function bulkSave(array $files)
    {
        $promises = [];

        foreach ($files as $file) {
            $promises[] = $this->s3Upload($file[0], $file[1]);
        }

        $results = Promise\all($promises)->wait();

        return array_map(function (Result $result) {
            return $result->toArray()['ObjectURL'];
        }, $results);
    }


    /**
     * @param string $filePath
     */
    public function delete($filePath)
    {
        $this->s3Client->deleteObject([
            'Bucket' => $this->basePath,
            'Key' => Utils::normalizePath($this->relativePath . '/' . $filePath),
        ]);
    }


    /**
     * @param array $files
     */
    public function bulkDelete(array $files)
    {
        $objects = [];

        foreach ($files as $filePath) {
            $objects[] = [
                'Key' => Utils::normalizePath($this->relativePath . '/' . $filePath)
            ];
        }

        $this->s3Client->deleteObjects([
            'Bucket' => $this->basePath,
            'Delete' => [
                'Objects' => $objects,
            ],
        ]);
    }


    /**
     * Converts Finder pattern to regular expression.
     * @param  array
     * @return string
     */
    private static function buildPattern($masks)
    {
        $pattern = array();
        $masks = is_array($masks) ? $masks : [$masks];
        foreach ($masks as $mask) {
            $mask = rtrim(strtr($mask, '\\', '/'), '/');
            $prefix = '';
            if ($mask === '') {
                continue;

            } elseif ($mask === '*') {
                return NULL;

            } elseif ($mask[0] === '/') { // absolute fixing
                $mask = ltrim($mask, '/');
                $prefix = '(?<=^/)';
            }
            $pattern[] = $prefix . strtr(preg_quote($mask, '#'),
                    array('\*\*' => '.*', '\*' => '[^/]*', '\?' => '[^/]', '\[\!' => '[^', '\[' => '[', '\]' => ']', '\-' => '-'));
        }
        return $pattern ? '#/(' . implode('|', $pattern) . ')\z#i' : NULL;
    }


    /**
     * @param $namespace
     * @param $filter
     * @return array
     */
    public function find($namespace, $filter)
    {
        $this->s3Client->registerStreamWrapper();

        $dir = 's3://' . Utils::normalizePath($this->basePath . '/' . $this->relativePath . '/' . $namespace);

        if (!is_dir($dir)) {
            return [];

        }

        $pattern = self::buildPattern($filter);

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        $results = [];

        foreach ($iterator as $file) {
            if (!$pattern || preg_match($pattern, '/' . strtr($file, '\\', '/'))) {

                $file = new \SplFileInfo(str_replace('\\', '/', $file));
                $results[(string)$file] = $file;
            }
        }

        return $results;
    }
}
