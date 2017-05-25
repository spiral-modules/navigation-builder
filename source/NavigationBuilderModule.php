<?php

namespace Spiral;

use Spiral\Core\DirectoriesInterface;
use Spiral\Modules\ModuleInterface;
use Spiral\Modules\PublisherInterface;
use Spiral\Modules\RegistratorInterface;
use Spiral\NavigationBuilder\Config;

/**
 * Class PagesModule
 *
 * @package Spiral
 */
class NavigationBuilderModule implements ModuleInterface
{
    /**
     * @inheritDoc
     */
    public function register(RegistratorInterface $registrator)
    {
        //Register tokenizer directory
        $registrator->configure('tokenizer', 'directories', 'spiral/navigation', [
            "directory('libraries') . 'spiral/navigation-builder/source/NavigationBuilder/Database/',",
        ]);

        //Register view namespace
        $registrator->configure('views', 'namespaces.spiral', 'spiral/navigation', [
            "'navigation' => [",
            "directory('libraries') . 'spiral/navigation-builder/source/views/',",
            "/*{{namespaces.navigation}}*/",
            "],",
        ]);

        //Register database settings
        $registrator->configure('databases', 'databases', 'spiral/navigation', [
            "'navigation' => [",
            "   'connection'  => 'mysql',",
            "   'tablePrefix' => 'cms_navigation_'",
            "   /*{{databases.navigation}}*/",
            "],",
        ]);

        //Register controller in vault config
        $registrator->configure('modules/vault', 'controllers', 'spiral/navigation', [
            "'navigation' => \\Spiral\\NavigationBuilder\\Controllers\\NavigationController::class,",
        ]);
    }

    /**
     * @inheritDoc
     */
    public function publish(PublisherInterface $publisher, DirectoriesInterface $directories)
    {
        //Publish config
        $publisher->publish(
            __DIR__ . '/config/config.php',
            $directories->directory('config') . Config::CONFIG . '.php',
            PublisherInterface::FOLLOW
        );
    }
}