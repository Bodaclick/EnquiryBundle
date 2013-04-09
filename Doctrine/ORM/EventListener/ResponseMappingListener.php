<?php

/*
 * This file is part of the BDKEnquiryBundle package.
 *
 * (c) Bodaclick S.L. <http://bodaclick.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BDK\EnquiryBundle\Doctrine\ORM\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use BDK\EnquiryBundle\DependencyInjection\InheritanceTypes;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * Doctrine ORM Listener to map the inheritance of Response subclasses if defined in configuration
 */
class ResponseMappingListener
{
    protected $responseClasses;
    protected $inheritanceType;
    protected $defaultResponse;

    /**
     * Constructor
     *
     * @param Response $defaultResponse Default Response base class
     * @param array    $responseClasses Array with data mapping from configuration
     * @param string   $inheritanceType Type of inheritance
     */
    public function __construct($defaultResponse, $responseClasses, $inheritanceType)
    {
        $this->responseClasses = $responseClasses;
        $this->inheritanceType = $inheritanceType;
        $this->defaultResponse = $defaultResponse;
    }

    /**
     * Method called when event loadClassMetadata is launched
     *
     * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $args
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $classMetadata = $args->getClassMetadata();

        $name = $classMetadata->getName();

        //If it's a defined response class, check if it's subclass of the default one
        if (in_array($name, array_values($this->responseClasses))) {
             if (!$classMetadata->getReflectionClass()->isSubclassOf($this->defaultResponse)) {
                 throw new \LogicException(
                     sprintf(
                        'The mapped response class %s is not a subclass of %s',
                        $name,
                        $this->defaultResponse
                    )
                 );
             }
        }

        //The discriminator map is only needed in the default Response class
        if ($classMetadata->getName()!==$this->defaultResponse) {
            return;
        }

        //Only if there are more than one Response class either in default classes
        //or in configuration, they are mapped as single or class table inheritance
        if (empty($this->responseClasses)) {
            return;
        }

        //Setting the hierarchy of Response classes and subclasses
        //depending on mapping given in configuration
        $builder = new ClassMetadataBuilder($args->getClassMetadata());

        switch ($this->inheritanceType) {
            case InheritanceTypes::SINGLE:
                $builder->setSingleTableInheritance();
                break;
            case InheritanceTypes::JOINED:
                $builder->setJoinedTableInheritance();
                break;
        }

        $builder->setDiscriminatorColumn('type');

        foreach ($this->responseClasses as $type=>$class) {
            $builder->addDiscriminatorMapClass($type, $class);
        }

    }
}
