<?php

namespace ondrs\UploadManager\DI;

use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use ondrs\UploadManager\Exception;
use ondrs\UploadManager\ManagerContainer;
use ondrs\UploadManager\ManagerProvider;
use ondrs\UploadManager\Managers\FileManager;
use ondrs\UploadManager\Managers\ImageManager;
use ondrs\UploadManager\Storages\FileStorage;
use ondrs\UploadManager\Upload;

class Extension extends CompilerExtension
{

    /** @var array */
    private $defaults = [
        'tempDir' => '%tempDir%',
        'basePath' => '%wwwDir%',
        'relativePath' => NULL,
        'imageManager' => [
            'dimensions' => NULL,
            'maxSize' => NULL,
            'quality' => NULL,
            'type' => NULL,
        ],
        'fileManager' => [
            'blacklist' => [],
        ],
    ];


    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);
        $builder = $this->getContainerBuilder();

        if ($config['relativePath'] === NULL) {
            throw new Exception('relativePath must be set');
        }

        $builder->addDefinition($this->prefix('storage'))
            ->setClass(FileStorage::class, [
                $config['basePath'],
                $config['relativePath'],
            ]);

        $builder->addDefinition($this->prefix('managerProvider'))
            ->setClass(ManagerProvider::class);

        $builder->addDefinition($this->prefix('managerContainer'))
            ->setClass(ManagerContainer::class);

        $builder->addDefinition($this->prefix('imageManager'))
            ->setClass(ImageManager::class, [
                $builder->getDefinition($this->prefix('storage')),
                $config['tempDir'],
                $config['imageManager']['dimensions'],
                $config['imageManager']['maxSize'],
                $config['imageManager']['quality'],
                $config['imageManager']['type'],
            ]);

        if (isset($config['imageManager']['saveOriginal'])) {
            $builder->getDefinition($this->prefix('imageManager'))
                ->addSetup('saveOriginal', [$config['imageManager']['saveOriginal']]);
        }

        $builder->addDefinition($this->prefix('fileManager'))
            ->setClass(FileManager::class, [
                $builder->getDefinition($this->prefix('storage')),
                $config['fileManager']['blacklist'],
            ]);

        $builder->addDefinition($this->prefix('upload'))
            ->setClass(Upload::class);
    }


    /**
     * @param Configurator $configurator
     */
    public static function register(Configurator $configurator)
    {
        $configurator->onCompile[] = function ($config, Compiler $compiler) {
            $compiler->addExtension('UploadManager', new Extension());
        };
    }

}
