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

        $container->setParameter('snowcap_im.formats', $config['formats']);
        $container->setParameter('snowcap_im.web_path', $config['web_path']);
        $container->setParameter('snowcap_im.cache_path', $config['cache_path']);
        $container->setParameter('snowcap_im.timeout', $config['timeout']);
        $container->setParameter('snowcap_im.binary_path', $config['binary_path']);

        $metadataCache = '%kernel.cache_dir%/snowcap_im';
        $metadataCache = $container->getParameterBag()->resolveValue($metadataCache);
        if (!is_dir($metadataCache)) {
            mkdir($metadataCache, 0777, true);
        }
        $container
            ->getDefinition('snowcap_im.metadata.cache')
            ->replaceArgument(0, $metadataCache)
        ;
    }
}
