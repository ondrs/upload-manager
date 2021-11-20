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


    public function getBasePath(): string
    {
        return $this->basePath;
    }


    public function getRelativePath(): string
    {
        return $this->relativePath;
    }


    /**
     * @param string $source
     * @param string $destination
     * @return string
     * @throws \Nette\IOException
     */
    public function save(string $source, string $destination): string
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
    public function bulkSave(array $files): array
    {
        $results = [];

        foreach ($files as $file) {
            $results[] = $this->save($file[0], $file[1]);
        }

        return $results;
    }


    /**
     * @param string $path
     * @throws \Nette\IOException
     */
    public function delete(string $path): void
    {
        $path = Utils::normalizePath("$this->basePath/$this->relativePath/$path");

        FileSystem::delete($path);
    }


    /**
     * @param array $files
     * @throws \Nette\IOException
     */
    public function bulkDelete(array $files): void
    {
        foreach ($files as $file) {
            $this->delete($file);
        }
    }


    /**
     * @param string $namespace
     * @param string|string[] $filter
     * @return array
     */
    public function find(string $namespace, $filter): array
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
