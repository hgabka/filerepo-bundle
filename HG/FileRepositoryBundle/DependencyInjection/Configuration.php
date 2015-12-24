<?php

namespace HG\FileRepositoryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('hg_file_repository');

        $rootNode
            ->children()
                ->scalarNode('manager_class')->defaultValue('HG\FileRepositoryBundle\Model\HGFileManager')->end()
                ->scalarNode('upload_manager_class')->defaultValue('HG\FileRepositoryBundle\File\FileRepositoryUploadManager')->end()
                ->scalarNode('upload_request_type')->defaultValue('file_repository')->end()
                ->scalarNode('base_dir')->defaultValue('file_repository')->end()
                ->scalarNode('secure_dir')->defaultValue('data')->end()
                ->scalarNode('secure_role')->defaultNull()->end()
                ->booleanNode('auto_delete_relations')->defaultTrue()->end()
                ->arrayNode('types')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('is_secure')->defaultFalse()->end()
                            ->scalarNode('secure_role')->defaultNull()->end()
                            ->scalarNode('filename')->defaultValue('%%id%%')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()    ;

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
