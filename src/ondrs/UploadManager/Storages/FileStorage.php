<?php

namespace ondrs\UploadManager\Storages;

use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use ondrs\UploadManager\Utils;

class FileStorage implements IStorage
{

    /** @var string */
    private $basePath;

    /** @var string */
    private $relativePath;


    /**
     * @param string $basePath
     * @param string $relativePath
     */
    public function __construct($basePath, $relativePath)
    {
        $this->basePath = $basePath;
        $this->relativePath = $relativePath;
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
     * @param string $source
     * @param string $destination
     * @return string
     * @throws \Nette\IOException
     */
    public function save($source, $destination)
    {
        $path = Utils::normalizePath("$this->basePath/$this->relativePath/$destination");

        Utils::makeDirectoryRecursive(dirname($path));
        FileSystem::copy($source, $path);

        return $path;
    }


    /**
     * @param array $files of [$source, $destination]
     * @return array
     * @throws \Nette\IOException
     */
    public function bulkSave(array $files)
    {
        $results = [];

        foreach ($files as $file) {
            $results[] = $this->save($file[0], $file[1]);
        }

        return $results;
    }


    /**
     * @param string $filePath
     * @throws \Nette\IOException
     */
    public function delete($filePath)
    {
        $path = Utils::normalizePath("$this->basePath/$this->relativePath/$filePath");

        FileSystem::delete($path);
    }


    /**
     * @param array $files
     * @throws \Nette\IOException
     */
    public function bulkDelete(array $files)
    {
        foreach ($files as $file) {
            $this->delete($file);
        }
    }


    /**
     * @param $namespace
     * @param $filter
     * @return array
     */
    public function find($namespace, $filter)
    {
        $dir = Utils::normalizePath("$this->basePath/$this->relativePath/$namespace");

        if (!is_dir($dir)) {
            return [];
        }

        $result = [];

        foreach (Finder::findFiles($filter)->in($dir) as $filePath => $file) {
            $result[$filePath] = $file;
        }

        return $result;
    }
}
