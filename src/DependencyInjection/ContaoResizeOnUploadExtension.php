<?php

/*
 * This file is part of Contao Resize On Upload Bundle.
 *
 * (c) John Doe
 *
 * @license LGPL-3.0-or-later
 */

namespace PresProg\ContaoResizeOnUploadBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContaoResizeOnUploadExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.yml');
        $loader->load('commands.yml');
        $loader->load('listeners.yml');
    }
}
