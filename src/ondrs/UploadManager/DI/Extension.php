<?php

namespace ondrs\UploadManager\DI;

use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\Schema\Helpers;
use ondrs\UploadManager\Exception;
use ondrs\UploadManager\ImageProcessor;
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


    /**
     * @throws Exception
     */
    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();
        $params = $builder->parameters;

        $this->defaults['tempDir'] = $params['tempDir'];
        $this->defaults['basePath'] = $params['wwwDir'];

        $config = Helpers::merge($this->getConfig(), $this->defaults);

        if ($config['relativePath'] === NULL) {
            throw new Exception('relativePath must be set');
        }

        $builder->addDefinition($this->prefix('storage'))
            ->setFactory(FileStorage::class, [
                $config['basePath'],
                $config['relativePath'],
            ]);

        $builder->addDefinition($this->prefix('managerProvider'))
            ->setFactory(ManagerProvider::class);

        $builder->addDefinition($this->prefix('managerContainer'))
            ->setFactory(ManagerContainer::class);

        $builder->addDefinition($this->prefix('imageProcessor'))
            ->setFactory(ImageProcessor::class, [
                $config['tempDir'],
            ]);

        $builder->addDefinition($this->prefix('imageManager'))
            ->setFactory(ImageManager::class, [
                $builder->getDefinition($this->prefix('storage')),
                $builder->getDefinition($this->prefix('imageProcessor')),
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
            ->setFactory(FileManager::class, [
                $builder->getDefinition($this->prefix('storage')),
                $config['fileManager']['blacklist'],
            ]);

        $builder->addDefinition($this->prefix('upload'))
            ->setFactory(Upload::class);
    }


    public static function register(Configurator $configurator): void
    {
        $configurator->onCompile[] = function ($config, Compiler $compiler) {
            $compiler->addExtension('UploadManager', new Extension());
        };
    }

}
