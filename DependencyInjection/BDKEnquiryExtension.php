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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages the bundle configuration
 */
class BDKEnquiryExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $driver = $config['db_driver'];

        //Load configuration for the db_driver given
        $loader->load(sprintf('%s.yml', $driver));

        //Load mapping of subclasses of Response
        $responseClasses = $config['responses']['mapping'];
        $inheritanceType = $config['responses']['inheritance'];

        $defaultResponses = $container->getParameter('bdk.response_mapping');

        //Check that default Response class is defined
        if ($defaultResponses==null || !isset($defaultResponses['default'])) {
            throw new \LogicException('No default Response class defined in bundle configuration');
        }

        //Set the default Response class (key exist, it's checked above)
        $defaultResponse = $defaultResponses['default'];
        $container->setParameter('bdk.enquiry.default_response_class', $defaultResponse);

        //Set the response classes as container parameter
        $container->setParameter('bdk.enquiry.responses_classes', $responseClasses);

        //Only enable the listeners for mapping Response classes if there are more than one
        if (count($defaultResponses) > 1 || !empty($responseClasses)) {
            //Normalize the user custom Response classes array
            $responseClasses = array_map(
                function ($value) {
                    return $value['class'];
                },
                $responseClasses
            );

            //Check that user custom Response class mapping don't collide with default ones
            $checkCollides = array_intersect_key($defaultResponses, $responseClasses);
            if (count($checkCollides)>0) {
                throw new \LogicException(
                    sprintf(
                        'Custom Response class mapping type not allowed: %s',
                        key($checkCollides)
                    )
                );
            }

            //Unset the default one
            unset($defaultResponses['default']);

            //Merge with default Response classes
            $responseClasses = array_merge($defaultResponses, $responseClasses);

            //Set the listener that configure the response mapping, depending on configuration
            $this->enableListener(
                $container,
                'bdk.response_mapping.listener',
                array($defaultResponse, $responseClasses, $inheritanceType),
                $driver
            );
        }

        //Set prefix to table or collection name
        if (!empty($config['db_prefix'])) {
            $this->enableListener($container, 'bdk.db_prefix.listener', array($config['db_prefix']), $driver);
        }

        if (!empty($config['logger'])) {
            $logger = new Reference($config['logger']);
            $def = $container->getDefinition('bdk.enquiry.manager');
            $def->addMethodCall('setLogger', array($logger));
        }
    }

    /**
     * enableListener
     *
     * @param $container
     * @param $id
     * @param $arguments
     * @param $driver
     *
     */
    protected function enableListener($container, $id, $arguments, $driver)
    {
        $def = $container->getDefinition($id);

        foreach ($arguments as $argument) {
            $def->addArgument($argument);
        }

        switch ($driver) {
            case DriversSupported::ORM:
                $def->addTag('doctrine.event_listener', array('event'=>'loadClassMetadata'));
                break;
            case DriversSupported::MONGODB:
                $def->addTag('doctrine_mongodb.odm.event_listener', array('event'=>'loadClassMetadata'));
                break;
        }
    }
}
