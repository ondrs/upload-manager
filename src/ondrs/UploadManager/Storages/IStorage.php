<?php

namespace ondrs\UploadManager\Storages;

interface IStorage
{

    public function getBasePath(): string;


    public function getRelativePath(): string;


    public function save(string $source, string $destination): string;


    /**
     * @param array $files of [$source, $destination]
     * @return array
     */
    public function bulkSave(array $files): array;


    public function delete(string $path): void;


    public function bulkDelete(array $files): void;


    /**
     * @param string $namespace
     * @param string $filter
     * @return array of [$filePath => SplFileInfo]
     */
    public function find(string $namespace, $filter): array;
}
