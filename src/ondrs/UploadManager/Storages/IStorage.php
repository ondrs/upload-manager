<?php

namespace ondrs\UploadManager\Storages;

interface IStorage
{

    /**
     * @return string
     */
    function getBasePath();


    /**
     * @return string
     */
    function getRelativePath();


    /**
     * @param string $source
     * @param string $destination
     * @return string
     */
    function save($source, $destination);


    /**
     * @param array $files of [$source, $destination]
     * @return array
     */
    function bulkSave(array $files);


    /**
     * @param string $path
     * @return void
     */
    function delete($path);


    /**
     * @param array $files
     * @return void
     */
    function bulkDelete(array $files);


    /**
     * @param string $namespace
     * @param string $filter
     * @return array of [$filePath => SplFileInfo]
     */
    function find($namespace, $filter);
}
