<?php

/*
 * This file is part of the Snowcap ImBundle package.
 *
 * (c) Snowcap <shoot@snowcap.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snowcap\ImBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @codeCoverageIgnore
 */
class SnowcapImExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        if (isset($config['formats'])) {
            $container->setParameter('snowcap_im.formats', $config['formats']);
        }
        if (isset($config['web_path'])) {
            $container->setParameter('snowcap_im.web_path', $config['web_path']);
        }
        if (isset($config['cache_path'])) {
            $container->setParameter('snowcap_im.cache_path', $config['cache_path']);
        }
        if (isset($config['timeout'])) {
            $container->setParameter('snowcap_im.timeout', $config['timeout']);
        }
        if (isset($config['binary_path'])) {
            $container->setParameter('snowcap_im.binary_path', $config['binary_path']);
        }
    }
}
