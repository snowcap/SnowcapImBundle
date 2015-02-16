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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @codeCoverageIgnore
 *
 */
class Configuration implements ConfigurationInterface
{
    private $rootDir;

    /**
     * @param string $rootDir The root directory tof your web_path and im_path
     */
    function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }


    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('snowcap_im');

        $rootNode
            ->children()
                ->scalarNode('binary_path')
                    ->info('The path to Mogrify')
                    ->example('/usr/bin/')
                ->end()
                ->integerNode('timeout')
                    ->info('Sets the process timeout (max. runtime).')
                    ->defaultValue(60)
                ->end()
                ->scalarNode('root_dir')
                    ->info('The root directory of your web_path and im_path.')
                    ->defaultValue($this->rootDir)
                ->end()
                ->scalarNode('web_path')
                    ->info('Relative path to the web folder (relative to root directory).')
                    ->defaultValue('../web/')
                ->end()
                ->scalarNode('im_path')
                    ->info('Relative path to the images cache folder (relative to web path).')
                    ->defaultValue('cache/im/')
                ->end()
                ->arrayNode('formats')
                    ->useAttributeAsKey('key')
                    ->prototype('variable')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
