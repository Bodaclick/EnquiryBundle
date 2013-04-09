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
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * Doctrine ORM Listener to add a prefix to all the BDKEnquiryBundle table names
 */
class TableNamePrefixListener
{
    protected $prefix;

    /**
     * Constructor
     *
     * @param string $prefix Prefix to add to the names
     */
    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Method called when event loadClassMetadata is launched
     *
     * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $args
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $classMetadata = $args->getClassMetadata();

        //Only set the prefix to the bundle entities
        if ($classMetadata->getReflectionClass()->getNamespaceName()!== 'Bodaclick\BDKEnquiryBundle\Entity') {
            return;
        }

        //Setting the hierarchy of Response classes and subclasses
        //depending on mapping given in configuration
        $builder = new ClassMetadataBuilder($args->getClassMetadata());
        $builder->setTable($this->prefix.$classMetadata->getTableName());

    }
}
