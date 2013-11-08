<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BDK\EnquiryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from the app/config files
 *
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('bdk_enquiry');

        $supportedDrivers = DriversSupported::getList();
        $supportedInheritanceTypes = InheritanceTypes::getList();

        $rootNode->children()
            ->scalarNode('db_driver')
                ->validate()
                ->ifNotInArray($supportedDrivers)
                    ->thenInvalid(
                        'The driver %s is not supported. Please choose one of '.json_encode($supportedDrivers)
                    )
                ->end()
                ->cannotBeOverwritten()
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->scalarNode('repository_class')->isRequired()->end()
            ->scalarNode('enquiry_class')->isRequired()->end()
            ->arrayNode('responses')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('mapping')
                        ->useAttributeAsKey('type')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('type')->end()
                                ->scalarNode('class')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                    ->scalarNode('inheritance')
                        ->defaultValue('single')
                        ->validate()
                        ->ifNotInArray($supportedInheritanceTypes)
                        ->thenInvalid(
                            'The %s inheritance type is not supported. Please choose one of '
                            .json_encode($supportedInheritanceTypes)
                        )
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->scalarNode('db_prefix')->end()
            ->scalarNode('logger')->end();

        return $treeBuilder;
    }
}
