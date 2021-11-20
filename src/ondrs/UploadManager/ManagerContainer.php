<?php

namespace ondrs\UploadManager;

use Nette\DI\Container;
use ondrs\UploadManager\Managers\IManager;

class ManagerContainer
{

    /** @var IManager[] */
    private $instances = [];


    public function __construct(Container $container)
    {
        foreach ($container->findByType(IManager::class) as $name) {
            $this->register($container->getService($name));
        }
    }


    /**
     * @param IManager $instance
     */
    public function register(IManager $instance): void
    {
        $this->instances[get_class($instance)] = $instance;
    }


    /**
     * @param string $name
     * @return IManager|NULL
     */
    public function get($name): ?IManager
    {
        return $this->instances[$name] ?? NULL;
    }


    /**
     * @param $name
     * @return IManager|NULL
     */
    public function &__get($name)
    {
        return $this->get($name);
    }

}
