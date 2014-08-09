<?php

namespace ondrs\Uploader\DI;


use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use ondrs\Uploader\Exception;

class UploaderExtension extends CompilerExtension
{

    /** @var array */
    private $defaults = [
        'basePath' => '%wwwDir%',
        'relativePath' => NULL,
        'imageManager' => [
            'basePath' => NULL,
            'relativePath' => NULL,
            'dimensions' => NULL,
            'maxSize' => NULL,
        ],
        'fileManager' => [
            'basePath' => NULL,
            'relativePath' => NULL,
            'blacklist' => NULL,
        ],
    ];


    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);
        $builder = $this->getContainerBuilder();

        if ($config['relativePath'] === NULL) {
            throw new Exception('relativePath must be set');
        }

        $builder->addDefinition($this->prefix('imageManager'))
            ->setClass('ondrs\Uploader\ImageManager', [
                $config['imageManager']['basePath'] ? $config['imageManager']['basePath'] : $config['basePath'],
                $config['imageManager']['relativePath'] ? $config['imageManager']['relativePath'] : $config['relativePath'],
                $config['imageManager']['dimensions'],
                $config['imageManager']['maxSize'],
            ]);

        $builder->addDefinition($this->prefix('fileManager'))
            ->setClass('ondrs\Uploader\FileManager', [
                $config['fileManager']['basePath'] ? $config['fileManager']['basePath'] : $config['basePath'],
                $config['fileManager']['relativePath'] ? $config['fileManager']['relativePath'] : $config['relativePath'],
                $config['fileManager']['blacklist'],
            ]);

        $builder->addDefinition($this->prefix('upload'))
            ->setClass('ondrs\Uploader\Upload', [
                $builder->getByType('Nette\Http\Request'),
                $builder->getDefinition($this->prefix('imageManager')),
                $builder->getDefinition($this->prefix('fileManager')),
            ]);

    }


    /**
     * @param Configurator $configurator
     */
    public static function register(Configurator $configurator)
    {
        $configurator->onCompile[] = function ($config, Compiler $compiler) {
            $compiler->addExtension('uploader', new UploaderExtension());
        };
    }

}
