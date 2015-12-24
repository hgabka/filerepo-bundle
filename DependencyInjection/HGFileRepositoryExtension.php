<?php

namespace HG\FileRepositoryBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class HGFileRepositoryExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('hg_file_repository.types', $config['types']);
        $container->setParameter('hg_file_repository.base_dir', $config['base_dir']);
        $container->setParameter('hg_file_repository.secure_dir', $config['secure_dir']);
        $container->setParameter('hg_file_repository.manager_class', $config['manager_class']);
        $container->setParameter('hg_file_repository.secure_role', $config['secure_role']);
        $container->setParameter('hg_file_repository.upload_manager_class', $config['upload_manager_class']);
        $container->setParameter('hg_file_repository.upload_request_type', $config['upload_request_type']);
        $container->setParameter('hg_file_repository.auto_delete_relations', $config['auto_delete_relations']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('twig.form.resources', array_merge(
            $container->getParameter('twig.form.resources'),
            array('HGFileRepositoryBundle:Form:file_repository_widget.html.twig', 'HGFileRepositoryBundle:Form:file_uploadify_widget.html.twig')
        ));
    }
}
